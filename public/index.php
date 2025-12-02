<?php
// FILE: /public/index.php

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Autoload core, model, and helper classes
spl_autoload_register(function ($className) {
    // Try core classes
    $coreFile = APP_PATH . '/core/' . $className . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }

    // Try models
    $modelFile = APP_PATH . '/models/' . $className . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return;
    }

    // Try helpers
    $helperFile = APP_PATH . '/helpers/' . $className . '.php';
    if (file_exists($helperFile)) {
        require_once $helperFile;
        return;
    }
});

// Initialize session
Session::start();

// Error handling for production
if (APP_ENV === 'production') {
    set_exception_handler(function($exception) {
        error_log('Exception: ' . $exception->getMessage());
        http_response_code(500);
        if (file_exists(APP_PATH . '/views/errors/500.php')) {
            require APP_PATH . '/views/errors/500.php';
        } else {
            echo 'An error occurred. Please try again later.';
        }
        exit;
    });
}

// Create router and dispatch
try {
    $router = new Router();
    $router->dispatch();
} catch (Exception $e) {
    error_log('Router exception: ' . $e->getMessage());
    http_response_code(500);
    if (APP_ENV === 'development') {
        echo '<h1>Error</h1><pre>' . $e->getMessage() . '</pre>';
    } else {
        echo 'An error occurred. Please try again later.';
    }
}
