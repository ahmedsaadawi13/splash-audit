<?php
// FILE: /app/controllers/ApiCorrectiveActionsController.php

class ApiCorrectiveActionsController extends Controller {
    private $correctiveActionModel;
    private $tenantId;

    public function __construct() {
        parent::__construct();
        $this->correctiveActionModel = $this->model('CorrectiveAction');
        $this->authenticateAPI();
    }

    private function authenticateAPI() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            $this->json(['error' => 'API key is required'], 401);
        }

        $result = $this->db->fetchOne(
            'SELECT tenant_id FROM api_keys WHERE api_key = ? AND is_active = 1',
            [$apiKey]
        );

        if (!$result) {
            $this->json(['error' => 'Invalid API key'], 401);
        }

        $this->tenantId = $result['tenant_id'];
    }

    /**
     * Add corrective action
     * POST /api/corrective-actions/add
     */
    public function add() {
        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $data = [
            'tenant_id' => $this->tenantId,
            'finding_id' => $input['finding_id'] ?? null,
            'action_description' => $input['action_description'] ?? '',
            'due_date' => $input['due_date'] ?? null,
            'responsible_user_id' => $input['responsible_user_id'] ?? null,
            'status' => 'open'
        ];

        if (empty($data['finding_id']) || empty($data['action_description']) || empty($data['due_date'])) {
            $this->json(['error' => 'Missing required fields: finding_id, action_description, due_date'], 400);
        }

        try {
            $actionId = $this->correctiveActionModel->create($data);

            $this->json([
                'success' => true,
                'corrective_action_id' => $actionId,
                'message' => 'Corrective action created successfully'
            ], 201);

        } catch (Exception $e) {
            error_log('API Corrective action creation error: ' . $e->getMessage());
            $this->json(['error' => 'Failed to create corrective action'], 500);
        }
    }
}
