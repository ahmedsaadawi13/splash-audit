<?php
// FILE: /app/models/AuditWorkItem.php

class AuditWorkItem extends Model {
    protected $table = 'audit_work_items';
    protected $tenantIsolation = true;

    /**
     * Get work items by audit plan
     */
    public function getByAuditPlan($auditPlanId) {
        $sql = "SELECT awi.*,
                       ci.item_text,
                       ci.procedure,
                       ac.title as checklist_title,
                       u.first_name,
                       u.last_name
                FROM audit_work_items awi
                JOIN checklist_items ci ON awi.checklist_item_id = ci.id
                JOIN audit_checklists ac ON ci.checklist_id = ac.id
                LEFT JOIN users u ON awi.auditor_id = u.id
                WHERE awi.tenant_id = ? AND awi.audit_plan_id = ?
                ORDER BY ci.sort_order ASC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $auditPlanId]);
    }

    /**
     * Get work items by auditor
     */
    public function getByAuditor($auditorId, $status = null) {
        if ($status) {
            $sql = "SELECT awi.*,
                           ci.item_text,
                           ap.title as audit_title
                    FROM audit_work_items awi
                    JOIN checklist_items ci ON awi.checklist_item_id = ci.id
                    JOIN audit_plans ap ON awi.audit_plan_id = ap.id
                    WHERE awi.tenant_id = ? AND awi.auditor_id = ? AND awi.result = ?
                    ORDER BY awi.updated_at DESC";

            return $this->db->fetchAll($sql, [Auth::tenantId(), $auditorId, $status]);
        }

        $sql = "SELECT awi.*,
                       ci.item_text,
                       ap.title as audit_title
                FROM audit_work_items awi
                JOIN checklist_items ci ON awi.checklist_item_id = ci.id
                JOIN audit_plans ap ON awi.audit_plan_id = ap.id
                WHERE awi.tenant_id = ? AND awi.auditor_id = ?
                ORDER BY awi.updated_at DESC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $auditorId]);
    }

    /**
     * Get work items by result status
     */
    public function getByResult($auditPlanId, $result) {
        return $this->findAll([
            'audit_plan_id' => $auditPlanId,
            'result' => $result
        ], 'updated_at DESC');
    }

    /**
     * Get completion statistics for audit plan
     */
    public function getStatistics($auditPlanId) {
        $sql = "SELECT
                    COUNT(*) as total_items,
                    SUM(CASE WHEN result = 'pass' THEN 1 ELSE 0 END) as pass_count,
                    SUM(CASE WHEN result = 'fail' THEN 1 ELSE 0 END) as fail_count,
                    SUM(CASE WHEN result = 'not_applicable' THEN 1 ELSE 0 END) as na_count,
                    SUM(CASE WHEN result = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN result != 'pending' THEN 1 ELSE 0 END) as completed_count
                FROM audit_work_items
                WHERE tenant_id = ? AND audit_plan_id = ?";

        return $this->db->fetchOne($sql, [Auth::tenantId(), $auditPlanId]);
    }

    /**
     * Mark work item as completed
     */
    public function markCompleted($id, $result, $evidenceNotes = null, $evidenceFile = null) {
        return $this->update($id, [
            'result' => $result,
            'evidence_notes' => $evidenceNotes,
            'evidence_file' => $evidenceFile,
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get pending work items count
     */
    public function getPendingCount($auditPlanId) {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM audit_work_items
             WHERE tenant_id = ? AND audit_plan_id = ? AND result = "pending"',
            [Auth::tenantId(), $auditPlanId]
        );

        return $result ? (int)$result['count'] : 0;
    }
}
