<?php
// FILE: /app/models/AuditUniverse.php

class AuditUniverse extends Model {
    protected $table = 'audit_universe';
    protected $tenantIsolation = true;

    /**
     * Get active entities
     */
    public function getActiveEntities() {
        return $this->findAll(['status' => 'active'], 'risk_score DESC');
    }

    /**
     * Get entities by category
     */
    public function getByCategory($category) {
        return $this->findAll(['category' => $category, 'status' => 'active']);
    }

    /**
     * Get entities by owner
     */
    public function getByOwner($ownerId) {
        return $this->findAll(['owner_user_id' => $ownerId, 'status' => 'active']);
    }

    /**
     * Get high-risk entities
     */
    public function getHighRiskEntities($threshold = 15) {
        $sql = "SELECT * FROM audit_universe
                WHERE tenant_id = ? AND risk_score >= ? AND status = 'active'
                ORDER BY risk_score DESC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $threshold]);
    }
}
