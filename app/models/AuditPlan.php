<?php
// FILE: /app/models/AuditPlan.php

class AuditPlan extends Model {
    protected $table = 'audit_plans';
    protected $tenantIsolation = true;

    /**
     * Get plans by status
     */
    public function getByStatus($status) {
        return $this->findAll(['status' => $status], 'planned_start DESC');
    }

    /**
     * Get plans by lead auditor
     */
    public function getByLeadAuditor($auditorId) {
        return $this->findAll(['lead_auditor_id' => $auditorId], 'planned_start DESC');
    }

    /**
     * Get upcoming audits
     */
    public function getUpcomingAudits($days = 30) {
        $sql = "SELECT * FROM audit_plans
                WHERE tenant_id = ?
                AND status IN ('scheduled', 'in_progress')
                AND planned_start BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY planned_start ASC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $days]);
    }

    /**
     * Get overdue audits
     */
    public function getOverdueAudits() {
        $sql = "SELECT * FROM audit_plans
                WHERE tenant_id = ?
                AND status = 'in_progress'
                AND planned_end < CURDATE()
                ORDER BY planned_end ASC";

        return $this->db->fetchAll($sql, [Auth::tenantId()]);
    }

    /**
     * Get audit statistics
     */
    public function getStatistics() {
        $sql = "SELECT
                    COUNT(*) as total_audits,
                    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'canceled' THEN 1 ELSE 0 END) as canceled
                FROM audit_plans
                WHERE tenant_id = ?";

        return $this->db->fetchOne($sql, [Auth::tenantId()]);
    }
}
