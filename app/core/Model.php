<?php
// FILE: /app/core/Model.php

class Model {
    protected $db;
    protected $table;
    protected $tenantIsolation = true; // Enable tenant isolation by default

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Find all records with tenant isolation
     */
    public function findAll($where = [], $orderBy = 'id DESC', $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        // Add tenant isolation
        if ($this->tenantIsolation && Auth::check()) {
            $where['tenant_id'] = Auth::tenantId();
        }

        // Build WHERE clause
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        // Add ORDER BY
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }

        // Add LIMIT
        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Find record by ID with tenant isolation
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $params = [$id];

        // Add tenant isolation
        if ($this->tenantIsolation && Auth::check()) {
            $sql .= ' AND tenant_id = ?';
            $params[] = Auth::tenantId();
        }

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Find one record
     */
    public function findOne($where = []) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        // Add tenant isolation
        if ($this->tenantIsolation && Auth::check()) {
            $where['tenant_id'] = Auth::tenantId();
        }

        // Build WHERE clause
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' LIMIT 1';

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Create new record
     */
    public function create($data) {
        // Add tenant_id automatically
        if ($this->tenantIsolation && Auth::check() && !isset($data['tenant_id'])) {
            $data['tenant_id'] = Auth::tenantId();
        }

        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->query($sql, $values);
        return $this->db->lastInsertId();
    }

    /**
     * Update record
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";

        // Add tenant isolation
        if ($this->tenantIsolation && Auth::check()) {
            $sql .= ' AND tenant_id = ?';
            $values[] = Auth::tenantId();
        }

        $this->db->query($sql, $values);
        return $this->db->query($sql, $values)->rowCount();
    }

    /**
     * Delete record
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];

        // Add tenant isolation
        if ($this->tenantIsolation && Auth::check()) {
            $sql .= ' AND tenant_id = ?';
            $params[] = Auth::tenantId();
        }

        return $this->db->query($sql, $params)->rowCount();
    }

    /**
     * Count records
     */
    public function count($where = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];

        // Add tenant isolation
        if ($this->tenantIsolation && Auth::check()) {
            $where['tenant_id'] = Auth::tenantId();
        }

        // Build WHERE clause
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'];
    }

    /**
     * Execute raw query
     */
    public function query($sql, $params = []) {
        return $this->db->query($sql, $params);
    }

    /**
     * Fetch all from raw query
     */
    public function fetchAll($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Fetch one from raw query
     */
    public function fetchOne($sql, $params = []) {
        return $this->db->fetchOne($sql, $params);
    }
}
