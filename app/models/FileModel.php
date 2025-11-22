<?php
// FILE: /app/models/FileModel.php

class FileModel extends Model {
    protected $table = 'files';
    protected $tenantIsolation = true;

    /**
     * Get files by module
     */
    public function getByModule($moduleType, $moduleId) {
        return $this->findAll([
            'module_type' => $moduleType,
            'module_id' => $moduleId
        ], 'created_at DESC');
    }

    /**
     * Get total storage used by tenant
     */
    public function getTotalStorageUsed() {
        $sql = "SELECT SUM(size_bytes) as total FROM files WHERE tenant_id = ?";
        $result = $this->db->fetchOne($sql, [Auth::tenantId()]);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get storage usage in MB
     */
    public function getStorageUsageMB() {
        return round($this->getTotalStorageUsed() / 1048576, 2);
    }
}
