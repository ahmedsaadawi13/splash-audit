<?php
// FILE: /app/helpers/Validator.php

class Validator {
    private $errors = [];
    private $data = [];

    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Validate required field
     */
    public function required($field, $message = null) {
        $value = $this->data[$field] ?? null;

        if (empty($value) && $value !== '0') {
            $this->errors[$field][] = $message ?? ucfirst($field) . ' is required.';
            return false;
        }
        return true;
    }

    /**
     * Validate email
     */
    public function email($field, $message = null) {
        $value = $this->data[$field] ?? null;

        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? 'Invalid email address.';
            return false;
        }
        return true;
    }

    /**
     * Validate minimum length
     */
    public function minLength($field, $length, $message = null) {
        $value = $this->data[$field] ?? '';

        if (strlen($value) < $length) {
            $this->errors[$field][] = $message ?? ucfirst($field) . " must be at least $length characters.";
            return false;
        }
        return true;
    }

    /**
     * Validate maximum length
     */
    public function maxLength($field, $length, $message = null) {
        $value = $this->data[$field] ?? '';

        if (strlen($value) > $length) {
            $this->errors[$field][] = $message ?? ucfirst($field) . " must not exceed $length characters.";
            return false;
        }
        return true;
    }

    /**
     * Validate numeric
     */
    public function numeric($field, $message = null) {
        $value = $this->data[$field] ?? null;

        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field][] = $message ?? ucfirst($field) . ' must be numeric.';
            return false;
        }
        return true;
    }

    /**
     * Validate integer
     */
    public function integer($field, $message = null) {
        $value = $this->data[$field] ?? null;

        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = $message ?? ucfirst($field) . ' must be an integer.';
            return false;
        }
        return true;
    }

    /**
     * Validate date
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        $value = $this->data[$field] ?? null;

        if (!empty($value)) {
            $d = DateTime::createFromFormat($format, $value);
            if (!$d || $d->format($format) !== $value) {
                $this->errors[$field][] = $message ?? 'Invalid date format.';
                return false;
            }
        }
        return true;
    }

    /**
     * Validate matches another field
     */
    public function matches($field, $matchField, $message = null) {
        $value = $this->data[$field] ?? null;
        $matchValue = $this->data[$matchField] ?? null;

        if ($value !== $matchValue) {
            $this->errors[$field][] = $message ?? ucfirst($field) . ' does not match.';
            return false;
        }
        return true;
    }

    /**
     * Validate unique in database
     */
    public function unique($field, $table, $excludeId = null, $message = null) {
        $value = $this->data[$field] ?? null;

        if (empty($value)) {
            return true;
        }

        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $field = ?";
        $params = [$value];

        // Add tenant isolation
        if (Auth::check()) {
            $sql .= ' AND tenant_id = ?';
            $params[] = Auth::tenantId();
        }

        // Exclude current record if updating
        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $result = $db->fetchOne($sql, $params);

        if ($result['count'] > 0) {
            $this->errors[$field][] = $message ?? ucfirst($field) . ' already exists.';
            return false;
        }
        return true;
    }

    /**
     * Validate exists in database
     */
    public function exists($field, $table, $column = 'id', $message = null) {
        $value = $this->data[$field] ?? null;

        if (empty($value)) {
            return true;
        }

        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM $table WHERE $column = ?";
        $params = [$value];

        $result = $db->fetchOne($sql, $params);

        if ($result['count'] == 0) {
            $this->errors[$field][] = $message ?? 'Selected ' . ucfirst($field) . ' is invalid.';
            return false;
        }
        return true;
    }

    /**
     * Validate in array
     */
    public function in($field, $values, $message = null) {
        $value = $this->data[$field] ?? null;

        if (!empty($value) && !in_array($value, $values)) {
            $this->errors[$field][] = $message ?? 'Invalid value for ' . ucfirst($field) . '.';
            return false;
        }
        return true;
    }

    /**
     * Check if validation passed
     */
    public function passes() {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails() {
        return !empty($this->errors);
    }

    /**
     * Get all errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function getError($field) {
        return $this->errors[$field][0] ?? '';
    }

    /**
     * Get all errors as a flat array
     */
    public function getAllErrors() {
        $allErrors = [];
        foreach ($this->errors as $fieldErrors) {
            $allErrors = array_merge($allErrors, $fieldErrors);
        }
        return $allErrors;
    }

    /**
     * Sanitize data
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}
