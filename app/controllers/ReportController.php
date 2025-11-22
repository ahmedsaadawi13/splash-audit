<?php
// FILE: /app/controllers/ReportController.php

class ReportController extends Controller {
    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
    }

    public function index() {
        $this->view('report/index', [
            'page_title' => 'Reports'
        ]);
    }

    /**
     * Audit Summary Report
     */
    public function auditSummary() {
        $auditPlanModel = $this->model('AuditPlan');
        $plans = $auditPlanModel->findAll([], 'planned_start DESC');

        if ($this->get('export') === 'csv') {
            $this->exportCSV('audit_summary', $plans, [
                'id' => 'ID',
                'title' => 'Title',
                'audit_type' => 'Type',
                'planned_start' => 'Start Date',
                'planned_end' => 'End Date',
                'status' => 'Status'
            ]);
        }

        $this->view('report/audit_summary', [
            'page_title' => 'Audit Summary Report',
            'plans' => $plans
        ]);
    }

    /**
     * Findings by Severity Report
     */
    public function findingsBySeverity() {
        $findingModel = $this->model('Finding');
        $findings = $findingModel->findAll([], 'severity DESC, created_at DESC');

        if ($this->get('export') === 'csv') {
            $this->exportCSV('findings_by_severity', $findings, [
                'id' => 'ID',
                'title' => 'Title',
                'severity' => 'Severity',
                'status' => 'Status',
                'created_at' => 'Date Created'
            ]);
        }

        $this->view('report/findings_by_severity', [
            'page_title' => 'Findings by Severity Report',
            'findings' => $findings,
            'statistics' => $findingModel->getStatistics()
        ]);
    }

    /**
     * Corrective Actions Status Report
     */
    public function correctiveActionsStatus() {
        $actionModel = $this->model('CorrectiveAction');
        $actions = $actionModel->findAll([], 'due_date ASC');

        if ($this->get('export') === 'csv') {
            $this->exportCSV('corrective_actions_status', $actions, [
                'id' => 'ID',
                'action_description' => 'Action',
                'due_date' => 'Due Date',
                'status' => 'Status'
            ]);
        }

        $this->view('report/corrective_actions_status', [
            'page_title' => 'Corrective Actions Status Report',
            'actions' => $actions,
            'statistics' => $actionModel->getStatistics()
        ]);
    }

    /**
     * Risk Register Export
     */
    public function riskRegister() {
        $riskModel = $this->model('Risk');
        $risks = $riskModel->getOpenRisks();

        if ($this->get('export') === 'csv') {
            $this->exportCSV('risk_register', $risks, [
                'id' => 'ID',
                'title' => 'Title',
                'category' => 'Category',
                'likelihood' => 'Likelihood',
                'impact' => 'Impact',
                'inherent_risk' => 'Inherent Risk',
                'residual_risk' => 'Residual Risk',
                'status' => 'Status'
            ]);
        }

        $this->view('report/risk_register', [
            'page_title' => 'Risk Register Report',
            'risks' => $risks,
            'statistics' => $riskModel->getStatistics()
        ]);
    }

    /**
     * Export data to CSV
     */
    private function exportCSV($filename, $data, $headers) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, array_values($headers));

        // Write data
        foreach ($data as $row) {
            $csvRow = [];
            foreach (array_keys($headers) as $key) {
                $csvRow[] = $row[$key] ?? '';
            }
            fputcsv($output, $csvRow);
        }

        fclose($output);
        exit;
    }
}
