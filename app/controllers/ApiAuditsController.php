<?php
// FILE: /app/controllers/ApiAuditsController.php

class ApiAuditsController extends ApiController {
    private $auditPlanModel;

    public function __construct() {
        parent::__construct();
        $this->auditPlanModel = $this->model('AuditPlan');
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
                'id' => (int)$audit['id'],
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
