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

        // Remove base path for subdirectory installations
        if (defined('BASE_PATH') && !empty(BASE_PATH)) {
            $basePath = rtrim(BASE_PATH, '/');
            if (strpos($url, $basePath) === 0) {
                $url = substr($url, strlen($basePath));
            }
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
                $controllerName = 'DashboardController';
                $this->method = 'index';
            } else {
                $controllerName = 'AuthController';
                $this->method = 'login';
            }

            // Load and instantiate controller
            $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $this->controller = new $controllerName;
            } else {
                $this->show404();
                return;
            }

            // Call method
            try {
                call_user_func_array([$this->controller, $this->method], []);
            } catch (Exception $e) {
                error_log('Router dispatch error: ' . $e->getMessage());
                $this->show500($e);
            }
            return;
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

                require_once $controllerFile;
                $this->controller = new $this->controller;
            } else {
                // Controller not found - show 404
                $this->show404();
                return;
            }

            // Method
            if (isset($url[1])) {
                if (method_exists($this->controller, $url[1])) {
                    $this->method = $url[1];
                    unset($url[1]);
                } else {
                    // Method not found - show 404
                    $this->show404();
                    return;
                }
            }

            // Parameters
            $this->params = $url ? array_values($url) : [];

            // Call controller method with parameters
            try {
                call_user_func_array([$this->controller, $this->method], $this->params);
            } catch (Exception $e) {
                error_log('Router dispatch error: ' . $e->getMessage());
                $this->show500($e);
            }
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

    /**
     * Show 404 error page
     */
    private function show404() {
        http_response_code(404);
        $errorFile = APP_PATH . '/views/errors/404.php';

        if (file_exists($errorFile)) {
            // Load layout with 404 error page
            $content = file_get_contents($errorFile);
            $layoutFile = APP_PATH . '/views/layouts/main.php';

            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            // Fallback error message if no error page exists
            echo '<html><head><title>404 Not Found</title></head><body>';
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The page you requested could not be found.</p>';
            echo '<a href="/">Go to Dashboard</a>';
            echo '</body></html>';
        }
        exit;
    }

    /**
     * Show 500 error page
     */
    private function show500($exception = null) {
        http_response_code(500);
        $errorFile = APP_PATH . '/views/errors/500.php';

        // Log the error
        if ($exception) {
            error_log('500 Error: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());
        }

        if (file_exists($errorFile)) {
            // Load layout with 500 error page
            $content = file_get_contents($errorFile);
            $layoutFile = APP_PATH . '/views/layouts/main.php';

            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            // Fallback error message if no error page exists
            echo '<html><head><title>500 Internal Server Error</title></head><body>';
            echo '<h1>500 - Internal Server Error</h1>';
            echo '<p>An error occurred while processing your request.</p>';

            // Show details in development mode
            if (defined('APP_ENV') && APP_ENV !== 'production' && $exception) {
                echo '<pre>' . $exception->getMessage() . '</pre>';
                echo '<pre>' . $exception->getTraceAsString() . '</pre>';
            }

            echo '<a href="/">Go to Dashboard</a>';
            echo '</body></html>';
        }
        exit;
    }
}
