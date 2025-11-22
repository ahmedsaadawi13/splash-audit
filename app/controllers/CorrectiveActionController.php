<?php
// FILE: /app/controllers/CorrectiveActionController.php

class CorrectiveActionController extends Controller {
    private $correctiveActionModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->correctiveActionModel = $this->model('CorrectiveAction');
    }

    public function index() {
        $actions = $this->correctiveActionModel->findAll([], 'due_date ASC');

        $this->view('corrective_action/index', [
            'page_title' => 'Corrective Actions',
            'actions' => $actions,
            'statistics' => $this->correctiveActionModel->getStatistics()
        ]);
    }

    public function create() {
        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor', 'process_owner']);

        // Check quota
        $quotaChecker = new QuotaChecker();
        if (!$quotaChecker->canCreateCorrectiveAction()) {
            $this->setError('Corrective action limit reached for your subscription.');
            $this->redirect('/corrective-action');
        }

        $findingModel = $this->model('Finding');
        $findings = $findingModel->getByStatus('open');

        $userModel = $this->model('User');
        $users = $userModel->getActiveUsers();

        $this->view('corrective_action/create', [
            'page_title' => 'Create Corrective Action',
            'findings' => $findings,
            'users' => $users
        ]);
    }

    public function store() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/corrective-action');
        }

        // Check quota
        $quotaChecker = new QuotaChecker();
        if (!$quotaChecker->canCreateCorrectiveAction()) {
            $this->setError('Corrective action limit reached for your subscription.');
            $this->redirect('/corrective-action');
        }

        $data = [
            'finding_id' => $this->post('finding_id'),
            'action_description' => $this->sanitize($this->post('action_description')),
            'due_date' => $this->post('due_date'),
            'responsible_user_id' => $this->post('responsible_user_id'),
            'status' => 'open'
        ];

        // Handle file upload
        if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] === UPLOAD_ERR_OK) {
            $fileUpload = new FileUpload();
            $uploadResult = $fileUpload->upload($_FILES['evidence_file'], 'evidence');

            if ($uploadResult) {
                $data['evidence_file'] = $uploadResult['relative_path'];
            }
        }

        $validator = new Validator($data);
        $validator->required('finding_id');
        $validator->required('action_description');
        $validator->required('due_date');
        $validator->required('responsible_user_id');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/corrective-action/create');
        }

        try {
            $id = $this->correctiveActionModel->create($data);
            $this->logActivity('created', 'corrective_action', $id, 'Created corrective action');
            $this->setSuccess('Corrective action created successfully.');
            $this->redirect('/corrective-action');

        } catch (Exception $e) {
            error_log('Corrective action creation error: ' . $e->getMessage());
            $this->setError('Failed to create corrective action.');
            $this->redirect('/corrective-action/create');
        }
    }

    public function updateStatus($id) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/corrective-action');
        }

        $action = $this->correctiveActionModel->findById($id);

        if (!$action) {
            $this->setError('Corrective action not found.');
            $this->redirect('/corrective-action');
        }

        $status = $this->post('status');

        try {
            $updateData = ['status' => $status];

            if ($status === 'completed') {
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            }

            $this->correctiveActionModel->update($id, $updateData);
            $this->logActivity('updated', 'corrective_action', $id, 'Status updated to: ' . $status);
            $this->setSuccess('Status updated successfully.');

        } catch (Exception $e) {
            error_log('Status update error: ' . $e->getMessage());
            $this->setError('Failed to update status.');
        }

        $this->redirect('/corrective-action');
    }
}
