<?php
// FILE: /app/models/AuditChecklist.php

class AuditChecklist extends Model {
    protected $table = 'audit_checklists';
    protected $tenantIsolation = true;

    /**
     * Get active checklists
     */
    public function getActiveChecklists() {
        return $this->findAll(['status' => 'active']);
    }

    /**
     * Get checklists by type
     */
    public function getByType($type) {
        return $this->findAll(['checklist_type' => $type, 'status' => 'active']);
    }

    /**
     * Get checklist with items
     */
    public function getWithItems($checklistId) {
        $checklist = $this->findById($checklistId);

        if ($checklist) {
            $sql = "SELECT * FROM checklist_items
                    WHERE tenant_id = ? AND checklist_id = ?
                    ORDER BY sort_order ASC";

            $checklist['items'] = $this->db->fetchAll($sql, [Auth::tenantId(), $checklistId]);
        }

        return $checklist;
    }
}
