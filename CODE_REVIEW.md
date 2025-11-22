# SplashAudit - Code Review & Recommendations

## Executive Summary

The SplashAudit system has been built with security and scalability in mind. This document outlines areas for improvement and best practices for production deployment.

## Security Improvements

### 1. Password Policy Enhancement

**Current State**: Basic password validation (8 characters minimum)

**Recommended Improvements**:
```php
// Add to Validator.php
public function strongPassword($field, $message = null) {
    $value = $this->data[$field] ?? '';

    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special char
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (!preg_match($pattern, $value)) {
        $this->errors[$field][] = $message ??
            'Password must contain at least 8 characters, 1 uppercase, 1 lowercase, 1 number, and 1 special character.';
        return false;
    }
    return true;
}
```

### 2. Rate Limiting for API and Login

**Implementation**:
```php
// Create app/helpers/RateLimiter.php
class RateLimiter {
    private $db;
    private $maxAttempts = 5;
    private $decayMinutes = 15;

    public function tooManyAttempts($key, $maxAttempts = null) {
        $max = $maxAttempts ?? $this->maxAttempts;
        $attempts = $this->attempts($key);
        return $attempts >= $max;
    }

    public function hit($key, $decayMinutes = null) {
        $decay = $decayMinutes ?? $this->decayMinutes;
        // Store in database or cache with expiry
    }

    public function attempts($key) {
        // Retrieve from database or cache
    }
}
```

**Usage in AuthController**:
```php
// In authenticate() method
$rateLimiter = new RateLimiter();
$key = 'login:' . $_SERVER['REMOTE_ADDR'];

if ($rateLimiter->tooManyAttempts($key, 5)) {
    Session::setFlash('error', 'Too many login attempts. Please try again in 15 minutes.');
    $this->redirect('/auth/login');
}

// On failed login
$rateLimiter->hit($key);
```

### 3. Content Security Policy (CSP) Headers

