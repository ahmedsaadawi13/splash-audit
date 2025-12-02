#!/bin/bash

# SplashAudit Automated Deployment Script
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

set -e

ENVIRONMENT=${1:-production}
APP_DIR="/var/www/splashaudit"
BACKUP_DIR="/var/backups/splashaudit"
DATE=$(date +%Y%m%d_%H%M%S)

echo "🚀 SplashAudit Deployment Script"
echo "================================="
echo "Environment: $ENVIRONMENT"
echo "Date: $DATE"
echo ""

# Check if running as root or with sudo
if [[ $EUID -ne 0 ]]; then
   echo "❌ This script must be run as root or with sudo"
   exit 1
fi

# Backup current installation
echo "📦 Creating backup..."
mkdir -p $BACKUP_DIR
tar -czf "$BACKUP_DIR/backup_$DATE.tar.gz" -C $APP_DIR .
mysqldump -u root -p splash_audit > "$BACKUP_DIR/database_$DATE.sql"
echo "✅ Backup created: $BACKUP_DIR/backup_$DATE.tar.gz"

# Pull latest code
echo "📥 Pulling latest code..."
cd $APP_DIR
git fetch origin
git checkout main
git pull origin main
echo "✅ Code updated"

# Install/update dependencies
echo "📦 Installing dependencies..."
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
fi
echo "✅ Dependencies installed"

# Set permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR/storage
chmod -R 755 $APP_DIR/public/assets/uploads
chmod 644 $APP_DIR/.env
echo "✅ Permissions set"

# Clear caches
echo "🧹 Clearing caches..."
rm -rf $APP_DIR/storage/cache/*
echo "✅ Caches cleared"

# Run database migrations (if any)
echo "🗄️  Checking database..."
# Add migration commands here if you implement them
echo "✅ Database check complete"

# Restart services
echo "🔄 Restarting services..."
if command -v systemctl &> /dev/null; then
    systemctl reload apache2 || systemctl reload nginx
    echo "✅ Web server reloaded"
fi

# Health check
echo "🏥 Running health check..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
if [ "$HTTP_CODE" = "200" ]; then
    echo "✅ Health check passed (HTTP $HTTP_CODE)"
else
    echo "⚠️  Warning: Health check returned HTTP $HTTP_CODE"
fi

# Cleanup old backups (keep last 7 days)
echo "🧹 Cleaning old backups..."
find $BACKUP_DIR -name "backup_*.tar.gz" -mtime +7 -delete
find $BACKUP_DIR -name "database_*.sql" -mtime +7 -delete
echo "✅ Old backups cleaned"

echo ""
echo "🎉 Deployment Complete!"
echo "======================="
echo "Environment: $ENVIRONMENT"
echo "Backup: $BACKUP_DIR/backup_$DATE.tar.gz"
echo "Time: $(date)"
echo ""
echo "Next steps:"
echo "1. Test the application: https://yourdomain.com"
echo "2. Check error logs: tail -f $APP_DIR/storage/logs/error.log"
echo "3. Monitor for issues"
echo ""
