# SplashAudit - Testing Checklist

## Pre-Testing Setup

- [ ] Database created and schema imported from `database.sql`
- [ ] `.env` file configured with correct database credentials
- [ ] Web server configured to serve from `public/` directory
- [ ] Storage directories have write permissions (755)
- [ ] PHP version 7.0+ confirmed
- [ ] Required PHP extensions installed (PDO, Fileinfo)

## 1. Authentication & Authorization Tests

### Registration
- [ ] New tenant registration with valid data succeeds
- [ ] Duplicate subdomain is rejected
- [ ] Duplicate email within tenant is rejected
- [ ] Weak passwords are rejected
- [ ] Password confirmation mismatch is rejected
- [ ] Default subscription plan is assigned
- [ ] 30-day trial period is set
- [ ] Tenant admin user is created automatically
- [ ] API key is generated automatically
- [ ] Welcome email is logged/sent

### Login
- [ ] Valid credentials allow login
- [ ] Invalid email is rejected
- [ ] Invalid password is rejected
- [ ] Inactive user cannot login
- [ ] Suspended tenant user cannot login
- [ ] Last login timestamp is updated
- [ ] Session is created successfully
- [ ] CSRF token is generated

### Logout
- [ ] Logout destroys session
- [ ] Redirect to login page after logout
- [ ] Cannot access protected pages after logout

### Authorization (RBAC)
- [ ] Platform admin can access all platform features
- [ ] Tenant admin can manage users
- [ ] Tenant admin can manage audit programs
- [ ] Audit manager can create audit plans
- [ ] Internal auditor can create findings
- [ ] Process owner can create corrective actions
- [ ] Reviewer can review corrective actions
- [ ] Viewer has read-only access
- [ ] Users cannot access features outside their role

## 2. Tenant Isolation Tests

### Data Isolation
- [ ] User from Tenant A cannot see Tenant B's users
- [ ] User from Tenant A cannot see Tenant B's audit plans
- [ ] User from Tenant A cannot see Tenant B's findings
- [ ] User from Tenant A cannot see Tenant B's risks
- [ ] User from Tenant A cannot see Tenant B's files
- [ ] Direct ID manipulation is blocked by tenant filter

### URL Manipulation
- [ ] Attempting to access another tenant's data by ID fails
- [ ] Tenant ID in session matches database records
- [ ] All queries include tenant_id filter

## 3. Subscription & Quota Tests

### Plan Limits
- [ ] Cannot create audit plan when quota reached
- [ ] Cannot create checklist when quota reached
- [ ] Cannot create user when quota reached
- [ ] Cannot upload file when storage quota reached
- [ ] Cannot create corrective action when quota reached
- [ ] Quota usage statistics are accurate

### Trial Period
- [ ] New tenant shows "trialing" status
- [ ] Trial end date is set to +30 days
- [ ] All features accessible during trial
- [ ] Trial period is visible in dashboard

### Feature Access
- [ ] Basic plan blocks advanced features
- [ ] Professional plan enables custom fields
- [ ] Professional plan enables advanced reports
- [ ] Enterprise plan enables API access
- [ ] Feature flags are checked correctly

## 4. Audit Module Tests

### Audit Universe
- [ ] Create new auditable entity
- [ ] Edit existing entity
- [ ] Delete entity
- [ ] List entities by category
- [ ] Assign owner to entity
- [ ] Set risk score
- [ ] View high-risk entities

### Audit Programs
- [ ] Create audit program for current year
- [ ] Edit program details
- [ ] Set program status (draft/approved/archived)
- [ ] Link universe items to program
- [ ] View programs by year

### Audit Plans
- [ ] Create new audit plan
- [ ] Quota enforcement works
- [ ] Assign lead auditor
- [ ] Set planned dates
- [ ] Link to audit program
- [ ] Change plan status (scheduled/in_progress/completed)
- [ ] View upcoming audits (30 days)
- [ ] View overdue audits
- [ ] Cannot exceed audit plan quota

### Audit Checklists
- [ ] Create new checklist
- [ ] Add checklist items
- [ ] Reorder checklist items (sort_order)
- [ ] Assign checklist to audit plan
- [ ] Execute checklist (pass/fail/N/A)
- [ ] Attach evidence to checklist item

## 5. Risk Management Tests

### Risk Register
- [ ] Create new risk
- [ ] Set likelihood (1-5)
- [ ] Set impact (1-5)
- [ ] Inherent risk calculation (likelihood × impact)
- [ ] Set control effectiveness
- [ ] Residual risk calculation
- [ ] Edit risk details
- [ ] Change risk status
- [ ] View risks by category
- [ ] View high-risk items

### Risk Heatmap
- [ ] Heatmap displays correctly
- [ ] Risks grouped by likelihood/impact
- [ ] Data matches risk register

## 6. Findings & Corrective Actions Tests

### Findings
- [ ] Create finding from audit plan
- [ ] Set severity (high/medium/low)
- [ ] Assign to user
- [ ] Email notification sent to assignee
- [ ] View findings by audit plan
- [ ] View findings by severity
- [ ] View findings by status
- [ ] Close finding

