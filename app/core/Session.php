<?php
// FILE: /app/core/Session.php

class Session {
    /**
     * Start session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Lax');

            session_start();

            // Regenerate session ID periodically
            if (!self::has('created_at')) {
                self::set('created_at', time());
            } elseif (time() - self::get('created_at') > 1800) {
                session_regenerate_id(true);
                self::set('created_at', time());
            }
        }
    }

    /**
     * Set session value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session key exists
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy session
     */
    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Get and remove flash message
     */
    public static function flash($key, $default = null) {
        $value = self::get($key, $default);
        self::remove($key);
        return $value;
    }

    /**
     * Set flash message
     */
    public static function setFlash($key, $value) {
        self::set($key, $value);
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        if (!self::has(CSRF_TOKEN_NAME)) {
            self::set(CSRF_TOKEN_NAME, bin2hex(random_bytes(32)));
        }
        return self::get(CSRF_TOKEN_NAME);
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        $sessionToken = self::get(CSRF_TOKEN_NAME);
        return $sessionToken && hash_equals($sessionToken, $token);
    }

    /**
     * Get CSRF token input field
     */
    public static function csrfField() {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
}
