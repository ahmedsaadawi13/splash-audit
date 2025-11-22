<?php
// FILE: /app/models/User.php

class User extends Model {
    protected $table = 'users';
    protected $tenantIsolation = true;

    /**
     * Find user by email
     */
    public function findByEmail($email, $tenantId = null) {
        $sql = 'SELECT * FROM users WHERE email = ?';
        $params = [$email];

        if ($tenantId !== null) {
            $sql .= ' AND tenant_id = ?';
            $params[] = $tenantId;
        } elseif (Auth::check()) {
            $sql .= ' AND tenant_id = ?';
            $params[] = Auth::tenantId();
        }

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get users by role
     */
    public function findByRole($role) {
        return $this->findAll(['role' => $role]);
    }

    /**
     * Get active users
     */
    public function getActiveUsers() {
        return $this->findAll(['status' => 'active']);
    }

    /**
     * Create new user
     */
    public function createUser($data) {
        $data['password'] = Auth::hashPassword($data['password']);
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        return $this->update($userId, [
            'password' => Auth::hashPassword($newPassword)
        ]);
    }

    /**
     * Get users by tenant
     */
    public function getUsersByTenant($tenantId) {
        $this->tenantIsolation = false;
        $users = $this->findAll(['tenant_id' => $tenantId]);
        $this->tenantIsolation = true;
        return $users;
    }
}
