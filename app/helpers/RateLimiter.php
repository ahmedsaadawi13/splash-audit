<?php
// FILE: /app/helpers/RateLimiter.php

class RateLimiter {
    private $db;
    private $maxAttempts;
    private $decayMinutes;

    /**
     * Constructor
     * @param int $maxAttempts Maximum attempts allowed
     * @param int $decayMinutes Time window in minutes
     */
    public function __construct($maxAttempts = 60, $decayMinutes = 1) {
        $this->db = Database::getInstance();
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    /**
     * Check if action is allowed
     * @param string $key Unique key for rate limiting (e.g., IP address, user ID, API key)
     * @param string $action Action being rate limited (e.g., 'login', 'api', 'password_reset')
     * @return bool
     */
    public function check($key, $action = 'default') {
        $this->clearOldAttempts($key, $action);

        $attempts = $this->getAttempts($key, $action);

        return $attempts < $this->maxAttempts;
    }

    /**
     * Record an attempt
     * @param string $key Unique key
     * @param string $action Action being performed
     * @return bool
     */
    public function hit($key, $action = 'default') {
        $expiresAt = date('Y-m-d H:i:s', time() + ($this->decayMinutes * 60));

        $sql = "INSERT INTO rate_limits (rate_key, action, attempts, expires_at, created_at)
                VALUES (?, ?, 1, ?, NOW())
                ON DUPLICATE KEY UPDATE
                attempts = attempts + 1,
                expires_at = ?";

        try {
            $this->db->query($sql, [$key, $action, $expiresAt, $expiresAt]);
            return true;
        } catch (Exception $e) {
            error_log('Rate limiter error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get number of attempts
     * @param string $key Unique key
     * @param string $action Action
     * @return int
     */
    public function getAttempts($key, $action = 'default') {
        $sql = "SELECT attempts FROM rate_limits
                WHERE rate_key = ? AND action = ? AND expires_at > NOW()";

        $result = $this->db->fetchOne($sql, [$key, $action]);

        return $result ? (int)$result['attempts'] : 0;
    }

    /**
     * Get time until limit resets (in seconds)
     * @param string $key Unique key
     * @param string $action Action
     * @return int
     */
    public function getRemainingTime($key, $action = 'default') {
        $sql = "SELECT expires_at FROM rate_limits
                WHERE rate_key = ? AND action = ?";

        $result = $this->db->fetchOne($sql, [$key, $action]);

        if (!$result) {
            return 0;
        }

        $expiresAt = strtotime($result['expires_at']);
        $now = time();

        return max(0, $expiresAt - $now);
    }

    /**
     * Clear rate limit for a key
     * @param string $key Unique key
     * @param string $action Action
     * @return bool
     */
    public function clear($key, $action = 'default') {
        $sql = "DELETE FROM rate_limits WHERE rate_key = ? AND action = ?";

        try {
            $this->db->query($sql, [$key, $action]);
            return true;
        } catch (Exception $e) {
            error_log('Rate limiter clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear old attempts that have expired
     * @param string $key Unique key
     * @param string $action Action
     */
    private function clearOldAttempts($key, $action) {
        $sql = "DELETE FROM rate_limits
                WHERE rate_key = ? AND action = ? AND expires_at <= NOW()";

        try {
            $this->db->query($sql, [$key, $action]);
        } catch (Exception $e) {
            error_log('Rate limiter cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * Clear all expired rate limits (cleanup job)
     */
    public static function cleanup() {
        $db = Database::getInstance();
        $sql = "DELETE FROM rate_limits WHERE expires_at <= NOW()";

        try {
            $db->query($sql);
        } catch (Exception $e) {
            error_log('Rate limiter global cleanup error: ' . $e->getMessage());
        }
    }

    /**
     * Check and enforce rate limit
     * Throws exception if limit exceeded
     * @param string $key Unique key
     * @param string $action Action
     * @throws Exception
     */
    public function enforce($key, $action = 'default') {
        if (!$this->check($key, $action)) {
            $remaining = $this->getRemainingTime($key, $action);
            $minutes = ceil($remaining / 60);

            throw new Exception("Rate limit exceeded. Please try again in $minutes minute(s).");
        }

        $this->hit($key, $action);
    }

    /**
     * Get client IP address
     * @return string
     */
    public static function getClientIp() {
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
