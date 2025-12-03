<?php
/**
 * Session Helper Class
 * Handles session management, flash messages, and CSRF protection
 */

class Session {

    /**
     * Start session if not already started
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set session timeout
        if (defined('SESSION_LIFETIME')) {
            ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
            ini_set('session.cookie_lifetime', SESSION_LIFETIME);
        }

        // Regenerate session ID periodically for security
        if (!self::has('last_regeneration')) {
            self::regenerate();
        } elseif (time() - self::get('last_regeneration') > 1800) { // 30 minutes
            self::regenerate();
        }
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate() {
        session_regenerate_id(true);
        self::put('last_regeneration', time());
    }

    /**
     * Set a session value
     */
    public static function put($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
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
     * Remove a session value
     */
    public static function forget($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Set a flash message
     */
    public static function flash($key, $value = null) {
        if ($value === null) {
            // Get and remove flash message
            $message = self::get('_flash_' . $key);
            self::forget('_flash_' . $key);
            return $message;
        } else {
            // Set flash message
            self::put('_flash_' . $key, $value);
        }
    }

    /**
     * Generate CSRF token
     */
    public static function csrfToken() {
        if (!self::has('_csrf_token')) {
            $token = bin2hex(random_bytes(32));
            self::put('_csrf_token', $token);
        }
        return self::get('_csrf_token');
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        return hash_equals(self::csrfToken(), $token);
    }

    /**
     * Generate CSRF field for forms
     */
    public static function csrfField() {
        $token = self::csrfToken();
        $fieldName = defined('CSRF_TOKEN_NAME') ? CSRF_TOKEN_NAME : 'csrf_token';
        return '<input type="hidden" name="' . $fieldName . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Destroy session
     */
    public static function destroy() {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Get all session data
     */
    public static function all() {
        return $_SESSION;
    }
}
