<?php
// FILE: /app/controllers/FindingController.php

class FindingController extends Controller {
    private $findingModel;
    private $correctiveActionModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->findingModel = $this->model('Finding');
        $this->correctiveActionModel = $this->model('CorrectiveAction');
    }

    public function index() {
        $findings = $this->findingModel->findAll([], 'severity DESC, created_at DESC');

        $this->view('finding/index', [
            'page_title' => 'Findings',
            'findings' => $findings,
            'statistics' => $this->findingModel->getStatistics()
        ]);
    }

    public function create() {
        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor']);

        $auditPlanModel = $this->model('AuditPlan');
        $auditPlans = $auditPlanModel->getByStatus('in_progress');

        $userModel = $this->model('User');
        $users = $userModel->getActiveUsers();

        $this->view('finding/create', [
            'page_title' => 'Create Finding',
            'auditPlans' => $auditPlans,
            'users' => $users
        ]);
    }

    public function store() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/finding');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor']);

        $data = [
            'audit_plan_id' => $this->post('audit_plan_id'),
            'title' => $this->sanitize($this->post('title')),
            'description' => $this->sanitize($this->post('description')),
            'severity' => $this->post('severity'),
            'cause' => $this->sanitize($this->post('cause')),
            'effect' => $this->sanitize($this->post('effect')),
            'criteria' => $this->sanitize($this->post('criteria')),
            'risk_rating' => $this->post('risk_rating', 0),
            'assigned_to_user_id' => $this->post('assigned_to_user_id'),
            'status' => 'open'
        ];

        $validator = new Validator($data);
        $validator->required('audit_plan_id');
        $validator->required('title');
        $validator->required('description');
        $validator->required('severity');
        $validator->in('severity', ['high', 'medium', 'low']);

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/finding/create');
        }

        try {
            $id = $this->findingModel->create($data);
            $this->logActivity('created', 'finding', $id, 'Created finding: ' . $data['title']);

            // Send notification if assigned
            if ($data['assigned_to_user_id']) {
                $userModel = $this->model('User');
                $user = $userModel->findById($data['assigned_to_user_id']);
                if ($user) {
                    $finding = $this->findingModel->findById($id);
                    Email::sendFindingAssignedNotification($finding, $user);
                }
            }

            $this->setSuccess('Finding created successfully.');
            $this->redirect('/finding');

        } catch (Exception $e) {
            error_log('Finding creation error: ' . $e->getMessage());
            $this->setError('Failed to create finding.');
            $this->redirect('/finding/create');
        }
    }

    public function view($id) {
        $finding = $this->findingModel->findById($id);

        if (!$finding) {
            $this->setError('Finding not found.');
            $this->redirect('/finding');
        }

        $correctiveActions = $this->correctiveActionModel->getByFinding($id);

        $this->view('finding/view', [
            'page_title' => 'Finding: ' . $finding['title'],
            'finding' => $finding,
            'correctiveActions' => $correctiveActions
        ]);
    }
}
