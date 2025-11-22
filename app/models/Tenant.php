<?php
// FILE: /app/models/Tenant.php

class Tenant extends Model {
    protected $table = 'tenants';
    protected $tenantIsolation = false; // Platform-level table

    /**
     * Find tenant by subdomain
     */
    public function findBySubdomain($subdomain) {
        return $this->findOne(['subdomain' => $subdomain]);
    }

    /**
     * Get active tenants
     */
    public function getActiveTenants() {
        return $this->findAll(['status' => 'active']);
    }

    /**
     * Get tenant with subscription plan
     */
    public function getTenantWithPlan($tenantId) {
        $sql = "SELECT t.*, sp.plan_name, sp.plan_code, sp.max_audit_plans, sp.max_checklists,
                       sp.max_users, sp.max_storage_mb, sp.max_corrective_actions, sp.features
                FROM tenants t
                LEFT JOIN subscription_plans sp ON t.subscription_plan_id = sp.id
                WHERE t.id = ?";

        return $this->db->fetchOne($sql, [$tenantId]);
    }

    /**
     * Check if tenant is active
     */
    public function isActive($tenantId) {
        $tenant = $this->findById($tenantId);
        return $tenant && $tenant['status'] === 'active';
    }
}
