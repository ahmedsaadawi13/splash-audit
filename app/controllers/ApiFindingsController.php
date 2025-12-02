<?php
// FILE: /app/controllers/ApiFindingsController.php

class ApiFindingsController extends ApiController {
    private $findingModel;

    public function __construct() {
        parent::__construct();
        $this->findingModel = $this->model('Finding');
    }

    /**
     * Create finding
     * POST /api/findings/create
     */
    public function create() {
        if (!$this->isPost()) {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $input = $this->getJsonInput();

        // Validate required fields
        $this->validateRequired($input, ['audit_plan_id', 'title', 'description']);

        $severity = $input['severity'] ?? 'medium';
        if (!in_array($severity, ['high', 'medium', 'low'])) {
            $this->json(['error' => 'Invalid severity. Must be: high, medium, or low'], 400);
        }

        $data = [
            'tenant_id' => $this->tenantId,
            'audit_plan_id' => $input['audit_plan_id'],
            'title' => $input['title'],
            'description' => $input['description'],
            'severity' => $severity,
            'cause' => $input['cause'] ?? '',
            'effect' => $input['effect'] ?? '',
            'criteria' => $input['criteria'] ?? '',
            'assigned_to_user_id' => $input['assigned_to'] ?? null,
            'status' => 'open'
        ];

        try {
            $findingId = $this->findingModel->create($data);

            $this->json([
                'success' => true,
                'finding_id' => (int)$findingId,
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
        $limit = (int)$this->get('limit', 100);
        $offset = (int)$this->get('offset', 0);

        // Validate status
        $validStatuses = ['open', 'in_progress', 'closed', 'deferred'];
        if (!in_array($status, $validStatuses)) {
            $this->json(['error' => 'Invalid status. Must be: ' . implode(', ', $validStatuses)], 400);
        }

        $sql = "SELECT * FROM findings
                WHERE tenant_id = ? AND status = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        $findings = $this->db->fetchAll($sql, [$this->tenantId, $status, $limit, $offset]);

        $this->json([
            'success' => true,
            'count' => count($findings),
            'findings' => $findings
        ]);
    }
}
