<?php
// FILE: /app/controllers/ApiFindingsController.php

class ApiFindingsController extends Controller {
    private $findingModel;
    private $tenantId;

    public function __construct() {
        parent::__construct();
        $this->findingModel = $this->model('Finding');

        // Authenticate API request
        $this->authenticateAPI();
    }

    /**
     * Authenticate API key
     */
    private function authenticateAPI() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            $this->json(['error' => 'API key is required'], 401);
        }

        // Validate API key
        $result = $this->db->fetchOne(
            'SELECT tenant_id FROM api_keys WHERE api_key = ? AND is_active = 1',
            [$apiKey]
        );

        if (!$result) {
            $this->json(['error' => 'Invalid API key'], 401);
        }

        $this->tenantId = $result['tenant_id'];

        // Update last used
        $this->db->query(
            'UPDATE api_keys SET last_used_at = NOW() WHERE api_key = ?',
            [$apiKey]
        );
    }

    /**
     * Create finding
     * POST /api/findings/create
     */
    public function create() {
        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $data = [
            'tenant_id' => $this->tenantId,
            'audit_plan_id' => $input['audit_plan_id'] ?? null,
            'title' => $input['title'] ?? '',
            'description' => $input['description'] ?? '',
            'severity' => $input['severity'] ?? 'medium',
            'cause' => $input['cause'] ?? '',
            'effect' => $input['effect'] ?? '',
            'criteria' => $input['criteria'] ?? '',
            'assigned_to_user_id' => $input['assigned_to'] ?? null,
            'status' => 'open'
        ];

        // Validate
        if (empty($data['audit_plan_id']) || empty($data['title']) || empty($data['description'])) {
            $this->json(['error' => 'Missing required fields: audit_plan_id, title, description'], 400);
        }

        if (!in_array($data['severity'], ['high', 'medium', 'low'])) {
            $this->json(['error' => 'Invalid severity. Must be: high, medium, or low'], 400);
        }

        try {
            $findingId = $this->findingModel->create($data);

            $this->json([
                'success' => true,
                'finding_id' => $findingId,
                'message' => 'Finding created successfully'
            ], 201);

        } catch (Exception $e) {
            error_log('API Finding creation error: ' . $e->getMessage());
            $this->json(['error' => 'Failed to create finding'], 500);
        }
    }

    /**
     * Get findings
     * GET /api/findings/list
     */
    public function list() {
        $status = $this->get('status', 'open');

        $sql = "SELECT * FROM findings WHERE tenant_id = ? AND status = ? ORDER BY created_at DESC";
        $findings = $this->db->fetchAll($sql, [$this->tenantId, $status]);

        $this->json([
            'success' => true,
            'count' => count($findings),
            'findings' => $findings
        ]);
    }
}
