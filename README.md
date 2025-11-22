# SplashAudit - Multi-Tenant Internal Audit SaaS System

A complete, lightweight, scalable multi-tenant SaaS platform for Internal Audit management built with PHP 7.x+ and MySQL.

## Features

### Core Features
- **Multi-Tenant Architecture**: Complete tenant isolation with subscription-based access control
- **Role-Based Access Control (RBAC)**: 7 predefined roles with granular permissions
- **Audit Universe Management**: Track all auditable entities (processes, departments, branches, systems)
- **Audit Programs**: Annual/quarterly audit planning
- **Risk Assessment Module**: Complete risk register with heatmap visualization
- **Audit Plans & Engagements**: Comprehensive audit lifecycle management
- **Audit Checklists**: Reusable templates with execution tracking
- **Findings Management**: Track audit findings with severity levels
- **Corrective Actions**: Action tracking with due dates and reviews
- **Compliance Management**: Track regulatory compliance requirements
- **Evidence Repository**: Secure document management
- **Notifications & Alerts**: Automated email notifications for key events
- **Dashboards**: Role-specific dashboards for different user types
- **Reports**: Export audit data to CSV format
- **REST API**: Documented API with API key authentication

### Security Features
- Password hashing with bcrypt
- CSRF protection on all forms
- SQL injection prevention (prepared statements)
- XSS protection (output escaping)
- File upload validation (type, size, MIME)
- Session management with regeneration
- Tenant data isolation
- Security headers (X-Frame-Options, X-XSS-Protection, etc.)

### Subscription & Quota Management
- Three subscription plans: Basic, Professional, Enterprise
- Quota enforcement for:
  - Audit plans
  - Checklists
  - Users
  - Storage size
  - Corrective actions
- Usage tracking and statistics
- Trial period support

## System Requirements

- PHP 7.0 or higher (7.x - 8.x)
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- mod_rewrite enabled (Apache)
- PDO PHP Extension
- Fileinfo PHP Extension

## Installation

### 1. Clone or Download

```bash
git clone <repository-url> SplashAudit
cd SplashAudit
```

### 2. Configure Environment

Copy the example environment file and configure your settings:

```bash
cp .env.example .env
```

Edit `.env` and set your database credentials:

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=splash_audit
DB_USER=root
DB_PASS=your_password
```

### 3. Create Database

Create a MySQL database and import the schema:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE splash_audit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

```bash
mysql -u root -p splash_audit < database.sql
```

### 4. Set Permissions

Ensure the storage directory is writable:

```bash
chmod -R 775 storage/
chmod -R 775 storage/uploads/
chmod -R 775 storage/logs/
```

### 5. Configure Web Server

#### Apache

Create a virtual host or configure your document root to point to the `public` directory.

Example Apache configuration:

```apache
<VirtualHost *:80>
    ServerName splashaudit.local
    DocumentRoot /path/to/SplashAudit/public

    <Directory /path/to/SplashAudit/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashaudit_error.log
    CustomLog ${APACHE_LOG_DIR}/splashaudit_access.log combined
</VirtualHost>
```

#### Nginx

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name splashaudit.local;
    root /path/to/SplashAudit/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.env {
        deny all;
    }
}
```

### 6. Access the Application

Navigate to: `http://localhost` or your configured domain

## Default Credentials

### Platform Admin
- Email: `admin@splashaudit.com`
- Password: `Admin@123`

### Demo Tenant Admin
- Email: `admin@demo.com`
- Password: `Demo@123`

**IMPORTANT**: Change these passwords immediately after first login!

## User Roles

### Platform Admin
- Full system access
- Manage all tenants
- Platform-level configuration

### Tenant Admin
- Manage company users
- Manage audit programs
- Manage company settings
- Full access to all tenant features

### Audit Manager
- Create and manage audit plans
- Assign audits to auditors
- Approve audit reports
- View all reports

### Internal Auditor
- Perform assigned audits
- Execute checklists
- Create findings
- Document evidence

### Process Owner
- Respond to findings
- Submit evidence
- Create and manage corrective actions

### Reviewer
- Review corrective actions
- Approve/reject submissions

### Viewer
- Read-only access to all modules

## REST API Documentation

The system provides a RESTful API for integration with external systems.

### Authentication

All API requests require an API key in the header:

```
X-API-KEY: your_api_key_here
```

API keys can be found in the database `api_keys` table or generated via the admin panel.

### Endpoints

#### 1. Create Finding

**Endpoint**: `POST /api/findings/create`

**Request Body**:
```json
{
  "audit_plan_id": 1,
  "title": "Security Vulnerability Found",
  "description": "SQL injection vulnerability in login form",
  "severity": "high",
  "cause": "Lack of input validation",
  "effect": "Potential data breach",
  "criteria": "OWASP Top 10",
  "assigned_to": 5
}
```

**Response**:
```json
{
  "success": true,
  "finding_id": 42,
  "message": "Finding created successfully"
}
```

#### 2. Add Corrective Action

**Endpoint**: `POST /api/corrective-actions/add`

**Request Body**:
```json
{
  "finding_id": 42,
  "action_description": "Implement input validation and parameterized queries",
  "due_date": "2024-12-31",
  "responsible_user_id": 8
}
```

**Response**:
```json
{
  "success": true,
  "corrective_action_id": 15,
  "message": "Corrective action created successfully"
}
```

#### 3. Get Audit Status

**Endpoint**: `GET /api/audits/status?audit_plan_id=1`