**Add to config/config.php**:
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
```

### 4. SQL Injection Prevention - Additional Layer

**Current State**: Using prepared statements (GOOD)

**Additional Protection**:
```php
// Add input sanitization for special cases
public function sanitizeForLike($input) {
    return str_replace(['%', '_'], ['\\%', '\\_'], $input);
}
```

### 5. File Upload Security Enhancement

**Add MIME type whitelist validation**:
```php
// In FileUpload.php - add additional check
private function validateMimeType($file) {
    $allowedMimes = [
        'application/pdf' => ['pdf'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png']
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    return isset($allowedMimes[$mimeType]) &&
           in_array($extension, $allowedMimes[$mimeType]);
}
```

### 6. API Key Security

**Recommendation**: Implement API key rotation and expiry

```php
// Add to api_keys table
ALTER TABLE api_keys ADD COLUMN expires_at DATETIME DEFAULT NULL;
ALTER TABLE api_keys ADD COLUMN last_rotated_at DATETIME DEFAULT NULL;

// In API controllers, check expiry
if ($apiKey['expires_at'] && strtotime($apiKey['expires_at']) < time()) {
    $this->json(['error' => 'API key has expired'], 401);
}
```

### 7. Session Security Enhancements

**Add to Session::start()**:
```php
// Prevent session fixation
if (!self::has('initiated')) {
    session_regenerate_id(true);
    self::set('initiated', true);
}

// Add session fingerprint
$fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
if (self::has('fingerprint') && self::get('fingerprint') !== $fingerprint) {
    self::destroy();
    die('Session validation failed');
}
self::set('fingerprint', $fingerprint);
```

### 8. Two-Factor Authentication (2FA)

**Recommended Addition**:
- Add `users.two_factor_secret` column
- Implement TOTP using Google Authenticator
- Add 2FA enrollment and verification flow

## Scalability Improvements

### 1. Database Indexing

**Recommended Indexes**:

```sql
-- Performance indexes for common queries
CREATE INDEX idx_users_email_tenant ON users(email, tenant_id);
CREATE INDEX idx_users_role_status ON users(role, status, tenant_id);

CREATE INDEX idx_audit_plans_status_dates ON audit_plans(status, planned_start, planned_end, tenant_id);
CREATE INDEX idx_audit_plans_lead ON audit_plans(lead_auditor_id, tenant_id);

CREATE INDEX idx_findings_audit_severity ON findings(audit_plan_id, severity, tenant_id);
CREATE INDEX idx_findings_assigned_status ON findings(assigned_to_user_id, status, tenant_id);

CREATE INDEX idx_corrective_actions_finding ON corrective_actions(finding_id, tenant_id);
CREATE INDEX idx_corrective_actions_responsible_status ON corrective_actions(responsible_user_id, status, tenant_id);
CREATE INDEX idx_corrective_actions_due_status ON corrective_actions(due_date, status, tenant_id);

CREATE INDEX idx_risks_category_status ON risks(category, status, tenant_id);
CREATE INDEX idx_risks_inherent ON risks(inherent_risk DESC, tenant_id);

CREATE INDEX idx_files_module ON files(module_type, module_id, tenant_id);

CREATE INDEX idx_notifications_user_status ON notifications(user_id, status, tenant_id);

CREATE INDEX idx_activity_logs_tenant_date ON activity_logs(tenant_id, created_at);
CREATE INDEX idx_activity_logs_user_module ON activity_logs(user_id, module, created_at);
```

### 2. Query Optimization

**Implement Eager Loading**:
```php
// Example: Load audit plan with related data in one query
public function getAuditPlanWithDetails($id) {
    $sql = "SELECT
                ap.*,
                u.first_name as auditor_first_name,
                u.last_name as auditor_last_name,
                prog.title as program_title,
                COUNT(DISTINCT f.id) as findings_count,
                COUNT(DISTINCT ca.id) as actions_count
            FROM audit_plans ap
            LEFT JOIN users u ON ap.lead_auditor_id = u.id
            LEFT JOIN audit_programs prog ON ap.program_id = prog.id
            LEFT JOIN findings f ON f.audit_plan_id = ap.id
            LEFT JOIN corrective_actions ca ON ca.finding_id = f.id
            WHERE ap.id = ? AND ap.tenant_id = ?
            GROUP BY ap.id";

    return $this->db->fetchOne($sql, [$id, Auth::tenantId()]);
}
```

### 3. Caching Layer

**Recommended Implementation**:

```php
// Create app/core/Cache.php
class Cache {
    private $cacheDir;

    public function __construct() {
        $this->cacheDir = STORAGE_PATH . '/cache/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key, $default = null) {
        $file = $this->cacheDir . md5($key) . '.cache';

        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires'] < time()) {
            unlink($file);
            return $default;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = 3600) {
        $file = $this->cacheDir . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];

        file_put_contents($file, serialize($data));
    }

    public function forget($key) {
        $file = $this->cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

// Usage example:
$cache = new Cache();
$stats = $cache->get('dashboard_stats_' . Auth::tenantId());

if (!$stats) {
    $stats = $this->calculateStatistics();
    $cache->set('dashboard_stats_' . Auth::tenantId(), $stats, 300); // 5 minutes
}
```

### 4. Pagination Implementation

**Add to Model class**:
```php
public function paginate($page = 1, $perPage = 20, $where = [], $orderBy = 'id DESC') {
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT * FROM {$this->table}";
    $params = [];

    if ($this->tenantIsolation && Auth::check()) {
        $where['tenant_id'] = Auth::tenantId();
    }

    if (!empty($where)) {
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "$key = ?";
            $params[] = $value;
        }
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY $orderBy LIMIT $perPage OFFSET $offset";

    $items = $this->db->fetchAll($sql, $params);

    // Get total count
    $countSql = "SELECT COUNT(*) as count FROM {$this->table}";
    if (!empty($where)) {
        $countSql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $total = $this->db->fetchOne($countSql, $params);

    return [
        'data' => $items,
        'total' => (int)$total['count'],
        'per_page' => $perPage,
        'current_page' => $page,
        'last_page' => ceil($total['count'] / $perPage)
    ];
}
```

### 5. Database Connection Pooling

**Recommendation**: In production, use persistent connections

```php
// In Database.php constructor
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_PERSISTENT => true // Add this for connection pooling
];
```

### 6. Asynchronous Job Processing

**Recommendation**: Implement background job queue for:
- Email sending
- Report generation
- Bulk data imports
- Notification processing

```php
// Create app/core/JobQueue.php
class JobQueue {
    public function push($job, $data) {
        // Store job in database queue
        $db = Database::getInstance();
        $db->query(
            "INSERT INTO job_queue (job_class, payload, status, created_at) VALUES (?, ?, 'pending', NOW())",
            [$job, json_encode($data)]
        );
    }
}

// Create worker script: worker.php
// Process jobs in background using cron or supervisor
```

### 7. File Storage Optimization

**Recommendation**: Use cloud storage for uploaded files

```php
// Create app/helpers/CloudStorage.php (example for S3)
class CloudStorage {
    public function upload($file, $path) {
        // Upload to S3, Azure Blob, or Google Cloud Storage
        // Return public URL
    }

    public function delete($path) {
        // Delete from cloud storage
    }

    public function getUrl($path) {
        // Return signed URL for temporary access
    }
}
```

## Architectural Considerations

### 1. Microservices Readiness

**Current State**: Monolithic architecture

**Future Consideration**: The codebase can be split into:
- **Auth Service**: User authentication and authorization
- **Audit Service**: Audit plans, findings, corrective actions
- **Risk Service**: Risk assessment and management
- **Reporting Service**: Report generation and analytics
- **Notification Service**: Email and alerts

### 2. API Versioning

**Recommendation**: Implement API versioning

```php
// Route: /api/v1/findings/create
class ApiV1FindingsController extends Controller {
    // Version 1 implementation
}

// Future: /api/v2/findings/create
class ApiV2FindingsController extends Controller {
    // Version 2 with breaking changes
}
```

### 3. Event-Driven Architecture

**Recommendation**: Implement event system

```php
// Create app/core/Event.php
class Event {
    private static $listeners = [];

    public static function listen($event, $callback) {
        self::$listeners[$event][] = $callback;
    }

    public static function trigger($event, $data = []) {
        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $listener) {
                call_user_func($listener, $data);
            }
        }
    }
}

// Usage:
Event::listen('finding.created', function($finding) {
    Email::sendFindingAssignedNotification($finding, $user);
    // Log to activity
    // Update statistics
});

Event::trigger('finding.created', $findingData);
```

### 4. Dependency Injection Container

**Recommendation**: Implement DI container for better testability

```php
// Create app/core/Container.php
class Container {
    private $bindings = [];

    public function bind($abstract, $concrete) {
        $this->bindings[$abstract] = $concrete;
    }

    public function make($abstract) {
        if (isset($this->bindings[$abstract])) {
            return call_user_func($this->bindings[$abstract]);
        }
        return new $abstract();
    }
}
```

### 5. Repository Pattern

**Recommendation**: Implement repository pattern for data access

```php
// Create repositories for complex queries
class AuditPlanRepository {
    private $db;

    public function findUpcomingWithTeam($days = 30) {
        // Complex query logic here
    }

    public function findOverdueWithActions() {
        // Complex query logic here
    }
}
```

### 6. Service Layer

**Recommendation**: Extract business logic to service classes

```php
// Create app/services/AuditService.php
class AuditService {
    public function createAuditPlan($data) {
        // Validate quota
        // Create plan
        // Send notifications
        // Log activity
        // Return result
    }
}
```

## Performance Monitoring

### Recommended Tools

1. **Query Profiling**: Enable MySQL slow query log
2. **Application Profiling**: Use Xdebug in development
3. **Error Tracking**: Implement Sentry or similar
4. **APM**: Consider New Relic or Datadog for production

### Monitoring Queries

```php
// Add query logging in development
class Database {
    private $queryLog = [];

    public function query($sql, $params = []) {
        $start = microtime(true);
        $stmt = parent::query($sql, $params);
        $time = microtime(true) - $start;

        if (APP_ENV === 'development') {
            $this->queryLog[] = [
                'sql' => $sql,
                'params' => $params,
                'time' => $time
            ];
        }

        return $stmt;
    }

    public function getQueryLog() {
        return $this->queryLog;
    }
}
```

## Code Quality Recommendations

### 1. Unit Testing

**Recommendation**: Implement PHPUnit tests

```php
// tests/UserModelTest.php
class UserModelTest extends PHPUnit\Framework\TestCase {
    public function testCreateUser() {
        $user = new User();
        $id = $user->createUser([
            'email' => 'test@example.com',
            'password' => 'Test@123',
            'first_name' => 'Test',
            'last_name' => 'User'
        ]);

        $this->assertGreaterThan(0, $id);
    }
}
```

### 2. Code Documentation

**Recommendation**: Add PHPDoc blocks

```php
/**
 * Create a new audit plan
 *
 * @param array $data Audit plan data
 * @return int Created audit plan ID
 * @throws QuotaExceededException
 */
public function createAuditPlan($data) {
    // Implementation
}
```

### 3. Logging Standards

**Recommendation**: Implement PSR-3 compatible logger

```php
// Use Monolog or custom logger
class Logger {
    public function error($message, $context = []) {
        error_log("[ERROR] " . $message . " " . json_encode($context));
    }

    public function info($message, $context = []) {
        error_log("[INFO] " . $message . " " . json_encode($context));
    }
}
```

## Production Deployment Checklist

- [ ] Change all default passwords
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Configure proper error logging (not display)
- [ ] Set secure session cookie settings
- [ ] Implement rate limiting
- [ ] Enable database query caching
- [ ] Configure automated backups
- [ ] Set up monitoring and alerting
- [ ] Implement log rotation
- [ ] Review and tighten file permissions
- [ ] Configure firewall rules
- [ ] Set up CDN for static assets
- [ ] Enable gzip compression
- [ ] Configure opcache for PHP
- [ ] Set up database replication (if needed)
- [ ] Implement DDoS protection
- [ ] Configure mail service (SMTP)
- [ ] Set up cron jobs for notifications
- [ ] Review API rate limits
- [ ] Document deployment process

## Conclusion

The SplashAudit system provides a solid foundation for a production-ready internal audit SaaS platform. The recommended improvements focus on:

1. **Security**: Enhanced authentication, rate limiting, and data protection
2. **Scalability**: Caching, indexing, and query optimization
3. **Architecture**: Event-driven design, service layer, and microservices readiness
4. **Maintainability**: Testing, documentation, and code organization

Implementing these recommendations will ensure the system can scale to support thousands of tenants and millions of records while maintaining security and performance.
