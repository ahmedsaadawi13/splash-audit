<?php
// FILE: /app/controllers/ApiAuditsController.php

class ApiAuditsController extends Controller {
    private $auditPlanModel;
    private $tenantId;

    public function __construct() {
        parent::__construct();
        $this->auditPlanModel = $this->model('AuditPlan');
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
     * Get audit status
     * GET /api/audits/status?audit_plan_id=X
     */
    public function status() {
        $auditPlanId = $this->get('audit_plan_id');

        if (empty($auditPlanId)) {
            $this->json(['error' => 'audit_plan_id parameter is required'], 400);
        }

        $audit = $this->db->fetchOne(
            'SELECT * FROM audit_plans WHERE id = ? AND tenant_id = ?',
            [$auditPlanId, $this->tenantId]
        );

        if (!$audit) {
            $this->json(['error' => 'Audit plan not found'], 404);
        }

        // Get findings count
        $findingsCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM findings WHERE audit_plan_id = ? AND tenant_id = ?',
            [$auditPlanId, $this->tenantId]
        );

        $this->json([
            'success' => true,
            'audit' => [
                'id' => $audit['id'],
                'title' => $audit['title'],
                'status' => $audit['status'],
                'planned_start' => $audit['planned_start'],
                'planned_end' => $audit['planned_end'],
                'actual_start' => $audit['actual_start'],
                'actual_end' => $audit['actual_end'],
                'findings_count' => (int)$findingsCount['count']
            ]
        ]);
    }
}