**Response**:
```json
{
  "success": true,
  "audit": {
    "id": 1,
    "title": "Q4 2024 IT Audit",
    "status": "in_progress",
    "planned_start": "2024-10-01",
    "planned_end": "2024-12-31",
    "actual_start": "2024-10-05",
    "actual_end": null,
    "findings_count": 12
  }
}
```

#### 4. List Findings

**Endpoint**: `GET /api/findings/list?status=open`

**Response**:
```json
{
  "success": true,
  "count": 5,
  "findings": [
    {
      "id": 1,
      "title": "Finding Title",
      "severity": "high",
      "status": "open",
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

### Error Responses

```json
{
  "error": "Error message description"
}
```

Common HTTP Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized (invalid API key)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

## Database Schema

The system uses 25+ tables organized as follows:

### Platform Tables (no tenant_id)
- `tenants` - Company/organization records
- `subscription_plans` - Available subscription tiers
- `tenant_subscriptions` - Active subscriptions
- `invoices` - Billing invoices
- `payments` - Payment records

### Tenant-Isolated Tables (with tenant_id)
- `users` - User accounts
- `api_keys` - API authentication keys
- `audit_universe` - Auditable entities
- `audit_programs` - Audit programs
- `audit_plans` - Individual audit plans
- `audit_checklists` - Reusable checklists
- `checklist_items` - Checklist questions
- `audit_work_items` - Checklist execution
- `risks` - Risk register
- `findings` - Audit findings
- `corrective_actions` - Action items
- `corrective_action_reviews` - Review records
- `compliance_areas` - Compliance frameworks
- `compliance_requirements` - Specific requirements
- `compliance_checks` - Compliance assessments
- `files` - Document repository
- `notifications` - Email notifications
- `usage_tracking` - Quota usage
- `activity_logs` - Audit trail

## File Structure

```
SplashAudit/
├── app/
│   ├── controllers/      # Application controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── AuditController.php
│   │   ├── RiskController.php
│   │   ├── FindingController.php
│   │   ├── CorrectiveActionController.php
│   │   ├── ReportController.php
│   │   ├── UserController.php
│   │   └── Api*.php (API controllers)
│   ├── models/          # Data models
│   │   ├── User.php
│   │   ├── Tenant.php
│   │   ├── AuditPlan.php
│   │   ├── Finding.php
│   │   └── ...
│   ├── views/           # View templates
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   └── ...
│   ├── core/            # Core framework
│   │   ├── Database.php
│   │   ├── Router.php
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   ├── View.php
│   │   ├── Session.php
│   │   └── Auth.php
│   └── helpers/         # Helper classes
│       ├── FileUpload.php
│       ├── Validator.php
│       ├── Email.php
│       └── QuotaChecker.php
├── config/              # Configuration files
│   └── config.php
├── public/              # Public web root
│   ├── index.php
│   ├── .htaccess
│   └── assets/
│       ├── css/
│       └── js/
├── storage/
│   ├── uploads/         # Uploaded files
│   └── logs/            # Application logs
├── database.sql         # Database schema
├── .env.example         # Environment template
└── README.md
```

## Development Guidelines

### Adding New Features

1. **Create Model**: Extend the `Model` class in `app/models/`
2. **Create Controller**: Extend the `Controller` class in `app/controllers/`
3. **Create Views**: Add templates in `app/views/`
4. **Update Router**: Routes are automatically handled by the Router class

### Tenant Isolation

Always ensure tenant isolation when querying data:

```php
// Good - tenant isolation enabled
$model = new YourModel();
$records = $model->findAll(); // Automatically filters by tenant_id

// Direct queries - include tenant_id
$sql = "SELECT * FROM table WHERE tenant_id = ?";
$results = $this->db->fetchAll($sql, [Auth::tenantId()]);
```

### Security Best Practices

1. Always use prepared statements
2. Validate and sanitize user input
3. Escape output in views
4. Use CSRF tokens on forms
5. Validate file uploads
6. Log sensitive operations

## Troubleshooting

### Database Connection Issues
- Verify database credentials in `.env`
- Ensure MySQL service is running
- Check database user permissions

### File Upload Errors
- Check storage directory permissions (`chmod 775 storage/`)
- Verify `upload_max_filesize` and `post_max_size` in `php.ini`
- Ensure allowed file extensions are configured

### Session Issues
- Check session directory permissions
- Verify `session.save_path` in PHP configuration
- Clear browser cookies

### Routing Issues
- Ensure mod_rewrite is enabled (Apache)
- Check `.htaccess` file exists in `public/`
- Verify web server configuration

## Performance Optimization

1. **Database Indexes**: Indexes are included in schema for common queries
2. **Query Optimization**: Use pagination for large result sets
3. **Caching**: Implement caching layer for repeated queries (optional)
4. **File Storage**: Consider CDN for uploaded files in production

## Security Considerations

1. Change default passwords immediately
2. Use HTTPS in production
3. Set strong session configuration
4. Regularly update PHP and MySQL
5. Implement rate limiting on API endpoints
6. Regular security audits
7. Keep storage directory outside web root in production

## Backup & Recovery

### Database Backup
```bash
mysqldump -u root -p splash_audit > backup_$(date +%Y%m%d).sql
```

### File Backup
```bash
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/
```

## Support & Contribution

This is a complete internal audit SaaS system built for educational and production use.

## License

MIT License - Feel free to use, modify, and distribute.

## Changelog

### Version 1.0.0 (Initial Release)
- Complete multi-tenant architecture
- Full audit lifecycle management
- Risk assessment module
- Findings and corrective actions
- Compliance management
- REST API
- Role-based access control
- Subscription and quota management
- Comprehensive reporting

---

**Built with PHP 7.x+, MySQL, and modern web technologies.**
