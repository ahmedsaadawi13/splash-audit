<?php
// FILE: /app/controllers/RiskController.php

class RiskController extends Controller {
    private $riskModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->riskModel = $this->model('Risk');
    }

    public function index() {
        $risks = $this->riskModel->getOpenRisks();

        $this->view('risk/index', [
            'page_title' => 'Risk Register',
            'risks' => $risks,
            'statistics' => $this->riskModel->getStatistics()
        ]);
    }

    public function create() {
        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $userModel = $this->model('User');
        $users = $userModel->getActiveUsers();

        $this->view('risk/create', [
            'page_title' => 'Add Risk',
            'users' => $users
        ]);
    }

    public function store() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/risk');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $data = [
            'title' => $this->sanitize($this->post('title')),
            'description' => $this->sanitize($this->post('description')),
            'category' => $this->sanitize($this->post('category')),
            'owner_user_id' => $this->post('owner_user_id'),
            'likelihood' => $this->post('likelihood'),
            'impact' => $this->post('impact'),
            'control_effectiveness' => $this->post('control_effectiveness', 0),
            'controls_description' => $this->sanitize($this->post('controls_description')),
            'status' => 'open'
        ];

        $validator = new Validator($data);
        $validator->required('title');
        $validator->required('likelihood');
        $validator->required('impact');
        $validator->integer('likelihood');
        $validator->integer('impact');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/risk/create');
        }

        try {
            $id = $this->riskModel->create($data);
            $this->logActivity('created', 'risk', $id, 'Created risk: ' . $data['title']);
            $this->setSuccess('Risk added successfully.');
            $this->redirect('/risk');

        } catch (Exception $e) {
            error_log('Risk creation error: ' . $e->getMessage());
            $this->setError('Failed to create risk.');
            $this->redirect('/risk/create');
        }
    }

    public function heatmap() {
        $heatmapData = $this->riskModel->getHeatmapData();

        $this->view('risk/heatmap', [
            'page_title' => 'Risk Heatmap',
            'heatmapData' => $heatmapData
        ]);
    }
}
