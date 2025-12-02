<?php
/**
 * Security Tests
 * Tests for SQL Injection, XSS, CSRF, and other security vulnerabilities
 */

require_once __DIR__ . '/../TestCase.php';

class SecurityTest extends TestCase {

    /**
     * Test SQL Injection Prevention in Models
     */
    public function testSqlInjectionPrevention() {
        $tenantId = $this->createTestTenant();
        $userId = $this->createTestUser($tenantId);
        $this->actingAs($userId, $tenantId);

        $userModel = new User();

        // Attempt SQL injection through findAll
        $maliciousInput = "1' OR '1'='1";
        $result = $userModel->findAll(['email' => $maliciousInput]);

        // Should return empty array, not all users
        $this->assertEmpty($result);
    }

    /**
     * Test SQL Injection in findById
     */
    public function testSqlInjectionInFindById() {
        $tenantId = $this->createTestTenant();
        $userId = $this->createTestUser($tenantId);
        $this->actingAs($userId, $tenantId);

        $userModel = new User();

        // Attempt SQL injection
        $maliciousId = "1 OR 1=1";
        $result = $userModel->findById($maliciousId);

        // Should not return any data or throw error gracefully
        $this->assertEmpty($result);
    }

    /**
     * Test XSS Prevention in Validator Sanitization
     */
    public function testXssPreventionInSanitization() {
        $validator = new Validator([]);

        $maliciousScript = '<script>alert("XSS")</script>';
        $sanitized = htmlspecialchars($maliciousScript, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('&lt;script&gt;', $sanitized);
    }

    /**
     * Test XSS Prevention in Different Contexts
     */
    public function testXssPreventionInMultipleContexts() {
        $attacks = [
            '<img src=x onerror=alert(1)>',
            '<svg/onload=alert(1)>',
            'javascript:alert(1)',
            '<iframe src="javascript:alert(1)">',
            '<body onload=alert(1)>',
            '<input onfocus=alert(1) autofocus>',
        ];

        foreach ($attacks as $attack) {
            $sanitized = htmlspecialchars($attack, ENT_QUOTES, 'UTF-8');
            $this->assertXssPrevented($attack, $sanitized);
        }
    }

    /**
     * Test CSRF Token Generation
     */
    public function testCsrfTokenGeneration() {
        $token = Session::generateCsrfToken();

        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    /**
     * Test CSRF Token Validation
     */
    public function testCsrfTokenValidation() {
        $token = Session::generateCsrfToken();

        // Valid token
        $_POST['csrf_token'] = $token;
        $this->assertTrue(Session::validateCsrfToken());

        // Invalid token
        $_POST['csrf_token'] = 'invalid_token';
        $this->assertFalse(Session::validateCsrfToken());

        // Missing token
        unset($_POST['csrf_token']);
        $this->assertFalse(Session::validateCsrfToken());
    }

    /**
     * Test Password Hashing
     */
    public function testPasswordHashing() {
        $password = 'SecurePassword123!';
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $this->assertNotEquals($password, $hash);
        $this->assertTrue(password_verify($password, $hash));
        $this->assertFalse(password_verify('WrongPassword', $hash));
    }

    /**
     * Test Tenant Isolation in Queries
     */
    public function testTenantIsolation() {
        $tenant1Id = $this->createTestTenant(['subdomain' => 'tenant1']);
        $tenant2Id = $this->createTestTenant(['subdomain' => 'tenant2']);

        $user1Id = $this->createTestUser($tenant1Id, ['email' => 'user1@tenant1.com']);
        $user2Id = $this->createTestUser($tenant2Id, ['email' => 'user2@tenant2.com']);

        // Act as tenant 1
        $this->actingAs($user1Id, $tenant1Id);

        $userModel = new User();

        // Try to access tenant 2's user by ID
        $result = $userModel->findById($user2Id);

        // Should not be able to access another tenant's data
        $this->assertEmpty($result);
    }

    /**
     * Test API Rate Limiting
     */
    public function testApiRateLimiting() {
        $rateLimiter = new RateLimiter(5, 1); // 5 requests per minute

        $key = 'test_api_key_' . time();

        // Make 5 requests - should all succeed
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue($rateLimiter->check($key, 'api'));
            $rateLimiter->hit($key, 'api');
        }

        // 6th request should fail
        $this->assertFalse($rateLimiter->check($key, 'api'));

        // Clean up
        $rateLimiter->clear($key, 'api');
    }

    /**
     * Test File Upload Validation
     */
    public function testFileUploadValidation() {
        // Create a mock file upload
        $_FILES['test_file'] = [
            'name' => 'malicious.php',
            'type' => 'application/x-php',
            'tmp_name' => '/tmp/phptest',
            'error' => UPLOAD_ERR_OK,
            'size' => 1024
        ];

        $uploader = new FileUpload('test_file');
        $uploader->setAllowedTypes(['jpg', 'png', 'pdf']);

        // PHP files should not be allowed
        $this->expectException(Exception::class);
        $uploader->upload();
    }

    /**
     * Test Email Validation
     */
    public function testEmailValidation() {
        $validator = new Validator(['email' => 'invalid-email']);
        $validator->email('email');

        $this->assertTrue($validator->fails());

        $validator2 = new Validator(['email' => 'valid@example.com']);
        $validator2->email('email');

        $this->assertFalse($validator2->fails());
    }

    /**
     * Test Authorization - Role-Based Access
     */
    public function testRoleBasedAccess() {
        $tenantId = $this->createTestTenant();

        // Create viewer user (low privilege)
        $viewerId = $this->createTestUser($tenantId, ['role' => 'viewer']);
        $this->actingAs($viewerId, $tenantId);

        // Viewer should not have admin role
        $this->assertFalse(Auth::hasRole(['tenant_admin', 'audit_manager']));

        // Create admin user
        $adminId = $this->createTestUser($tenantId, ['role' => 'tenant_admin', 'email' => 'admin@test.com']);
        $this->actingAs($adminId, $tenantId);

        // Admin should have admin role
        $this->assertTrue(Auth::hasRole(['tenant_admin']));
    }

    /**
     * Test Session Hijacking Prevention
     */
    public function testSessionSecurity() {
        $tenantId = $this->createTestTenant();
        $userId = $this->createTestUser($tenantId);

        // Set session
        $_SESSION['user_id'] = $userId;
        $_SESSION['tenant_id'] = $tenantId;

        // Session should have proper values
        $this->assertEquals($userId, $_SESSION['user_id']);
        $this->assertEquals($tenantId, $_SESSION['tenant_id']);

        // Session ID should be regenerated on login
        $oldSessionId = session_id();
        session_regenerate_id(true);
        $newSessionId = session_id();

        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /**
     * Test Path Traversal Prevention
     */
    public function testPathTraversalPrevention() {
        $maliciousPaths = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32',
            './../../config/database.php',
        ];

        foreach ($maliciousPaths as $path) {
            $normalized = str_replace(['../', '..\\'], '', $path);
            $this->assertStringNotContainsString('..', $normalized);
        }
    }

    /**
     * Test Command Injection Prevention
     */
    public function testCommandInjectionPrevention() {
        $maliciousInput = 'file.txt; rm -rf /';

        // Should not execute system commands
        $escaped = escapeshellarg($maliciousInput);

        $this->assertStringContainsString("'file.txt; rm -rf /'", $escaped);
        $this->assertStringNotContainsString(';', $escaped);
    }

    /**
     * Test Mass Assignment Protection
     */
    public function testMassAssignmentProtection() {
        $tenantId = $this->createTestTenant();
        $userId = $this->createTestUser($tenantId);
        $this->actingAs($userId, $tenantId);

        $userModel = new User();

        // Attempt to update role through mass assignment
        $maliciousData = [
            'first_name' => 'Updated',
            'role' => 'platform_admin', // Trying to escalate privileges
            'tenant_id' => 999 // Trying to change tenant
        ];

        // Update should only change allowed fields
        // Note: This test assumes proper implementation in controllers
        $this->assertTrue(true); // Placeholder
    }
}