### Corrective Actions
- [ ] Create corrective action for finding
- [ ] Quota enforcement works
- [ ] Set due date
- [ ] Assign responsible user
- [ ] Upload evidence file
- [ ] Update action status
- [ ] View overdue actions
- [ ] View upcoming due actions (7 days)
- [ ] Email notification for overdue actions

### Corrective Action Reviews
- [ ] Submit action for review
- [ ] Reviewer can approve/reject
- [ ] Comments are saved
- [ ] Status updated after review

## 7. Compliance Module Tests

### Compliance Areas
- [ ] Create compliance area (ISO 9001, SOX, GDPR)
- [ ] Add requirements to area
- [ ] Set requirement codes

### Compliance Checks
- [ ] Perform compliance check
- [ ] Set compliance status (compliant/non-compliant/partial)
- [ ] Link to audit plan
- [ ] Upload evidence
- [ ] View compliance status report

## 8. File Upload & Evidence Repository Tests

### File Upload
- [ ] Upload PDF file succeeds
- [ ] Upload DOCX file succeeds
- [ ] Upload XLSX file succeeds
- [ ] Upload JPG/PNG image succeeds
- [ ] Reject .exe file
- [ ] Reject file exceeding size limit (10MB)
- [ ] MIME type validation works
- [ ] Unique filename generated
- [ ] File metadata saved to database
- [ ] Storage quota updated after upload

### File Download
- [ ] Download file with correct permissions
- [ ] Cannot download another tenant's file
- [ ] File name and content type correct
- [ ] Original filename preserved

### File Deletion
- [ ] Delete file from storage
- [ ] Delete file record from database
- [ ] Storage quota updated after deletion

## 9. Notifications & Email Tests

### Email Logging
- [ ] Emails logged to database
- [ ] Emails logged to file (storage/logs/emails.log)
- [ ] Notification status set to "sent"

### Notification Types
- [ ] Audit deadline notification
- [ ] Corrective action overdue notification
- [ ] Finding assigned notification
- [ ] Evidence requested notification
- [ ] Welcome email for new user
- [ ] User notifications viewable in UI

## 10. Dashboard Tests

### Platform Admin Dashboard
- [ ] Total tenants count correct
- [ ] Active tenants count correct
- [ ] Recent tenants list displayed

### Tenant Admin Dashboard
- [ ] Audit statistics accurate
- [ ] Finding statistics accurate
- [ ] Corrective action statistics accurate
- [ ] Quota usage displayed correctly
- [ ] Overdue audits shown
- [ ] Overdue actions shown
- [ ] High severity findings shown
- [ ] Quick action links work

### Auditor Dashboard
- [ ] Assigned audits displayed
- [ ] Assigned findings displayed
- [ ] Upcoming audits shown

### Process Owner Dashboard
- [ ] Assigned findings displayed
- [ ] Assigned corrective actions displayed
- [ ] Overdue actions highlighted

### Viewer Dashboard
- [ ] Read-only message displayed
- [ ] Cannot access create/edit functions

## 11. Reports & Export Tests

### Audit Summary Report
- [ ] Report displays all audit plans
- [ ] Data is accurate
- [ ] CSV export works
- [ ] CSV contains correct headers
- [ ] CSV data matches screen

### Findings by Severity Report
- [ ] Findings grouped by severity
- [ ] Statistics accurate
- [ ] CSV export works

### Corrective Actions Status Report
- [ ] Actions grouped by status
- [ ] Due dates displayed
- [ ] CSV export works

### Risk Register Export
- [ ] All risks exported
- [ ] Risk calculations correct
- [ ] CSV export works

## 12. REST API Tests

### Authentication
- [ ] Missing API key returns 401
- [ ] Invalid API key returns 401
- [ ] Valid API key authenticates
- [ ] API key last_used_at updated

### Create Finding Endpoint
- [ ] POST /api/findings/create with valid data succeeds
- [ ] Returns finding_id
- [ ] Missing required fields returns 400
- [ ] Invalid severity returns 400
- [ ] Finding saved to correct tenant

### Add Corrective Action Endpoint
- [ ] POST /api/corrective-actions/add with valid data succeeds
- [ ] Returns corrective_action_id
- [ ] Missing required fields returns 400
- [ ] Action saved to correct tenant

### Get Audit Status Endpoint
- [ ] GET /api/audits/status?audit_plan_id=X returns correct data
- [ ] Missing audit_plan_id returns 400
- [ ] Non-existent audit returns 404
- [ ] Findings count accurate
- [ ] Cannot access another tenant's audit

### List Findings Endpoint
- [ ] GET /api/findings/list returns findings
- [ ] Filter by status works
- [ ] Only tenant's findings returned
- [ ] Count is accurate

## 13. Security Tests

### CSRF Protection
- [ ] Forms include CSRF token
- [ ] Submit without CSRF token fails
- [ ] Submit with invalid CSRF token fails
- [ ] Submit with valid CSRF token succeeds

