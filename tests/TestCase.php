<?php
/**
 * Base Test Case
 * Provides common functionality for all tests
 */

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase {
    protected $db;

    /**
     * Setup before each test
     */
    protected function setUp(): void {
        parent::setUp();

        // Reset database connection for each test
        $this->setupDatabase();

        // Clear session
        $_SESSION = [];

        // Reset $_SERVER superglobal
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
    }

    /**
     * Setup test database
     */
    protected function setupDatabase() {
        try {
            $this->db = Database::getInstance();

            // Start transaction for test isolation
            $this->db->query('START TRANSACTION');
        } catch (Exception $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }
    }

    /**
     * Teardown after each test
     */
    protected function tearDown(): void {
        // Rollback transaction to keep database clean
        if ($this->db) {
            try {
                $this->db->query('ROLLBACK');
            } catch (Exception $e) {
                // Ignore rollback errors
            }
        }

        parent::tearDown();
    }

    /**
     * Create a test tenant
     */
    protected function createTestTenant($data = []) {
        $defaults = [
            'company_name' => 'Test Company',
            'subdomain' => 'test' . rand(1000, 9999),
            'status' => 'active',
            'subscription_plan_id' => 1
        ];

        $tenantData = array_merge($defaults, $data);

        $sql = "INSERT INTO tenants (company_name, subdomain, status, subscription_plan_id)
                VALUES (?, ?, ?, ?)";

        $this->db->query($sql, [
            $tenantData['company_name'],
            $tenantData['subdomain'],
            $tenantData['status'],
            $tenantData['subscription_plan_id']
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Create a test user
     */
    protected function createTestUser($tenantId, $data = []) {
        $defaults = [
            'email' => 'test' . rand(1000, 9999) . '@example.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'first_name' => 'Test',
            'last_name' => 'User',
            'role' => 'internal_auditor',
            'status' => 'active'
        ];

        $userData = array_merge($defaults, $data);

        $sql = "INSERT INTO users (tenant_id, email, password, first_name, last_name, role, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $this->db->query($sql, [
            $tenantId,
            $userData['email'],
            $userData['password'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['role'],
            $userData['status']
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Authenticate test user
     */
    protected function actingAs($userId, $tenantId) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['tenant_id'] = $tenantId;
        $_SESSION['authenticated'] = true;
    }

    /**
     * Assert array has keys
     */
    protected function assertArrayHasKeys(array $keys, array $array, $message = '') {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message);
        }
    }

    /**
     * Assert SQL injection is prevented
     */
    protected function assertSqlInjectionPrevented($input, $result) {
        // Common SQL injection patterns should not work
        $this->assertNotContains('DROP', strtoupper($result));
        $this->assertNotContains('DELETE', strtoupper($result));
        $this->assertNotContains('UNION', strtoupper($result));
        $this->assertNotContains('--', $result);
        $this->assertNotContains('/*', $result);
    }

    /**
     * Assert XSS is prevented
     */
    protected function assertXssPrevented($input, $output) {
        // Script tags should be escaped
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringNotContainsString('javascript:', $output);
        $this->assertStringNotContainsString('onerror=', $output);
        $this->assertStringNotContainsString('onclick=', $output);
    }
}
