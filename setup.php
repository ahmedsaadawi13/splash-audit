<?php
/**
 * SplashAudit Installation Wizard
 *
 * This script helps you set up SplashAudit quickly and easily.
 * Run this from the command line: php setup.php
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "          SplashAudit - Installation Wizard                    \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// Check if running from CLI
if (php_sapi_name() !== 'cli') {
    die("ERROR: This script must be run from the command line.\n");
}

// Check PHP version
if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    die("ERROR: PHP 7.0 or higher is required. You are running " . PHP_VERSION . "\n");
}

// Check for required PHP extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    die("ERROR: Missing required PHP extensions: " . implode(', ', $missingExtensions) . "\n");
}

echo "✓ PHP version check passed (" . PHP_VERSION . ")\n";
echo "✓ All required PHP extensions are installed\n\n";

// Check if .env already exists
if (file_exists('.env')) {
    echo "WARNING: .env file already exists.\n";
    echo "Do you want to overwrite it? (yes/no): ";
    $overwrite = trim(fgets(STDIN));

    if (strtolower($overwrite) !== 'yes') {
        die("Installation cancelled.\n");
    }
}

// Check if .env.example exists
if (!file_exists('.env.example')) {
    die("ERROR: .env.example file not found. Please restore it from the repository.\n");
}

echo "══════════════════════════════════════════════════════════════\n";
echo "                   DATABASE CONFIGURATION                      \n";
echo "══════════════════════════════════════════════════════════════\n\n";

// Get database credentials
echo "Database Host [localhost]: ";
$dbHost = trim(fgets(STDIN));
$dbHost = !empty($dbHost) ? $dbHost : 'localhost';

echo "Database Port [3306]: ";
$dbPort = trim(fgets(STDIN));
$dbPort = !empty($dbPort) ? $dbPort : '3306';

echo "Database Name [splash_audit]: ";
$dbName = trim(fgets(STDIN));
$dbName = !empty($dbName) ? $dbName : 'splash_audit';

echo "Database User [root]: ";
$dbUser = trim(fgets(STDIN));
$dbUser = !empty($dbUser) ? $dbUser : 'root';

echo "Database Password: ";
$dbPass = trim(fgets(STDIN));

// Test database connection
echo "\nTesting database connection...\n";

try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Database connection successful\n\n";

    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbName'");
    $dbExists = $stmt->rowCount() > 0;

    if ($dbExists) {
        echo "WARNING: Database '$dbName' already exists.\n";
        echo "Do you want to drop and recreate it? (yes/no): ";
        $dropDb = trim(fgets(STDIN));

        if (strtolower($dropDb) === 'yes') {
            $pdo->exec("DROP DATABASE `$dbName`");
            echo "✓ Existing database dropped\n";
        } else {
            echo "Using existing database...\n";
        }
    }

    // Create database if it doesn't exist
    if (!$dbExists || strtolower($dropDb ?? '') === 'yes') {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✓ Database '$dbName' created\n";
    }

} catch (PDOException $e) {
    die("ERROR: Database connection failed: " . $e->getMessage() . "\n");
}

echo "\n══════════════════════════════════════════════════════════════\n";
echo "                   APPLICATION CONFIGURATION                   \n";
echo "══════════════════════════════════════════════════════════════\n\n";

echo "Application URL [http://localhost]: ";
$appUrl = trim(fgets(STDIN));
$appUrl = !empty($appUrl) ? $appUrl : 'http://localhost';

echo "Environment (development/production) [development]: ";
$appEnv = trim(fgets(STDIN));
$appEnv = !empty($appEnv) ? $appEnv : 'development';

echo "Enable Debug Mode? (yes/no) [yes]: ";
$debugInput = trim(fgets(STDIN));
$debug = (empty($debugInput) || strtolower($debugInput) === 'yes') ? 'true' : 'false';

// Generate application key
$appKey = bin2hex(random_bytes(32));

// Create .env file
$envContent = file_get_contents('.env.example');

$envContent = str_replace('DB_HOST=localhost', "DB_HOST=$dbHost", $envContent);
$envContent = str_replace('DB_PORT=3306', "DB_PORT=$dbPort", $envContent);
$envContent = str_replace('DB_NAME=splash_audit', "DB_NAME=$dbName", $envContent);
$envContent = str_replace('DB_USER=root', "DB_USER=$dbUser", $envContent);
$envContent = str_replace('DB_PASS=', "DB_PASS=$dbPass", $envContent);
$envContent = str_replace('APP_URL=http://localhost', "APP_URL=$appUrl", $envContent);
$envContent = str_replace('APP_ENV=development', "APP_ENV=$appEnv", $envContent);
$envContent = str_replace('APP_DEBUG=true', "APP_DEBUG=$debug", $envContent);
$envContent = str_replace('APP_KEY=your-secret-key-here', "APP_KEY=$appKey", $envContent);

file_put_contents('.env', $envContent);
echo "\n✓ .env file created successfully\n";

// Import database schema
echo "\n══════════════════════════════════════════════════════════════\n";
echo "                   DATABASE IMPORT                             \n";
echo "══════════════════════════════════════════════════════════════\n\n";

echo "Do you want to import the database schema now? (yes/no): ";
$importDb = trim(fgets(STDIN));

if (strtolower($importDb) === 'yes') {
    if (!file_exists('database.sql')) {
        echo "ERROR: database.sql file not found.\n";
    } else {
        echo "Importing database schema...\n";

        try {
            $pdo->exec("USE `$dbName`");
            $sql = file_get_contents('database.sql');

            // Split into individual statements and execute
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    $pdo->exec($stmt);
                }
            }

            echo "✓ Database schema imported successfully\n";
            echo "✓ Sample data created:\n";
            echo "  - Platform Admin: admin@splashaudit.com (Password: Admin@123)\n";
            echo "  - Demo Tenant Admin: admin@demo.com (Password: Demo@123)\n";

        } catch (PDOException $e) {
            echo "ERROR: Database import failed: " . $e->getMessage() . "\n";
            echo "You can manually import database.sql later.\n";
        }
    }
}

// Check storage directory permissions
echo "\n══════════════════════════════════════════════════════════════\n";
echo "                   FILE PERMISSIONS                            \n";
echo "══════════════════════════════════════════════════════════════\n\n";

$storagePath = __DIR__ . '/storage';
if (!file_exists($storagePath)) {
    mkdir($storagePath, 0755, true);
    echo "✓ Created storage directory\n";
}

$uploadPath = $storagePath . '/uploads';
if (!file_exists($uploadPath)) {
    mkdir($uploadPath, 0755, true);
    echo "✓ Created uploads directory\n";
}

$cachePath = $storagePath . '/cache';
if (!file_exists($cachePath)) {
    mkdir($cachePath, 0755, true);
    echo "✓ Created cache directory\n";
}

// Check if directories are writable
if (is_writable($storagePath)) {
    echo "✓ Storage directory is writable\n";
} else {
    echo "WARNING: Storage directory is not writable. Please run: chmod -R 755 storage/\n";
}

echo "\n══════════════════════════════════════════════════════════════\n";
echo "                   INSTALLATION COMPLETE!                      \n";
echo "══════════════════════════════════════════════════════════════\n\n";

echo "✓ Installation completed successfully!\n\n";
echo "Next steps:\n";
echo "1. Point your web server document root to /public\n";
echo "2. Access the application at: $appUrl\n";
echo "3. Login with the credentials shown above\n";
echo "4. Change the default passwords immediately\n\n";

if ($appEnv === 'production') {
    echo "PRODUCTION MODE CHECKLIST:\n";
    echo "[ ] Set APP_DEBUG=false in .env\n";
    echo "[ ] Enable HTTPS\n";
    echo "[ ] Configure real SMTP settings in .env\n";
    echo "[ ] Set up automated backups\n";
    echo "[ ] Review and update security settings\n";
    echo "[ ] Set up cron job for rate limit cleanup\n\n";
}

echo "For documentation, visit: https://github.com/yourusername/splashaudit\n";
echo "For support, email: support@splashaudit.com\n\n";
echo "Thank you for choosing SplashAudit!\n\n";
