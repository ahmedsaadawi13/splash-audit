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

    /**
     * Paginate results
     */
    public function paginate($where = [], $orderBy = 'id DESC', $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;

        // Get total count
        $totalCount = $this->count($where);

        // Get records for current page
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

        // Add LIMIT and OFFSET
        $sql .= " LIMIT $perPage OFFSET $offset";

        $data = $this->db->fetchAll($sql, $params);

        return [
            'data' => $data,
            'total' => $totalCount,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($totalCount / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $totalCount)
        ];
    }

    /**
     * Check if record exists
     */
    public function exists($where = []) {
        return $this->count($where) > 0;
    }

    /**
     * Get first record
     */
    public function first($where = [], $orderBy = 'id ASC') {
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

        $sql .= " ORDER BY $orderBy LIMIT 1";

        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get last record
     */
    public function last($where = [], $orderBy = 'id DESC') {
        return $this->first($where, $orderBy);
    }

    /**
     * Get single column values
     */
    public function pluck($column, $where = []) {
        $sql = "SELECT $column FROM {$this->table}";
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

        $results = $this->db->fetchAll($sql, $params);
        return array_column($results, $column);
    }

    /**
     * Create multiple records
     */
    public function createMany($dataArray) {
        if (empty($dataArray)) {
            return 0;
        }

        $insertedIds = [];

        foreach ($dataArray as $data) {
            $insertedIds[] = $this->create($data);
        }

        return $insertedIds;
    }

    /**
     * Update multiple records
     */
    public function updateMany($where, $data) {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields);

        // Add tenant isolation
        if ($this->tenantIsolation && Auth::check()) {
            $where['tenant_id'] = Auth::tenantId();
        }

        // Build WHERE clause
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "$key = ?";
                $values[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        return $this->db->query($sql, $values)->rowCount();
    }

    /**
     * Delete multiple records
     */
    public function deleteMany($where) {
        $sql = "DELETE FROM {$this->table}";
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

        return $this->db->query($sql, $params)->rowCount();
    }

    /**
     * Increment a column value
     */
    public function increment($id, $column, $amount = 1) {
        $sql = "UPDATE {$this->table} SET $column = $column + ? WHERE id = ?";
        $params = [$amount, $id];

        // Add tenant isolation
        if ($this->tenantIsolation && Auth::check()) {
            $sql .= ' AND tenant_id = ?';
            $params[] = Auth::tenantId();
        }

        return $this->db->query($sql, $params)->rowCount();
    }

    /**
     * Decrement a column value
     */
    public function decrement($id, $column, $amount = 1) {
        return $this->increment($id, $column, -$amount);
    }

    /**
     * Get distinct values for a column
     */
    public function distinct($column, $where = []) {
        $sql = "SELECT DISTINCT $column FROM {$this->table}";
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

        $results = $this->db->fetchAll($sql, $params);
        return array_column($results, $column);
    }

    /**
     * Get all records (alias for findAll with no parameters)
     */
    public function getAll($orderBy = 'id DESC') {
        return $this->findAll([], $orderBy);
    }

    /**
     * Truncate table (use with caution!)
     */
    public function truncate() {
        // Only allow in development mode
        if (defined('APP_ENV') && APP_ENV === 'production') {
            throw new Exception('Cannot truncate table in production environment');
        }

        $sql = "TRUNCATE TABLE {$this->table}";
        return $this->db->query($sql);
    }
}
