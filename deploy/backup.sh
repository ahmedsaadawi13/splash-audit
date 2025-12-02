#!/bin/bash

# SplashAudit Backup Script
# Add to crontab: 0 2 * * * /var/www/splashaudit/deploy/backup.sh

set -e

APP_DIR="/var/www/splashaudit"
BACKUP_DIR="/var/backups/splashaudit"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Database credentials (from .env)
DB_NAME="splash_audit"
DB_USER="root"
DB_PASS=""

echo "🔄 Starting backup: $DATE"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup files
echo "📁 Backing up files..."
tar -czf "$BACKUP_DIR/files_$DATE.tar.gz" \
    -C $APP_DIR \
    --exclude='storage/cache' \
    --exclude='storage/logs' \
    --exclude='.git' \
    .

# Backup database
echo "🗄️  Backing up database..."
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > "$BACKUP_DIR/database_$DATE.sql.gz"

# Get backup sizes
FILES_SIZE=$(du -h "$BACKUP_DIR/files_$DATE.tar.gz" | cut -f1)
DB_SIZE=$(du -h "$BACKUP_DIR/database_$DATE.sql.gz" | cut -f1)

echo "✅ Backup complete!"
echo "   Files: $FILES_SIZE"
echo "   Database: $DB_SIZE"

# Cleanup old backups
echo "🧹 Cleaning backups older than $RETENTION_DAYS days..."
find $BACKUP_DIR -name "files_*.tar.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "database_*.sql.gz" -mtime +$RETENTION_DAYS -delete

# Optional: Upload to S3 or remote storage
# aws s3 cp "$BACKUP_DIR/files_$DATE.tar.gz" s3://your-bucket/backups/
# aws s3 cp "$BACKUP_DIR/database_$DATE.sql.gz" s3://your-bucket/backups/

echo "✅ Backup completed: $DATE"
