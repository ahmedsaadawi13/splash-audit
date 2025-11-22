<?php
// FILE: /app/models/Finding.php

class Finding extends Model {
    protected $table = 'findings';
    protected $tenantIsolation = true;

    /**
     * Get findings by status
     */
    public function getByStatus($status) {
        return $this->findAll(['status' => $status], 'created_at DESC');
    }

    /**
     * Get findings by severity
     */
    public function getBySeverity($severity) {
        return $this->findAll(['severity' => $severity], 'created_at DESC');
    }

    /**
     * Get findings by audit plan
     */
    public function getByAuditPlan($auditPlanId) {
        return $this->findAll(['audit_plan_id' => $auditPlanId], 'severity DESC, created_at DESC');
    }

    /**
     * Get findings assigned to user
     */
    public function getAssignedToUser($userId) {
        return $this->findAll(['assigned_to_user_id' => $userId, 'status' => 'open'], 'severity DESC');
    }

    /**
     * Get open high-severity findings
     */
    public function getOpenHighSeverity() {
        $sql = "SELECT * FROM findings
                WHERE tenant_id = ? AND severity = 'high' AND status = 'open'
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, [Auth::tenantId()]);
    }

    /**
     * Get finding statistics
     */
    public function getStatistics() {
        $sql = "SELECT
                    COUNT(*) as total_findings,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high_severity,
                    SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium_severity,
                    SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low_severity
                FROM findings
                WHERE tenant_id = ?";

        return $this->db->fetchOne($sql, [Auth::tenantId()]);
    }
}
