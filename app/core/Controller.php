<?php
// FILE: /app/core/Controller.php

class Controller {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Load model
     */
    protected function model($model) {
        $modelFile = APP_PATH . '/models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        }
        throw new Exception("Model $model not found");
    }

    /**
     * Load view
     */
    protected function view($view, $data = []) {
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            throw new Exception("View $view not found");
        }
    }

    /**
     * Redirect
     */
    protected function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /**
     * JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf() {
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        if (!Session::verifyCsrfToken($token)) {
            Session::setFlash('error', 'Invalid security token. Please try again.');
            return false;
        }
        return true;
    }

    /**
     * Check if request is POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Get POST data
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Sanitize input
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Set success message
     */
    protected function setSuccess($message) {
        Session::setFlash('success', $message);
    }

    /**
     * Set error message
     */
    protected function setError($message) {
        Session::setFlash('error', $message);
    }

    /**
     * Log activity
     */
    protected function logActivity($action, $module, $moduleId = null, $description = null) {
        try {
            $this->db->query(
                'INSERT INTO activity_logs (tenant_id, user_id, action, module, module_id, description, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
                [
                    Auth::tenantId(),
                    Auth::id(),
                    $action,
                    $module,
                    $moduleId,
                    $description,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]
            );
        } catch (Exception $e) {
            error_log('Activity log error: ' . $e->getMessage());
        }
    }
}
