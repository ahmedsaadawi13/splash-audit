# SplashAudit Administrator Guide

## System Administration

### Installation

#### Using setup.php (Recommended)

```bash
php setup.php
```

Follow interactive prompts for database setup and configuration.

#### Using Docker

```bash
# Clone repository
git clone https://github.com/yourusername/splashaudit.git
cd splashaudit

# Start services
docker-compose up -d

# Access at http://localhost:8080
```

#### Manual Installation

1. **Requirements:**
   - PHP 7.0+ (7.4+ recommended)
   - MySQL 5.7+ or MariaDB 10.2+
   - Apache/Nginx with mod_rewrite
   - 256MB+ RAM

2. **Install:**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE splash_audit"

   # Import schema
   mysql -u root -p splash_audit < database.sql

   # Configure .env
   cp .env.example .env
   nano .env
   ```

3. **Configure Web Server:**
   - Point document root to `/public`
   - Enable mod_rewrite
   - Set file permissions: `chmod -R 755 storage/`

---

## Configuration

### Environment Variables (.env)

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=your-secure-key-here

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=splash_audit
DB_USER=your_user
DB_PASS=your_password

# Email (SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=your-email@gmail.com
MAIL_PASS=your-password
MAIL_FROM=noreply@yourdomain.com
```

---

## User Management

### Roles & Permissions

| Role | Permissions |
|------|-------------|
| Platform Admin | Full system access |
| Tenant Admin | Full tenant access |
| Audit Manager | Create/manage audits |
| Internal Auditor | Execute audits, create findings |
| Process Owner | View assigned items |
| Reviewer | Review corrective actions |
| Viewer | Read-only access |

### Creating Admin Users

```sql
INSERT INTO users (tenant_id, email, password, first_name, last_name, role, status)
VALUES (1, 'admin@company.com', '$2y$10$hash...', 'Admin', 'User', 'tenant_admin', 'active');
```

---

## Tenant Management

### Multi-Tenant Isolation

- Each tenant has separate data
- Tenant ID enforced at query level
- No cross-tenant data access

### Adding New Tenants

```sql
-- Create tenant
INSERT INTO tenants (company_name, subdomain, status, subscription_plan_id)
VALUES ('Company Name', 'subdomain', 'active', 1);

-- Create tenant admin
INSERT INTO users (tenant_id, email, password, ...)
VALUES (LAST_INSERT_ID(), 'admin@company.com', ...);
```

---

## Security

### SSL/HTTPS Setup

1. **Obtain SSL Certificate:**
   - Let's Encrypt (free): `certbot --apache`
   - Commercial certificate

2. **Force HTTPS in .htaccess:**
   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### Rate Limiting

- API: 60 requests/minute per key
- Login: 5 attempts/15 minutes per IP
- Configurable in `RateLimiter` class

### Backup & Recovery

```bash
# Database backup
mysqldump -u root -p splash_audit > backup_$(date +%Y%m%d).sql

# File backup
tar -czf files_$(date +%Y%m%d).tar.gz storage/uploads

# Automated daily backups (crontab)
0 2 * * * /path/to/backup.sh
```

---

## Monitoring

### Application Logs

- Location: `storage/logs/`
- Error log: `error.log`
- Access log: `access.log`
- Activity log: Database table `activity_logs`

### Performance Monitoring

```sql
-- Slow queries
SELECT * FROM mysql.slow_log;

-- Active connections
SHOW PROCESSLIST;

-- Database size
SELECT table_schema, SUM(data_length + index_length) / 1024 / 1024 AS "Size (MB)"
FROM information_schema.tables
GROUP BY table_schema;
```

---

## Maintenance

### Database Optimization

```sql
-- Optimize tables
OPTIMIZE TABLE findings, audit_plans, corrective_actions;

-- Clean old activity logs (90+ days)
DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Clean expired rate limits
DELETE FROM rate_limits WHERE expires_at < NOW();
```

### Cron Jobs

```cron
# Rate limit cleanup (every 15 minutes)
*/15 * * * * php /path/to/cleanup_rate_limits.php

# Send email notifications (hourly)
0 * * * * php /path/to/send_notifications.php

# Database backup (daily at 2 AM)
0 2 * * * /path/to/backup.sh
```

---

## Troubleshooting

### Common Issues

**Database Connection Failed:**
- Check credentials in `.env`
- Verify MySQL is running: `systemctl status mysql`
- Check firewall: `sudo ufw allow 3306`

**500 Internal Server Error:**
- Check error logs: `tail -f storage/logs/error.log`
- Verify file permissions: `chmod -R 755 storage/`
- Enable debug: `APP_DEBUG=true` in `.env`

**Email Not Sending:**
- Verify SMTP credentials
- Check firewall (port 587/465)
- Test with: `php test_email.php`

---

## Scaling

### Performance Tips

1. **Enable OpCache:**
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **Use Redis for Sessions:**
   ```php
   session.save_handler = redis
   session.save_path = "tcp://127.0.0.1:6379"
   ```

3. **Database Read Replicas:**
   - Configure master-slave replication
   - Route read queries to slaves

4. **CDN for Assets:**
   - Serve CSS/JS/images from CDN
   - Reduce server load

---

## Support

**Technical Support:**
- Email: admin@splashaudit.com
- Documentation: https://docs.splashaudit.com
- GitHub Issues: https://github.com/yourusername/splashaudit/issues

---

**Version:** 1.0
**Last Updated:** 2024-12-02
