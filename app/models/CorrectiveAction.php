<?php
// FILE: /app/models/CorrectiveAction.php

class CorrectiveAction extends Model {
    protected $table = 'corrective_actions';
    protected $tenantIsolation = true;

    /**
     * Get actions by status
     */
    public function getByStatus($status) {
        return $this->findAll(['status' => $status], 'due_date ASC');
    }

    /**
     * Get actions by finding
     */
    public function getByFinding($findingId) {
        return $this->findAll(['finding_id' => $findingId]);
    }

    /**
     * Get actions assigned to user
     */
    public function getAssignedToUser($userId) {
        return $this->findAll(['responsible_user_id' => $userId], 'due_date ASC');
    }

    /**
     * Get overdue actions
     */
    public function getOverdueActions() {
        $sql = "SELECT * FROM corrective_actions
                WHERE tenant_id = ?
                AND status IN ('open', 'pending_review')
                AND due_date < CURDATE()
                ORDER BY due_date ASC";

        return $this->db->fetchAll($sql, [Auth::tenantId()]);
    }

    /**
     * Get upcoming due actions
     */
    public function getUpcomingDueActions($days = 7) {
        $sql = "SELECT * FROM corrective_actions
                WHERE tenant_id = ?
                AND status = 'open'
                AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY due_date ASC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $days]);
    }

    /**
     * Get action statistics
     */
    public function getStatistics() {
        $sql = "SELECT
                    COUNT(*) as total_actions,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                    SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending_review,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN due_date < CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as overdue
                FROM corrective_actions
                WHERE tenant_id = ?";

        return $this->db->fetchOne($sql, [Auth::tenantId()]);
    }
}
