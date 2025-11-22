<?php
// FILE: /app/controllers/DashboardController.php

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
    }

    /**
     * Main dashboard - routes to role-specific dashboard
     */
    public function index() {
        $role = Auth::role();

        switch ($role) {
            case 'platform_admin':
                $this->platformAdminDashboard();
                break;
            case 'tenant_admin':
            case 'audit_manager':
                $this->tenantAdminDashboard();
                break;
            case 'internal_auditor':
                $this->auditorDashboard();
                break;
            case 'process_owner':
                $this->processOwnerDashboard();
                break;
            default:
                $this->viewerDashboard();
                break;
        }
    }

    /**
     * Platform Admin Dashboard
     */
    private function platformAdminDashboard() {
        $tenantModel = $this->model('Tenant');

        $data = [
            'page_title' => 'Platform Admin Dashboard',
            'total_tenants' => $this->db->fetchOne('SELECT COUNT(*) as count FROM tenants')['count'],
            'active_tenants' => $this->db->fetchOne("SELECT COUNT(*) as count FROM tenants WHERE status = 'active'")['count'],
            'recent_tenants' => $tenantModel->findAll([], 'created_at DESC', 10)
        ];

        $this->view('dashboard/platform_admin', $data);
    }

    /**
     * Tenant Admin Dashboard
     */
    private function tenantAdminDashboard() {
        $auditPlanModel = $this->model('AuditPlan');
        $findingModel = $this->model('Finding');
        $correctiveActionModel = $this->model('CorrectiveAction');
        $riskModel = $this->model('Risk');

        $data = [
            'page_title' => 'Dashboard',
            'audit_stats' => $auditPlanModel->getStatistics(),
            'finding_stats' => $findingModel->getStatistics(),
            'action_stats' => $correctiveActionModel->getStatistics(),
            'upcoming_audits' => $auditPlanModel->getUpcomingAudits(30),
            'overdue_audits' => $auditPlanModel->getOverdueAudits(),
            'high_severity_findings' => $findingModel->getOpenHighSeverity(),
            'overdue_actions' => $correctiveActionModel->getOverdueActions(),
            'high_risks' => $riskModel->getHighRisks(15),
            'quota_stats' => (new QuotaChecker())->getUsageStats()
        ];

        $this->view('dashboard/tenant_admin', $data);
    }

    /**
     * Auditor Dashboard
     */
    private function auditorDashboard() {
        $auditPlanModel = $this->model('AuditPlan');
        $findingModel = $this->model('Finding');

        $data = [
            'page_title' => 'Auditor Dashboard',
            'assigned_audits' => $auditPlanModel->getByLeadAuditor(Auth::id()),
            'assigned_findings' => $findingModel->getAssignedToUser(Auth::id()),
            'upcoming_audits' => $auditPlanModel->getUpcomingAudits(30)
        ];

        $this->view('dashboard/auditor', $data);
    }

    /**
     * Process Owner Dashboard
     */
    private function processOwnerDashboard() {
        $findingModel = $this->model('Finding');
        $correctiveActionModel = $this->model('CorrectiveAction');

        $data = [
            'page_title' => 'Process Owner Dashboard',
            'assigned_findings' => $findingModel->getAssignedToUser(Auth::id()),
            'assigned_actions' => $correctiveActionModel->getAssignedToUser(Auth::id()),
            'overdue_actions' => $this->db->fetchAll(
                "SELECT * FROM corrective_actions
                 WHERE tenant_id = ? AND responsible_user_id = ?
                 AND status != 'completed' AND due_date < CURDATE()
                 ORDER BY due_date ASC",
                [Auth::tenantId(), Auth::id()]
            )
        ];

        $this->view('dashboard/process_owner', $data);
    }

    /**
     * Viewer Dashboard (read-only)
     */
    private function viewerDashboard() {
        $data = [
            'page_title' => 'Dashboard',
            'message' => 'Welcome! You have view-only access to the system.'
        ];

        $this->view('dashboard/viewer', $data);
    }
}
