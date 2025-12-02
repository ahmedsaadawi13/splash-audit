<?php
// FILE: /app/models/ChecklistItem.php

class ChecklistItem extends Model {
    protected $table = 'checklist_items';
    protected $tenantIsolation = true;

    /**
     * Get items by checklist ID
     */
    public function getByChecklist($checklistId) {
        return $this->findAll(['checklist_id' => $checklistId], 'sort_order ASC');
    }

    /**
     * Get items by checklist with work item results
     */
    public function getWithResults($checklistId, $auditPlanId) {
        $sql = "SELECT ci.*,
                       awi.result,
                       awi.evidence_notes,
                       awi.completed_at
                FROM checklist_items ci
                LEFT JOIN audit_work_items awi ON ci.id = awi.checklist_item_id
                    AND awi.audit_plan_id = ?
                WHERE ci.tenant_id = ? AND ci.checklist_id = ?
                ORDER BY ci.sort_order ASC";

        return $this->db->fetchAll($sql, [$auditPlanId, Auth::tenantId(), $checklistId]);
    }

    /**
     * Reorder checklist items
     */
    public function reorder($items) {
        foreach ($items as $order => $id) {
            $this->update($id, ['sort_order' => $order]);
        }
    }

    /**
     * Get count of items in checklist
     */
    public function getItemCount($checklistId) {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM checklist_items WHERE tenant_id = ? AND checklist_id = ?',
            [Auth::tenantId(), $checklistId]
        );

        return $result ? (int)$result['count'] : 0;
    }
}
