# SplashAudit Deployment Guide

## Quick Deployment Options

Choose the deployment method that best fits your needs:

### Option 1: Docker Deployment (Easiest) ⭐ RECOMMENDED
### Option 2: Shared Hosting (cPanel/Plesk)
### Option 3: VPS/Cloud Server (Ubuntu/CentOS)
### Option 4: Cloud Platforms (Heroku, AWS, DigitalOcean)

---

## 🐳 OPTION 1: Docker Deployment (5 Minutes)

**Requirements:**
- Server with Docker and Docker Compose installed
- Domain name pointing to server
- SSH access

### Step 1: Prepare Server

```bash
# SSH into your server
ssh user@your-server.com

# Install Docker (if not installed)
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

### Step 2: Clone and Configure

```bash
# Clone repository
git clone https://github.com/ahmedsaadawi13/splash-audit.git
cd splash-audit

# Create production .env file
cp .env.example .env.production

# Edit configuration
nano .env.production
```

**Required .env.production settings:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=your-secure-64-character-key-here

DB_HOST=database
DB_PORT=3306
DB_NAME=splash_audit_prod
DB_USER=splashuser
DB_PASS=CHANGE-THIS-STRONG-PASSWORD

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your-email@gmail.com
MAIL_PASS=your-app-password
MAIL_FROM=noreply@yourdomain.com
```

### Step 3: Generate App Key

```bash
# Generate secure app key
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
# Copy output to APP_KEY in .env.production
```

### Step 4: Deploy

```bash
# Build and start containers
docker-compose -f docker-compose.prod.yml up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f app
```

### Step 5: Configure SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal is set up automatically
```

### Step 6: Access Application

```
https://yourdomain.com

Default Login:
Email: admin@splashaudit.com
Password: Admin@123

⚠️ CHANGE PASSWORD IMMEDIATELY!
```

---

## 🖥️ OPTION 2: Shared Hosting Deployment

**Requirements:**
- cPanel or Plesk hosting
- PHP 7.4+
- MySQL database
- SSH access (optional but helpful)

### Step 1: Prepare Database

1. Login to cPanel
2. Go to **MySQL Databases**
3. Create database: `yourusername_splash`
4. Create user: `yourusername_splash`
5. Generate strong password
6. Grant ALL privileges to user
7. Note credentials for later

### Step 2: Upload Files

**Via FTP:**
```
1. Download SplashAudit from GitHub
2. Extract locally
3. Connect via FTP (FileZilla)
4. Upload all files to public_html/
```

**Via SSH (faster):**
```bash
ssh yourusername@yourdomain.com
cd public_html
git clone https://github.com/ahmedsaadawi13/splash-audit.git .
```

### Step 3: Configure

```bash
# Create .env file
cp .env.example .env
nano .env
```

Update values:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_NAME=yourusername_splash
DB_USER=yourusername_splash
DB_PASS=your-database-password
```

### Step 4: Import Database

**Via phpMyAdmin:**
1. Open phpMyAdmin in cPanel
2. Select your database
3. Click **Import** tab
4. Choose `database.sql`
5. Click **Go**

**Via SSH:**
```bash
mysql -u yourusername_splash -p yourusername_splash < database.sql
```

### Step 5: Set Document Root

In cPanel:
1. Go to **Domains** → **Domains**
2. Find your domain
3. Click **Manage**
4. Change Document Root to: `/public_html/public`
5. Save

### Step 6: Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
chmod 644 .env
```

### Step 7: Test & Access

Visit: `https://yourdomain.com`

---

## 🖥️ OPTION 3: VPS/Cloud Server (Ubuntu 20.04)

**Requirements:**
- Fresh Ubuntu 20.04 server
- Root or sudo access
- Domain pointing to server IP

### Automated Setup Script

Save this as `deploy.sh` and run it:

```bash
#!/bin/bash

echo "🚀 SplashAudit Production Deployment"
echo "===================================="

# Update system
sudo apt-get update
sudo apt-get upgrade -y

# Install LAMP stack
sudo apt-get install -y apache2 mysql-server php7.4 php7.4-mysql php7.4-mbstring php7.4-xml php7.4-gd php7.4-curl libapache2-mod-php7.4

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2

# Secure MySQL
sudo mysql_secure_installation

# Clone application
cd /var/www
sudo git clone https://github.com/ahmedsaadawi13/splash-audit.git splashaudit
cd splashaudit

# Set permissions
sudo chown -R www-data:www-data /var/www/splashaudit
sudo chmod -R 755 storage/

# Configure Apache
sudo cp deploy/apache-vhost.conf /etc/apache2/sites-available/splashaudit.conf
sudo a2ensite splashaudit.conf
sudo systemctl reload apache2

# Create database
sudo mysql -e "CREATE DATABASE splash_audit;"
sudo mysql -e "CREATE USER 'splashuser'@'localhost' IDENTIFIED BY 'CHANGE-THIS-PASSWORD';"
sudo mysql -e "GRANT ALL PRIVILEGES ON splash_audit.* TO 'splashuser'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Import schema
sudo mysql splash_audit < database.sql

# Configure environment
sudo cp .env.example .env
sudo nano .env

echo "✅ Setup complete!"
echo "Next steps:"
echo "1. Edit /var/www/splashaudit/.env with your settings"
echo "2. Get SSL certificate: sudo certbot --apache -d yourdomain.com"
echo "3. Access: https://yourdomain.com"
```

