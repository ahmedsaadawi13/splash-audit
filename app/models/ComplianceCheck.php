<?php
// FILE: /app/models/ComplianceCheck.php

class ComplianceCheck extends Model {
    protected $table = 'compliance_checks';
    protected $tenantIsolation = true;

    /**
     * Get checks by audit plan
     */
    public function getByAuditPlan($auditPlanId) {
        $sql = "SELECT cc.*,
                       cr.requirement_code,
                       cr.requirement_text,
                       ca.name as area_name,
                       u.first_name,
                       u.last_name
                FROM compliance_checks cc
                JOIN compliance_requirements cr ON cc.requirement_id = cr.id
                JOIN compliance_areas ca ON cr.compliance_area_id = ca.id
                LEFT JOIN users u ON cc.checked_by_user_id = u.id
                WHERE cc.tenant_id = ? AND cc.audit_plan_id = ?
                ORDER BY ca.name ASC, cr.requirement_code ASC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $auditPlanId]);
    }

    /**
     * Get checks by requirement
     */
    public function getByRequirement($requirementId) {
        return $this->findAll(['requirement_id' => $requirementId], 'check_date DESC');
    }

    /**
     * Get checks by status
     */
    public function getByStatus($status) {
        return $this->findAll(['compliance_status' => $status], 'check_date DESC');
    }

    /**
     * Get non-compliant checks
     */
    public function getNonCompliant() {
        return $this->db->fetchAll(
            "SELECT cc.*,
                    cr.requirement_code,
                    cr.requirement_text,
                    ca.name as area_name
             FROM compliance_checks cc
             JOIN compliance_requirements cr ON cc.requirement_id = cr.id
             JOIN compliance_areas ca ON cr.compliance_area_id = ca.id
             WHERE cc.tenant_id = ?
             AND cc.compliance_status IN ('non_compliant', 'partial')
             ORDER BY cc.check_date DESC",
            [Auth::tenantId()]
        );
    }

    /**
     * Get compliance statistics
     */
    public function getStatistics($auditPlanId = null) {
        $sql = "SELECT
                    COUNT(*) as total_checks,
                    SUM(CASE WHEN compliance_status = 'compliant' THEN 1 ELSE 0 END) as compliant,
                    SUM(CASE WHEN compliance_status = 'non_compliant' THEN 1 ELSE 0 END) as non_compliant,
                    SUM(CASE WHEN compliance_status = 'partial' THEN 1 ELSE 0 END) as partial,
                    SUM(CASE WHEN compliance_status = 'not_assessed' THEN 1 ELSE 0 END) as not_assessed
                FROM compliance_checks
                WHERE tenant_id = ?";

        $params = [Auth::tenantId()];

        if ($auditPlanId) {
            $sql .= " AND audit_plan_id = ?";
            $params[] = $auditPlanId;
        }

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get latest check for requirement
     */
    public function getLatestCheck($requirementId) {
        return $this->db->fetchOne(
            'SELECT * FROM compliance_checks
             WHERE tenant_id = ? AND requirement_id = ?
             ORDER BY check_date DESC
             LIMIT 1',
            [Auth::tenantId(), $requirementId]
        );
    }
}
