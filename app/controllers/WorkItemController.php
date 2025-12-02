<?php
// FILE: /app/controllers/WorkItemController.php

class WorkItemController extends Controller {
    private $workItemModel;
    private $checklistItemModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->workItemModel = $this->model('AuditWorkItem');
        $this->checklistItemModel = $this->model('ChecklistItem');
    }

    /**
     * List work items for audit plan
     */
    public function index($auditPlanId) {
        $auditPlanModel = $this->model('AuditPlan');
        $auditPlan = $auditPlanModel->findById($auditPlanId);

        if (!$auditPlan) {
            $this->setError('Audit plan not found.');
            $this->redirect('/audit/plan');
        }

        $workItems = $this->workItemModel->getByAuditPlan($auditPlanId);
        $statistics = $this->workItemModel->getStatistics($auditPlanId);

        $this->view('workitem/index', [
            'page_title' => 'Work Items: ' . $auditPlan['title'],
            'auditPlan' => $auditPlan,
            'workItems' => $workItems,
            'statistics' => $statistics
        ]);
    }

    /**
     * My assigned work items
     */
    public function myItems() {
        $workItems = $this->workItemModel->getByAuditor(Auth::userId());

        $pendingCount = 0;
        foreach ($workItems as $item) {
            if ($item['result'] === 'pending') {
                $pendingCount++;
            }
        }

        $this->view('workitem/my_items', [
            'page_title' => 'My Work Items',
            'workItems' => $workItems,
            'pendingCount' => $pendingCount
        ]);
    }

    /**
     * Show work item execution form
     */
    public function execute($id) {
        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor']);

        $workItem = $this->db->fetchOne(
            "SELECT awi.*,
                    ci.item_text,
                    ci.procedure,
                    ci.risk_reference,
                    ci.requirement_reference,
                    ac.title as checklist_title,
                    ap.title as audit_title
             FROM audit_work_items awi
             JOIN checklist_items ci ON awi.checklist_item_id = ci.id
             JOIN audit_checklists ac ON ci.checklist_id = ac.id
             JOIN audit_plans ap ON awi.audit_plan_id = ap.id
             WHERE awi.id = ? AND awi.tenant_id = ?",
            [$id, Auth::tenantId()]
        );

        if (!$workItem) {
            $this->setError('Work item not found.');
            $this->redirect('/workitem/my-items');
        }

        $this->view('workitem/execute', [
            'page_title' => 'Execute Work Item',
            'workItem' => $workItem
        ]);
    }

    /**
     * Save work item result
     */
    public function save($id) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/workitem/my-items');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor']);

        $workItem = $this->workItemModel->findById($id);

        if (!$workItem) {
            $this->setError('Work item not found.');
            $this->redirect('/workitem/my-items');
        }

        $result = $this->post('result');
        $evidenceNotes = $this->sanitize($this->post('evidence_notes'));
        $evidenceFile = null;

        $validator = new Validator(['result' => $result]);
        $validator->required('result');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/workitem/execute/' . $id);
        }

        // Handle file upload if present
        if (!empty($_FILES['evidence_file']['name'])) {
            try {
                $uploader = new FileUpload('evidence_file');
                $uploader->setAllowedTypes(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx']);
                $uploader->setMaxSize(10); // 10MB

                if ($uploader->upload()) {
                    $evidenceFile = $uploader->getUploadedFileName();
                }
            } catch (Exception $e) {
                error_log('File upload error: ' . $e->getMessage());
                $this->setError('File upload failed: ' . $e->getMessage());
                $this->redirect('/workitem/execute/' . $id);
            }
        }

        try {
            $this->workItemModel->markCompleted($id, $result, $evidenceNotes, $evidenceFile);
            $this->logActivity('updated', 'work_item', $id, 'Completed work item with result: ' . $result);

            // If result is 'fail', prompt to create finding
            if ($result === 'fail') {
                $this->setSuccess('Work item completed. Consider creating a finding for this failure.');
                $this->redirect('/workitem/create-finding/' . $id);
            } else {
                $this->setSuccess('Work item completed successfully.');
                $this->redirect('/workitem/my-items');
            }

        } catch (Exception $e) {
            error_log('Work item update error: ' . $e->getMessage());
            $this->setError('Failed to save work item result.');
            $this->redirect('/workitem/execute/' . $id);
        }
    }

    /**
     * Show finding creation form for failed work item
     */
    public function createFinding($workItemId) {
        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor']);

        $workItem = $this->db->fetchOne(
            "SELECT awi.*,
                    ci.item_text,
                    ci.procedure,
                    ap.title as audit_title,
                    ap.id as audit_plan_id
             FROM audit_work_items awi
             JOIN checklist_items ci ON awi.checklist_item_id = ci.id
             JOIN audit_plans ap ON awi.audit_plan_id = ap.id
             WHERE awi.id = ? AND awi.tenant_id = ?",
            [$workItemId, Auth::tenantId()]
        );

        if (!$workItem) {
            $this->setError('Work item not found.');
            $this->redirect('/workitem/my-items');
        }

        $userModel = $this->model('User');
        $users = $userModel->getActiveUsers();

        $this->view('workitem/create_finding', [
            'page_title' => 'Create Finding from Work Item',
            'workItem' => $workItem,
            'users' => $users
        ]);
    }

    /**
     * Store finding from failed work item
     */
    public function storeFinding() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/workitem/my-items');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor']);

        $workItemId = $this->post('work_item_id');
        $auditPlanId = $this->post('audit_plan_id');

        $data = [
            'audit_plan_id' => $auditPlanId,
            'title' => $this->sanitize($this->post('title')),
            'description' => $this->sanitize($this->post('description')),
            'severity' => $this->post('severity'),
            'cause' => $this->sanitize($this->post('cause')),
            'effect' => $this->sanitize($this->post('effect')),
            'criteria' => $this->sanitize($this->post('criteria')),
            'assigned_to_user_id' => $this->post('assigned_to_user_id'),
            'status' => 'open'
        ];

        $validator = new Validator($data);
        $validator->required('title');
        $validator->required('description');
        $validator->required('severity');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/workitem/create-finding/' . $workItemId);
        }

        try {
            $findingModel = $this->model('Finding');
            $findingId = $findingModel->create($data);

            $this->logActivity('created', 'finding', $findingId, 'Created finding from work item');
            $this->setSuccess('Finding created successfully.');
            $this->redirect('/finding/view/' . $findingId);

        } catch (Exception $e) {
            error_log('Finding creation error: ' . $e->getMessage());
            $this->setError('Failed to create finding.');
            $this->redirect('/workitem/create-finding/' . $workItemId);
        }
    }

    /**
     * Assign work items to auditor
     */
    public function assign() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/audit/plan');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $workItemIds = $this->post('work_item_ids', []);
        $auditorId = $this->post('auditor_id');

        if (empty($workItemIds) || empty($auditorId)) {
            $this->setError('Please select work items and an auditor.');
            $this->redirectBack();
        }

        try {
            $count = 0;
            foreach ($workItemIds as $itemId) {
                $this->workItemModel->update($itemId, ['auditor_id' => $auditorId]);
                $count++;
            }

            $this->logActivity('updated', 'work_items', 0, "Assigned $count work items to auditor");
            $this->setSuccess("Successfully assigned $count work items.");
            $this->redirectBack();

        } catch (Exception $e) {
            error_log('Work item assignment error: ' . $e->getMessage());
            $this->setError('Failed to assign work items.');
            $this->redirectBack();
        }
    }

    /**
     * View work item detail
     */
    public function view($id) {
        $workItem = $this->db->fetchOne(
            "SELECT awi.*,
                    ci.item_text,
                    ci.procedure,
                    ci.risk_reference,
                    ci.requirement_reference,
                    ac.title as checklist_title,
                    ap.title as audit_title,
                    u.first_name,
                    u.last_name
             FROM audit_work_items awi
             JOIN checklist_items ci ON awi.checklist_item_id = ci.id
             JOIN audit_checklists ac ON ci.checklist_id = ac.id
             JOIN audit_plans ap ON awi.audit_plan_id = ap.id
             LEFT JOIN users u ON awi.auditor_id = u.id
             WHERE awi.id = ? AND awi.tenant_id = ?",
            [$id, Auth::tenantId()]
        );

        if (!$workItem) {
            $this->setError('Work item not found.');
            $this->redirect('/workitem/my-items');
        }

        $this->view('workitem/view', [
            'page_title' => 'Work Item Details',
            'workItem' => $workItem
        ]);
    }
}
