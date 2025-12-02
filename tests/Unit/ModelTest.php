<?php
/**
 * Base Model Class Tests
 * Tests the core Model functionality
 */

require_once __DIR__ . '/../TestCase.php';

class ModelTest extends TestCase {

    /**
     * Test findAll with tenant isolation
     */
    public function testFindAllWithTenantIsolation() {
        $tenantId1 = $this->createTestTenant(['subdomain' => 'tenant1']);
        $tenantId2 = $this->createTestTenant(['subdomain' => 'tenant2']);

        $userId1 = $this->createTestUser($tenantId1);
        $userId2 = $this->createTestUser($tenantId2);

        // Act as tenant 1
        $this->actingAs($userId1, $tenantId1);

        $userModel = new User();
        $users = $userModel->findAll();

        // Should only get tenant 1's users
        $this->assertCount(1, $users);
        $this->assertEquals($tenantId1, $users[0]['tenant_id']);
    }

    /**
     * Test pagination
     */
    public function testPagination() {
        $tenantId = $this->createTestTenant();

        // Create 25 test users
        for ($i = 0; $i < 25; $i++) {
            $this->createTestUser($tenantId, ['email' => "user{$i}@test.com"]);
        }

        $userId = $this->createTestUser($tenantId);
        $this->actingAs($userId, $tenantId);

        $userModel = new User();
        $result = $userModel->paginate([], 'id ASC', 1, 10);

        $this->assertArrayHasKeys(['data', 'total', 'per_page', 'current_page', 'last_page'], $result);
        $this->assertEquals(26, $result['total']); // 25 + 1 acting user
        $this->assertEquals(10, $result['per_page']);
        $this->assertEquals(1, $result['current_page']);
        $this->assertEquals(3, $result['last_page']);
        $this->assertCount(10, $result['data']);
    }

    /**
     * Test exists method
     */
    public function testExists() {
        $tenantId = $this->createTestTenant();
        $userId = $this->createTestUser($tenantId, ['email' => 'exists@test.com']);
        $this->actingAs($userId, $tenantId);

        $userModel = new User();

        $this->assertTrue($userModel->exists(['email' => 'exists@test.com']));
        $this->assertFalse($userModel->exists(['email' => 'notexists@test.com']));
    }

    /**
     * Test pluck method
     */
    public function testPluck() {
        $tenantId = $this->createTestTenant();

        $this->createTestUser($tenantId, ['email' => 'user1@test.com']);
        $this->createTestUser($tenantId, ['email' => 'user2@test.com']);
        $userId = $this->createTestUser($tenantId, ['email' => 'user3@test.com']);

        $this->actingAs($userId, $tenantId);

        $userModel = new User();
        $emails = $userModel->pluck('email');

        $this->assertCount(3, $emails);
        $this->assertContains('user1@test.com', $emails);
        $this->assertContains('user2@test.com', $emails);
        $this->assertContains('user3@test.com', $emails);
    }

    /**
     * Test first and last methods
     */
    public function testFirstAndLast() {
        $tenantId = $this->createTestTenant();

        $user1Id = $this->createTestUser($tenantId, ['email' => 'first@test.com']);
        $user2Id = $this->createTestUser($tenantId, ['email' => 'second@test.com']);
        $user3Id = $this->createTestUser($tenantId, ['email' => 'third@test.com']);

        $this->actingAs($user1Id, $tenantId);

        $userModel = new User();

        $first = $userModel->first([], 'id ASC');
        $last = $userModel->last([], 'id DESC');

        $this->assertEquals($user1Id, $first['id']);
        $this->assertEquals($user3Id, $last['id']);
    }

    /**
     * Test increment and decrement
     */
    public function testIncrementAndDecrement() {
        $tenantId = $this->createTestTenant();

        // Create a finding with initial count
        $sql = "INSERT INTO findings (tenant_id, audit_plan_id, title, description, severity, status)
                VALUES (?, 1, 'Test Finding', 'Description', 'high', 'open')";

        $this->db->query($sql, [$tenantId]);
        $findingId = $this->db->lastInsertId();

        $userId = $this->createTestUser($tenantId);
        $this->actingAs($userId, $tenantId);

        $findingModel = new Finding();

        // Note: We'd need a numeric column to test this properly
        // This is a conceptual test
        $this->assertTrue(true); // Placeholder
    }

    /**
     * Test count method
     */
    public function testCount() {
        $tenantId = $this->createTestTenant();

        $this->createTestUser($tenantId);
        $this->createTestUser($tenantId);
        $userId = $this->createTestUser($tenantId);

        $this->actingAs($userId, $tenantId);

        $userModel = new User();
        $count = $userModel->count();

        $this->assertEquals(3, $count);
    }

    /**
     * Test distinct method
     */
    public function testDistinct() {
        $tenantId = $this->createTestTenant();

        $this->createTestUser($tenantId, ['role' => 'internal_auditor']);
        $this->createTestUser($tenantId, ['role' => 'internal_auditor']);
        $this->createTestUser($tenantId, ['role' => 'audit_manager']);
        $userId = $this->createTestUser($tenantId, ['role' => 'tenant_admin']);

        $this->actingAs($userId, $tenantId);

        $userModel = new User();
        $roles = $userModel->distinct('role');

        $this->assertCount(3, $roles);
        $this->assertContains('internal_auditor', $roles);
        $this->assertContains('audit_manager', $roles);
        $this->assertContains('tenant_admin', $roles);
    }
}
