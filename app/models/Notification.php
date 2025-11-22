<?php
// FILE: /app/models/Notification.php

class Notification extends Model {
    protected $table = 'notifications';
    protected $tenantIsolation = true;

    /**
     * Get notifications for user
     */
    public function getForUser($userId, $limit = 10) {
        $sql = "SELECT * FROM notifications
                WHERE tenant_id = ? AND user_id = ?
                ORDER BY created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $userId, $limit]);
    }

    /**
     * Get unread notifications
     */
    public function getUnreadForUser($userId) {
        $sql = "SELECT * FROM notifications
                WHERE tenant_id = ? AND user_id = ? AND status = 'pending'
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, [Auth::tenantId(), $userId]);
    }

    /**
     * Mark as sent
     */
    public function markAsSent($id) {
        return $this->update($id, [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }
}
