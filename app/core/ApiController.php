<?php
// FILE: /app/core/ApiController.php

class ApiController extends Controller {
    protected $tenantId;
    protected $apiKey;

    public function __construct() {
        parent::__construct();
        $this->authenticateAPI();
    }

    /**
     * Authenticate API request using X-API-KEY header
     */
    protected function authenticateAPI() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            $this->json(['error' => 'API key is required'], 401);
        }

        // Validate API key
        $result = $this->db->fetchOne(
            'SELECT tenant_id, is_active FROM api_keys WHERE api_key = ?',
            [$apiKey]
        );

        if (!$result || !$result['is_active']) {
            $this->json(['error' => 'Invalid or inactive API key'], 401);
        }

        $this->tenantId = $result['tenant_id'];
        $this->apiKey = $apiKey;

        // Update last used timestamp
        $this->db->query(
            'UPDATE api_keys SET last_used_at = NOW() WHERE api_key = ?',
            [$apiKey]
        );
    }

    /**
     * Validate required fields in request
     */
    protected function validateRequired($data, $requiredFields) {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $this->json([
                'error' => 'Missing required fields: ' . implode(', ', $missing)
            ], 400);
        }
    }

    /**
     * Get JSON input from request body
     */
    protected function getJsonInput() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['error' => 'Invalid JSON in request body'], 400);
        }

        return $data;
    }
}
