# SplashAudit Installation Guide
# DigitalOcean LAMP Stack - Subdirectory Installation
# Target: https://yourdomain.com/audit

## Installation Steps

### Step 1: SSH into your droplet
ssh root@your-droplet-ip

### Step 2: Navigate and clone
cd /var/www/html
git clone https://github.com/ahmedsaadawi13/splash-audit.git audit
cd audit

### Step 3: Create database
mysql -u root -p

# In MySQL prompt:
CREATE DATABASE splash_audit_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'splashuser'@'localhost' IDENTIFIED BY 'CHANGE-THIS-STRONG-PASSWORD';
GRANT ALL PRIVILEGES ON splash_audit_prod.* TO 'splashuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;

### Step 4: Import database
mysql -u root -p splash_audit_prod < database.sql

### Step 5: Configure environment
cp .env.example .env
nano .env

# Update these values:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com/audit  # IMPORTANT: Include /audit in the URL!

DB_HOST=localhost
DB_NAME=splash_audit_prod
DB_USER=splashuser
DB_PASS=your-strong-password-here

# CRITICAL: The APP_URL must include the /audit subdirectory path
# This allows the application to generate correct URLs for assets and links

### Step 6: Generate APP_KEY
php -r "echo 'APP_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;"
# Copy output and add to .env

### Step 7: Set permissions
chown -R www-data:www-data /var/www/html/audit
chmod -R 755 /var/www/html/audit
chmod -R 755 /var/www/html/audit/storage
chmod -R 755 /var/www/html/audit/public/assets/uploads
chmod 644 /var/www/html/audit/.env

### Step 8: Configure Apache for subdirectory
# No changes needed - it should work automatically!
# The .htaccess in public folder handles everything

### Step 9: Test
Open browser: https://yourdomain.com/audit

Login:
Email: admin@splashaudit.com
Password: Admin@123

⚠️ CHANGE PASSWORD IMMEDIATELY!

## Troubleshooting

If you get 404 error:
sudo a2enmod rewrite
sudo systemctl restart apache2

If you get permission errors:
sudo chown -R www-data:www-data /var/www/html/audit
sudo chmod -R 755 /var/www/html/audit/storage

If you get database error:
Check credentials in /var/www/html/audit/.env
Test: mysql -u splashuser -p splash_audit_prod
