<?php
// FILE: /app/controllers/ApiCorrectiveActionsController.php

class ApiCorrectiveActionsController extends ApiController {
    private $correctiveActionModel;

    public function __construct() {
        parent::__construct();
        $this->correctiveActionModel = $this->model('CorrectiveAction');
    }

    /**
     * Add corrective action
     * POST /api/corrective-actions/add
     */
    public function add() {
        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $input = $this->getJsonInput();

        // Validate required fields
        $this->validateRequired($input, ['finding_id', 'action_description', 'due_date']);

        $data = [
            'tenant_id' => $this->tenantId,
            'finding_id' => $input['finding_id'],
            'action_description' => $input['action_description'],
            'due_date' => $input['due_date'],
            'responsible_user_id' => $input['responsible_user_id'] ?? null,
            'status' => 'open'
        ];

        try {
            $actionId = $this->correctiveActionModel->create($data);

            $this->json([
                'success' => true,
                'corrective_action_id' => (int)$actionId,
                'message' => 'Corrective action created successfully'
            ], 201);

        } catch (Exception $e) {
            error_log('API Corrective action creation error: ' . $e->getMessage());
            $this->json(['error' => 'Failed to create corrective action'], 500);
        }
    }
}
