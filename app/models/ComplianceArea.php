<?php
// FILE: /app/models/ComplianceArea.php

class ComplianceArea extends Model {
    protected $table = 'compliance_areas';
    protected $tenantIsolation = true;

    /**
     * Get active compliance areas
     */
    public function getActiveAreas() {
        return $this->findAll(['status' => 'active']);
    }

    /**
     * Get area with requirements
     */
    public function getWithRequirements($areaId) {
        $area = $this->findById($areaId);

        if ($area) {
            $sql = "SELECT * FROM compliance_requirements
                    WHERE tenant_id = ? AND compliance_area_id = ?
                    ORDER BY requirement_code ASC";

            $area['requirements'] = $this->db->fetchAll($sql, [Auth::tenantId(), $areaId]);
        }

        return $area;
    }
}
