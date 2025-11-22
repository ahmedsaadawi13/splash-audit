<?php
// FILE: /config/config.php

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
}

// Load .env file
loadEnv(__DIR__ . '/../.env');

// Application Configuration
define('APP_NAME', getenv('APP_NAME') ?: 'SplashAudit');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'UTC');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'splash_audit');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Session Configuration
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 7200);
define('CSRF_TOKEN_NAME', getenv('CSRF_TOKEN_NAME') ?: 'csrf_token');

// File Upload Configuration
define('MAX_UPLOAD_SIZE', getenv('MAX_UPLOAD_SIZE') ?: 10485760); // 10MB
define('ALLOWED_EXTENSIONS', getenv('ALLOWED_EXTENSIONS') ?: 'pdf,docx,xlsx,jpg,jpeg,png');
define('UPLOAD_PATH', __DIR__ . '/../storage/uploads/');

// Email Configuration
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'noreply@splashaudit.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'SplashAudit');

// Platform Settings
define('PLATFORM_ADMIN_EMAIL', getenv('PLATFORM_ADMIN_EMAIL') ?: 'admin@splashaudit.com');
define('DEFAULT_SUBSCRIPTION_PLAN', getenv('DEFAULT_SUBSCRIPTION_PLAN') ?: 'basic');

// API Settings
define('API_RATE_LIMIT', getenv('API_RATE_LIMIT') ?: 100);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('LOGS_PATH', STORAGE_PATH . '/logs');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/php_errors.log');
}

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