Run it:
```bash
chmod +x deploy.sh
./deploy.sh
```

---

## ☁️ OPTION 4: Cloud Platform Deployment

### A. DigitalOcean App Platform (1-Click)

1. Create DigitalOcean account
2. Click **Create** → **Apps**
3. Connect GitHub repository
4. Choose:
   - Type: Web Service
   - Branch: main
   - Build Command: (none)
   - Run Command: `php -S 0.0.0.0:8080 -t public`
5. Add MySQL database
6. Add environment variables from .env
7. Click **Deploy**

### B. Heroku Deployment

```bash
# Install Heroku CLI
curl https://cli-assets.heroku.com/install.sh | sh

# Login
heroku login

# Create app
heroku create your-app-name

# Add MySQL addon
heroku addons:create cleardb:ignite

# Get database URL
heroku config:get CLEARDB_DATABASE_URL

# Set environment variables
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false

# Deploy
git push heroku main

# Import database
heroku run mysql -u user -p database < database.sql

# Open app
heroku open
```

### C. AWS Elastic Beanstalk

1. Install EB CLI: `pip install awsebcli`
2. Initialize: `eb init`
3. Create environment: `eb create production`
4. Deploy: `eb deploy`
5. Open: `eb open`

---

## 📋 Post-Deployment Checklist

After deployment, complete these essential tasks:

### Security
- [ ] Change all default passwords
- [ ] Generate new APP_KEY
- [ ] Set APP_DEBUG=false
- [ ] Configure SSL/HTTPS
- [ ] Set up firewall rules
- [ ] Enable fail2ban (for VPS)
- [ ] Configure rate limiting

### Email
- [ ] Configure SMTP settings
- [ ] Test email delivery
- [ ] Set up SPF/DKIM records
- [ ] Verify sender domain

### Database
- [ ] Set up automated backups
- [ ] Configure backup retention (30 days)
- [ ] Test database restore
- [ ] Optimize database settings

### Monitoring
- [ ] Set up uptime monitoring (UptimeRobot)
- [ ] Configure error logging
- [ ] Set up log rotation
- [ ] Install monitoring agent

### Performance
- [ ] Enable OpCache
- [ ] Configure caching
- [ ] Set up CDN (optional)
- [ ] Test load time

### Cron Jobs
```cron
# Add to crontab -e

# Rate limit cleanup (every 15 minutes)
*/15 * * * * cd /var/www/splashaudit && php cron/cleanup_rate_limits.php

# Send notifications (hourly)
0 * * * * cd /var/www/splashaudit && php cron/send_notifications.php

# Database backup (daily at 2 AM)
0 2 * * * /var/www/splashaudit/deploy/backup.sh
```

---

## 🔧 Troubleshooting

### Issue: White screen / 500 error
```bash
# Check Apache error log
sudo tail -f /var/log/apache2/error.log

# Check application log
tail -f storage/logs/error.log

# Fix permissions
sudo chown -R www-data:www-data /var/www/splashaudit
sudo chmod -R 755 storage/
```

### Issue: Database connection failed
```bash
# Test database connection
mysql -u splashuser -p splash_audit

# Check credentials in .env
cat .env | grep DB_

# Verify MySQL is running
sudo systemctl status mysql
```

### Issue: .htaccess not working
```bash
# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check AllowOverride in Apache config
sudo nano /etc/apache2/sites-available/splashaudit.conf
# Ensure: AllowOverride All
```

---

## 🎯 Quick Deploy Commands

For each platform:

**Docker:**
```bash
docker-compose up -d && docker-compose logs -f
```

**VPS:**
```bash
cd /var/www/splashaudit && git pull && sudo systemctl reload apache2
```

**Shared Hosting:**
```bash
cd public_html && git pull && chmod -R 755 storage/
```

---

## 📞 Support

If you encounter issues:

1. Check logs: `storage/logs/error.log`
2. Review documentation: `/docs`
3. GitHub Issues: https://github.com/ahmedsaadawi13/splash-audit/issues
4. Email: support@splashaudit.com

---

**Deployment Time Estimates:**
- Docker: 5-10 minutes
- Shared Hosting: 15-30 minutes
- VPS: 30-60 minutes
- Cloud Platform: 10-20 minutes

**Good luck with your deployment! 🚀**
