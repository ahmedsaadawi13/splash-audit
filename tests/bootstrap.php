<?php
/**
 * PHPUnit Bootstrap File
 * Sets up the testing environment
 */

// Define test environment
define('APP_ENV', 'testing');
define('APP_DEBUG', true);

// Define paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Load environment variables for testing
$_ENV['DB_HOST'] = 'localhost';
$_ENV['DB_NAME'] = 'splash_audit_test';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';
$_ENV['APP_KEY'] = 'test-key-for-phpunit-testing-only';
$_ENV['APP_URL'] = 'http://localhost';

// Define BASE_PATH for testing (empty for root installation)
define('BASE_PATH', '');

// Autoloader
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

    // Try controllers
    $controllerFile = APP_PATH . '/controllers/' . $className . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        return;
    }
});

// Initialize session for testing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "PHPUnit Bootstrap Loaded - Test Environment Ready\n";
