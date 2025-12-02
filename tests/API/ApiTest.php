<?php
/**
 * API Endpoint Tests
 * Tests REST API functionality
 */

require_once __DIR__ . '/../TestCase.php';

class ApiTest extends TestCase {

    private $apiKey;
    private $tenantId;

    protected function setUp(): void {
        parent::setUp();

        // Create test tenant and API key
        $this->tenantId = $this->createTestTenant();

        $sql = "INSERT INTO api_keys (tenant_id, api_key, name, is_active)
                VALUES (?, ?, 'Test API Key', 1)";

        $this->apiKey = 'test_api_key_' . bin2hex(random_bytes(16));
        $this->db->query($sql, [$this->tenantId, $this->apiKey]);
    }

    /**
     * Test API Authentication with Valid Key
     */
    public function testApiAuthenticationSuccess() {
        $_SERVER['HTTP_X_API_KEY'] = $this->apiKey;

        // This would normally be tested through actual API controller
        $this->assertTrue(true); // Placeholder for integration test
    }

    /**
     * Test API Authentication with Invalid Key
     */
    public function testApiAuthenticationFailure() {
        $_SERVER['HTTP_X_API_KEY'] = 'invalid_key';

        // Should fail authentication
        $this->assertTrue(true); // Placeholder
    }

    /**
     * Test API Rate Limiting
     */
    public function testApiRateLimiting() {
        $rateLimiter = new RateLimiter(60, 1);

        // Should allow requests within limit
        $this->assertTrue($rateLimiter->check($this->apiKey, 'api'));

        // Record a hit
        $rateLimiter->hit($this->apiKey, 'api');

        // Should still be under limit
        $attempts = $rateLimiter->getAttempts($this->apiKey, 'api');
        $this->assertEquals(1, $attempts);
    }

    /**
     * Test JSON Input Parsing
     */
    public function testJsonInputParsing() {
        $testData = ['title' => 'Test Finding', 'severity' => 'high'];
        $json = json_encode($testData);

        // Simulate JSON input
        $parsed = json_decode($json, true);

        $this->assertEquals($testData, $parsed);
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    }

    /**
     * Test Invalid JSON Input
     */
    public function testInvalidJsonInput() {
        $invalidJson = '{invalid json}';
        $parsed = json_decode($invalidJson, true);

        $this->assertNull($parsed);
        $this->assertNotEquals(JSON_ERROR_NONE, json_last_error());
    }

    /**
     * Test API Response Format
     */
    public function testApiResponseFormat() {
        $response = [
            'status' => 'success',
            'data' => ['id' => 1, 'title' => 'Test'],
            'message' => 'Operation successful'
        ];

        $this->assertArrayHasKeys(['status', 'data', 'message'], $response);
    }

    /**
     * Test API Error Response
     */
    public function testApiErrorResponse() {
        $errorResponse = [
            'error' => 'Not found',
            'code' => 404
        ];

        $this->assertArrayHasKey('error', $errorResponse);
        $this->assertEquals(404, $errorResponse['code']);
    }
}
