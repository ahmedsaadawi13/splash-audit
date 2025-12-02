<?php
// FILE: /app/models/ComplianceRequirement.php

class ComplianceRequirement extends Model {
    protected $table = 'compliance_requirements';
    protected $tenantIsolation = true;

    /**
     * Get requirements by compliance area
     */
    public function getByArea($complianceAreaId) {
        return $this->findAll(['compliance_area_id' => $complianceAreaId], 'requirement_code ASC');
    }

    /**
     * Get requirements with compliance check status
     */
    public function getWithCheckStatus($complianceAreaId = null, $auditPlanId = null) {
        $sql = "SELECT cr.*,
                       cc.compliance_status,
                       cc.check_date,
                       ca.name as area_name
                FROM compliance_requirements cr
                LEFT JOIN compliance_checks cc ON cr.id = cc.requirement_id";

        $params = [Auth::tenantId()];

        if ($auditPlanId) {
            $sql .= " AND cc.audit_plan_id = ?";
            $params[] = $auditPlanId;
        }

        $sql .= " LEFT JOIN compliance_areas ca ON cr.compliance_area_id = ca.id
                  WHERE cr.tenant_id = ?";

        if ($complianceAreaId) {
            $sql .= " AND cr.compliance_area_id = ?";
            $params[] = $complianceAreaId;
        }

        $sql .= " ORDER BY cr.requirement_code ASC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get requirement by code
     */
    public function getByCode($code) {
        return $this->db->fetchOne(
            'SELECT * FROM compliance_requirements WHERE tenant_id = ? AND requirement_code = ?',
            [Auth::tenantId(), $code]
        );
    }

    /**
     * Get statistics for compliance area
     */
    public function getAreaStatistics($complianceAreaId) {
        $sql = "SELECT
                    COUNT(*) as total_requirements,
                    SUM(CASE WHEN cc.compliance_status = 'compliant' THEN 1 ELSE 0 END) as compliant_count,
                    SUM(CASE WHEN cc.compliance_status = 'non_compliant' THEN 1 ELSE 0 END) as non_compliant_count,
                    SUM(CASE WHEN cc.compliance_status = 'partial' THEN 1 ELSE 0 END) as partial_count,
                    SUM(CASE WHEN cc.compliance_status = 'not_assessed' THEN 1 ELSE 0 END) as not_assessed_count
                FROM compliance_requirements cr
                LEFT JOIN compliance_checks cc ON cr.id = cc.requirement_id
                WHERE cr.tenant_id = ? AND cr.compliance_area_id = ?";

        return $this->db->fetchOne($sql, [Auth::tenantId(), $complianceAreaId]);
    }
}
