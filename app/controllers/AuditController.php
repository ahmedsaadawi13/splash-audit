<?php
// FILE: /app/controllers/AuditController.php

class AuditController extends Controller {
    private $auditUniverseModel;
    private $auditProgramModel;
    private $auditPlanModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->auditUniverseModel = $this->model('AuditUniverse');
        $this->auditProgramModel = $this->model('AuditProgram');
        $this->auditPlanModel = $this->model('AuditPlan');
    }

    // ============================================
    // AUDIT UNIVERSE
    // ============================================

    public function universe() {
        $entities = $this->auditUniverseModel->getActiveEntities();

        $this->view('audit/universe/index', [
            'page_title' => 'Audit Universe',
            'entities' => $entities
        ]);
    }

    public function createUniverse() {
        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $userModel = $this->model('User');
        $users = $userModel->getActiveUsers();

        $this->view('audit/universe/create', [
            'page_title' => 'Add Auditable Entity',
            'users' => $users
        ]);
    }

    public function storeUniverse() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/audit/universe');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $data = [
            'name' => $this->sanitize($this->post('name')),
            'category' => $this->sanitize($this->post('category')),
            'owner_user_id' => $this->post('owner_user_id'),
            'risk_score' => $this->post('risk_score', 0),
            'notes' => $this->sanitize($this->post('notes')),
            'status' => 'active'
        ];

        $validator = new Validator($data);
        $validator->required('name');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/audit/universe/create');
        }

        try {
            $id = $this->auditUniverseModel->create($data);
            $this->logActivity('created', 'audit_universe', $id, 'Created auditable entity: ' . $data['name']);
            $this->setSuccess('Auditable entity created successfully.');
            $this->redirect('/audit/universe');

        } catch (Exception $e) {
            error_log('Universe creation error: ' . $e->getMessage());
            $this->setError('Failed to create entity.');
            $this->redirect('/audit/universe/create');
        }
    }

    // ============================================
    // AUDIT PROGRAMS
    // ============================================

    public function programs() {
        $programs = $this->auditProgramModel->findAll([], 'year DESC, created_at DESC');

        $this->view('audit/program/index', [
            'page_title' => 'Audit Programs',
            'programs' => $programs
        ]);
    }

    public function createProgram() {
        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $this->view('audit/program/create', [
            'page_title' => 'Create Audit Program'
        ]);
    }

    public function storeProgram() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/audit/programs');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $data = [
            'title' => $this->sanitize($this->post('title')),
            'year' => $this->post('year'),
            'scope' => $this->sanitize($this->post('scope')),
            'objectives' => $this->sanitize($this->post('objectives')),
            'start_date' => $this->post('start_date'),
            'end_date' => $this->post('end_date'),
            'status' => 'draft'
        ];

        $validator = new Validator($data);
        $validator->required('title');
        $validator->required('year');
        $validator->integer('year');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/audit/program/create');
        }

        try {
            $id = $this->auditProgramModel->create($data);
            $this->logActivity('created', 'audit_program', $id, 'Created audit program: ' . $data['title']);
            $this->setSuccess('Audit program created successfully.');
            $this->redirect('/audit/programs');

        } catch (Exception $e) {
            error_log('Program creation error: ' . $e->getMessage());
            $this->setError('Failed to create program.');
            $this->redirect('/audit/program/create');
        }
    }

    // ============================================
    // AUDIT PLANS
    // ============================================

    public function plans() {
        $plans = $this->auditPlanModel->findAll([], 'planned_start DESC');

        $this->view('audit/plan/index', [
            'page_title' => 'Audit Plans',
            'plans' => $plans
        ]);
    }

    public function createPlan() {
        Auth::requireRole(['tenant_admin', 'audit_manager']);

        // Check quota
        $quotaChecker = new QuotaChecker();
        if (!$quotaChecker->canCreateAuditPlan()) {
            $this->setError('Audit plan limit reached for your subscription.');
            $this->redirect('/audit/plans');
        }

        $programs = $this->auditProgramModel->getApprovedPrograms();
        $userModel = $this->model('User');
        $auditors = $userModel->findByRole('internal_auditor');

        $this->view('audit/plan/create', [
            'page_title' => 'Create Audit Plan',
            'programs' => $programs,
            'auditors' => $auditors
        ]);
    }

    public function storePlan() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/audit/plans');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        // Check quota
        $quotaChecker = new QuotaChecker();
        if (!$quotaChecker->canCreateAuditPlan()) {
            $this->setError('Audit plan limit reached for your subscription.');
            $this->redirect('/audit/plans');
        }

        $data = [
            'program_id' => $this->post('program_id'),
            'title' => $this->sanitize($this->post('title')),
            'description' => $this->sanitize($this->post('description')),
            'audit_type' => $this->post('audit_type'),
            'planned_start' => $this->post('planned_start'),
            'planned_end' => $this->post('planned_end'),
            'lead_auditor_id' => $this->post('lead_auditor_id'),
            'scope' => $this->sanitize($this->post('scope')),
            'objectives' => $this->sanitize($this->post('objectives')),
            'status' => 'scheduled'
        ];

        $validator = new Validator($data);
        $validator->required('title');
        $validator->required('audit_type');
        $validator->required('planned_start');
        $validator->required('planned_end');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/audit/plan/create');
        }

        try {
            $id = $this->auditPlanModel->create($data);
            $this->logActivity('created', 'audit_plan', $id, 'Created audit plan: ' . $data['title']);
            $this->setSuccess('Audit plan created successfully.');
            $this->redirect('/audit/plans');

        } catch (Exception $e) {
            error_log('Audit plan creation error: ' . $e->getMessage());
            $this->setError('Failed to create audit plan.');
            $this->redirect('/audit/plan/create');
        }
    }

    public function viewPlan($id) {
        $plan = $this->auditPlanModel->findById($id);

        if (!$plan) {
            $this->setError('Audit plan not found.');
            $this->redirect('/audit/plans');
        }

        // Get findings for this audit
        $findingModel = $this->model('Finding');
        $findings = $findingModel->getByAuditPlan($id);

        $this->view('audit/plan/view', [
            'page_title' => 'Audit Plan: ' . $plan['title'],
            'plan' => $plan,
            'findings' => $findings
        ]);
    }
}
