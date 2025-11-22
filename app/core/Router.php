<?php
// FILE: /app/core/Router.php

class Router {
    private $routes = [];
    private $controller = 'DashboardController';
    private $method = 'index';
    private $params = [];

    public function __construct() {
        $this->parseUrl();
    }

    /**
     * Add route
     */
    public function add($route, $controller, $method = 'index') {
        $this->routes[$route] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    /**
     * Parse URL
     */
    private function parseUrl() {
        $url = $_SERVER['REQUEST_URI'];

        // Remove query string
        if (($pos = strpos($url, '?')) !== false) {
            $url = substr($url, 0, $pos);
        }

        // Remove trailing slash
        $url = rtrim($url, '/');

        // Remove leading slash
        $url = ltrim($url, '/');

        if (empty($url)) {
            $url = '';
        }

        return explode('/', filter_var($url, FILTER_SANITIZE_URL));
    }

    /**
     * Dispatch route
     */
    public function dispatch() {
        $url = $this->parseUrl();

        // Check if URL is empty (home page)
        if (empty($url[0])) {
            // Redirect to dashboard if authenticated, otherwise to login
            if (Auth::check()) {
                $this->controller = 'DashboardController';
                $this->method = 'index';
            } else {
                $this->controller = 'AuthController';
                $this->method = 'login';
            }
        } else {
            // Check for API routes
            if ($url[0] === 'api') {
                $this->handleApiRoute($url);
                return;
            }

            // Controller
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

            if (file_exists($controllerFile)) {
                $this->controller = $controllerName;
                unset($url[0]);
            }

            require_once APP_PATH . '/controllers/' . $this->controller . '.php';
            $this->controller = new $this->controller;

            // Method
            if (isset($url[1])) {
                if (method_exists($this->controller, $url[1])) {
                    $this->method = $url[1];
                    unset($url[1]);
                }
            }

            // Parameters
            $this->params = $url ? array_values($url) : [];

            // Call controller method with parameters
            call_user_func_array([$this->controller, $this->method], $this->params);
        }
    }

    /**
     * Handle API routes
     */
    private function handleApiRoute($url) {
        // API routes: /api/{module}/{action}
        $module = $url[1] ?? 'default';
        $action = $url[2] ?? 'index';

        $controllerName = 'Api' . ucfirst($module) . 'Controller';
        $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controller = new $controllerName;

            if (method_exists($controller, $action)) {
                $params = array_slice($url, 3);
                call_user_func_array([$controller, $action], $params);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'API method not found']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
        }
    }
}
