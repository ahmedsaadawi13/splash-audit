<?php
// FILE: /app/models/AuditProgram.php

class AuditProgram extends Model {
    protected $table = 'audit_programs';
    protected $tenantIsolation = true;

    /**
     * Get programs by year
     */
    public function getByYear($year) {
        return $this->findAll(['year' => $year]);
    }

    /**
     * Get approved programs
     */
    public function getApprovedPrograms() {
        return $this->findAll(['status' => 'approved']);
    }

    /**
     * Get current year programs
     */
    public function getCurrentYearPrograms() {
        return $this->getByYear(date('Y'));
    }
}