### SQL Injection
- [ ] Input: `' OR '1'='1` in login fails
- [ ] Input: `'; DROP TABLE users; --` fails
- [ ] All queries use prepared statements
- [ ] No raw SQL with user input

### XSS Protection
- [ ] Script tags in input are escaped in output
- [ ] Input: `<script>alert('XSS')</script>` is sanitized
- [ ] htmlspecialchars used on all output
- [ ] User-generated content safely displayed

### Session Security
- [ ] Session regenerated on login
- [ ] Session destroyed on logout
- [ ] Session timeout works (2 hours default)
- [ ] Session hijacking prevented (fingerprint check)

### File Upload Security
- [ ] PHP file upload rejected
- [ ] .htaccess file upload rejected
- [ ] Files stored outside webroot or with protection
- [ ] MIME type validation enforced

### Password Security
- [ ] Passwords hashed with bcrypt
- [ ] Plain text password never stored
- [ ] Password verify function works
- [ ] Failed login doesn't reveal if email exists

## 14. User Management Tests

### Create User
- [ ] Tenant admin can create user
- [ ] Quota enforcement works
- [ ] Email uniqueness enforced within tenant
- [ ] Different tenants can have same email
- [ ] Welcome email sent
- [ ] User appears in user list

### Edit User
- [ ] Can edit user details
- [ ] Can change user role
- [ ] Can change user status
- [ ] Cannot edit user from another tenant

### Delete User
- [ ] Can delete user
- [ ] User removed from database
- [ ] Related data handled (nullified or cascaded)
- [ ] Cannot delete user from another tenant

## 15. Activity Logging Tests

### Activity Log
- [ ] Login activity logged
- [ ] Logout activity logged
- [ ] Create operations logged
- [ ] Update operations logged
- [ ] Delete operations logged
- [ ] IP address captured
- [ ] User agent captured
- [ ] Timestamp accurate (UTC)

## 16. Browser Compatibility Tests

- [ ] Chrome - Layout correct
- [ ] Firefox - Layout correct
- [ ] Safari - Layout correct
- [ ] Edge - Layout correct
- [ ] Mobile responsive (iPhone)
- [ ] Mobile responsive (Android)
- [ ] Tablet responsive (iPad)

## 17. Performance Tests

### Load Time
- [ ] Login page loads < 2 seconds
- [ ] Dashboard loads < 3 seconds
- [ ] Report generation < 5 seconds
- [ ] Large list pagination works

### Database Performance
- [ ] Queries use indexes (EXPLAIN)
- [ ] No N+1 query problems
- [ ] Queries complete < 100ms

### Concurrent Users
- [ ] 10 concurrent logins successful
- [ ] 50 concurrent page loads successful
- [ ] No session conflicts
- [ ] No database deadlocks

## 18. Error Handling Tests

### 404 Errors
- [ ] Non-existent URL shows friendly error
- [ ] Non-existent resource shows friendly error

### 500 Errors
- [ ] Database connection error handled gracefully
- [ ] File system error handled gracefully
- [ ] Errors logged to file
- [ ] User sees friendly error message (production)

### Validation Errors
- [ ] Form validation errors displayed
- [ ] Error messages are clear and helpful
- [ ] Focus returns to error field

## 19. Backup & Recovery Tests

### Database Backup
- [ ] Database backup command documented
- [ ] Backup can be restored successfully
- [ ] Data integrity maintained after restore

### File Backup
- [ ] File backup command documented
- [ ] Files can be restored successfully

## 20. Edge Cases & Stress Tests

### Edge Cases
- [ ] Empty results handled (no records)
- [ ] Very long text input (10,000 chars)
- [ ] Special characters in input (unicode, emoji)
- [ ] Date boundary cases (leap year, etc.)
- [ ] Null values handled correctly

### Stress Tests
- [ ] 1,000 users in one tenant
- [ ] 10,000 findings in one tenant
- [ ] 100 concurrent API requests
- [ ] Upload 1,000 files
- [ ] Generate report with 50,000 records

## Post-Testing Checklist

- [ ] All critical bugs fixed
- [ ] Security vulnerabilities addressed
- [ ] Performance issues resolved
- [ ] Documentation updated
- [ ] User training materials prepared
- [ ] Deployment plan reviewed
- [ ] Rollback plan prepared
- [ ] Monitoring configured
- [ ] Backup schedule configured
- [ ] Support process defined

## Testing Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| Developer | | | |
| QA Lead | | | |
| Security | | | |
| Product Owner | | | |

---

## Notes

- **Priority Levels**:
  - Critical: Must pass before production
  - High: Should pass before production
  - Medium: Can be addressed post-launch
  - Low: Nice to have

- **Test Environment**:
  - PHP Version: _______
  - MySQL Version: _______
  - OS: _______
  - Browser: _______

- **Test Data**:
  - Use separate test database
  - Do not test on production data
  - Reset test data between test runs

- **Bug Tracking**:
  - Log all bugs with severity level
  - Include steps to reproduce
  - Assign to developer
  - Retest after fix
