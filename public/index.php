<?php
// FILE: /public/index.php

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Autoload core classes
spl_autoload_register(function ($className) {
    $coreFile = APP_PATH . '/core/' . $className . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
    }
});

// Initialize session
Session::start();

// Create router and dispatch
$router = new Router();
$router->dispatch();
