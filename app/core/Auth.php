<?php
// FILE: /app/core/Auth.php

class Auth {
    /**
     * Login user
     */
    public static function login($user) {
        Session::set('user_id', $user['id']);
        Session::set('tenant_id', $user['tenant_id']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
        session_regenerate_id(true);

        // Update last login
        $db = Database::getInstance();
        $db->query(
            'UPDATE users SET last_login_at = NOW() WHERE id = ?',
            [$user['id']]
        );
    }

    /**
     * Logout user
     */
    public static function logout() {
        Session::destroy();
    }

    /**
     * Check if user is authenticated
     */
    public static function check() {
        return Session::has('user_id');
    }

    /**
     * Get current user ID
     */
    public static function id() {
        return Session::get('user_id');
    }

    /**
     * Get current tenant ID
     */
    public static function tenantId() {
        return Session::get('tenant_id');
    }

    /**
     * Get current user role
     */
    public static function role() {
        return Session::get('user_role');
    }

    /**
     * Get current user name
     */
    public static function userName() {
        return Session::get('user_name');
    }

    /**
     * Get current user email
     */
    public static function userEmail() {
        return Session::get('user_email');
    }

    /**
     * Get current user data
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        $db = Database::getInstance();
        return $db->fetchOne(
            'SELECT * FROM users WHERE id = ? AND tenant_id = ?',
            [self::id(), self::tenantId()]
        );
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        return in_array(self::role(), $roles);
    }

    /**
     * Check if user is platform admin
     */
    public static function isPlatformAdmin() {
        return self::role() === 'platform_admin';
    }

    /**
     * Check if user is tenant admin
     */
    public static function isTenantAdmin() {
        return self::role() === 'tenant_admin';
    }

    /**
     * Check if user is audit manager
     */
    public static function isAuditManager() {
        return self::role() === 'audit_manager';
    }

    /**
     * Check if user is internal auditor
     */
    public static function isInternalAuditor() {
        return self::role() === 'internal_auditor';
    }

    /**
     * Check if user can perform action
     * Role hierarchy check
     */
    public static function can($permission) {
        $role = self::role();

        $roleHierarchy = [
            'platform_admin' => ['all'],
            'tenant_admin' => ['manage_users', 'manage_audit_programs', 'manage_settings', 'view_reports'],
            'audit_manager' => ['create_audit_plans', 'assign_audits', 'approve_reports', 'view_reports'],
            'internal_auditor' => ['perform_audits', 'create_findings', 'create_checklists'],
            'process_owner' => ['respond_findings', 'submit_evidence', 'create_corrective_actions'],
            'reviewer' => ['review_corrective_actions'],
            'viewer' => ['view_only']
        ];

        // Platform admin has all permissions
        if ($role === 'platform_admin') {
            return true;
        }

        $rolePermissions = $roleHierarchy[$role] ?? [];
        return in_array($permission, $rolePermissions) || in_array('all', $rolePermissions);
    }

    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::check()) {
            Session::setFlash('error', 'Please login to continue.');
            header('Location: /auth/login');
            exit;
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole($roles) {
        self::requireAuth();

        if (!self::hasRole($roles)) {
            Session::setFlash('error', 'You do not have permission to access this page.');
            header('Location: /dashboard');
            exit;
        }
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
