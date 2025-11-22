<?php
// FILE: /app/helpers/QuotaChecker.php

class QuotaChecker {
    private $db;
    private $tenantId;
    private $plan;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->tenantId = Auth::tenantId();
        $this->plan = $this->getTenantPlan();
    }

    /**
     * Get tenant's subscription plan
     */
    private function getTenantPlan() {
        $sql = "SELECT sp.*
                FROM subscription_plans sp
                INNER JOIN tenants t ON t.subscription_plan_id = sp.id
                WHERE t.id = ?";

        $plan = $this->db->fetchOne($sql, [$this->tenantId]);

        if (!$plan) {
            // Return basic plan as default
            return $this->db->fetchOne("SELECT * FROM subscription_plans WHERE plan_code = 'basic'");
        }

        return $plan;
    }

    /**
     * Check if tenant can create audit plan
     */
    public function canCreateAuditPlan() {
        $maxPlans = (int)$this->plan['max_audit_plans'];

        // -1 means unlimited
        if ($maxPlans === -1) {
            return true;
        }

        $currentCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM audit_plans WHERE tenant_id = ?',
            [$this->tenantId]
        );

        return (int)$currentCount['count'] < $maxPlans;
    }

    /**
     * Check if tenant can create checklist
     */
    public function canCreateChecklist() {
        $maxChecklists = (int)$this->plan['max_checklists'];

        if ($maxChecklists === -1) {
            return true;
        }

        $currentCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM audit_checklists WHERE tenant_id = ?',
            [$this->tenantId]
        );

        return (int)$currentCount['count'] < $maxChecklists;
    }

    /**
     * Check if tenant can create user
     */
    public function canCreateUser() {
        $maxUsers = (int)$this->plan['max_users'];

        if ($maxUsers === -1) {
            return true;
        }

        $currentCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM users WHERE tenant_id = ?',
            [$this->tenantId]
        );

        return (int)$currentCount['count'] < $maxUsers;
    }

    /**
     * Check if tenant can upload file
     */
    public function canUploadFile($fileSize) {
        $maxStorageMb = (int)$this->plan['max_storage_mb'];

        if ($maxStorageMb === -1) {
            return true;
        }

        $currentUsage = $this->db->fetchOne(
            'SELECT SUM(size_bytes) as total FROM files WHERE tenant_id = ?',
            [$this->tenantId]
        );

        $currentUsageMb = (int)($currentUsage['total'] ?? 0) / 1048576;
        $fileSizeMb = $fileSize / 1048576;

        return ($currentUsageMb + $fileSizeMb) <= $maxStorageMb;
    }

    /**
     * Check if tenant can create corrective action
     */
    public function canCreateCorrectiveAction() {
        $maxActions = (int)$this->plan['max_corrective_actions'];

        if ($maxActions === -1) {
            return true;
        }

        $currentCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM corrective_actions WHERE tenant_id = ?',
            [$this->tenantId]
        );

        return (int)$currentCount['count'] < $maxActions;
    }

    /**
     * Check if tenant has feature access
     */
    public function hasFeature($featureName) {
        $features = json_decode($this->plan['features'], true);
        return isset($features[$featureName]) && $features[$featureName] === true;
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats() {
        $stats = [];

        // Audit plans
        $auditPlansCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM audit_plans WHERE tenant_id = ?',
            [$this->tenantId]
        );
        $stats['audit_plans'] = [
            'used' => (int)$auditPlansCount['count'],
            'limit' => (int)$this->plan['max_audit_plans'],
            'percentage' => $this->calculatePercentage($auditPlansCount['count'], $this->plan['max_audit_plans'])
        ];

        // Checklists
        $checklistsCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM audit_checklists WHERE tenant_id = ?',
            [$this->tenantId]
        );
        $stats['checklists'] = [
            'used' => (int)$checklistsCount['count'],
            'limit' => (int)$this->plan['max_checklists'],
            'percentage' => $this->calculatePercentage($checklistsCount['count'], $this->plan['max_checklists'])
        ];

        // Users
        $usersCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM users WHERE tenant_id = ?',
            [$this->tenantId]
        );
        $stats['users'] = [
            'used' => (int)$usersCount['count'],
            'limit' => (int)$this->plan['max_users'],
            'percentage' => $this->calculatePercentage($usersCount['count'], $this->plan['max_users'])
        ];

        // Storage
        $storageUsage = $this->db->fetchOne(
            'SELECT SUM(size_bytes) as total FROM files WHERE tenant_id = ?',
            [$this->tenantId]
        );
        $usedMb = (int)($storageUsage['total'] ?? 0) / 1048576;
        $stats['storage'] = [
            'used' => round($usedMb, 2),
            'limit' => (int)$this->plan['max_storage_mb'],
            'percentage' => $this->calculatePercentage($usedMb, $this->plan['max_storage_mb'])
        ];

        // Corrective actions
        $correctiveActionsCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM corrective_actions WHERE tenant_id = ?',
            [$this->tenantId]
        );
        $stats['corrective_actions'] = [
            'used' => (int)$correctiveActionsCount['count'],
            'limit' => (int)$this->plan['max_corrective_actions'],
            'percentage' => $this->calculatePercentage($correctiveActionsCount['count'], $this->plan['max_corrective_actions'])
        ];

        return $stats;
    }

    /**
     * Calculate percentage
     */
    private function calculatePercentage($used, $limit) {
        if ($limit === -1 || $limit === 0) {
            return 0;
        }
        return round(($used / $limit) * 100, 2);
    }

    /**
     * Get plan details
     */
    public function getPlan() {
        return $this->plan;
    }
}
