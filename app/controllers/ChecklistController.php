<?php
// FILE: /app/controllers/ChecklistController.php

class ChecklistController extends Controller {
    private $checklistModel;
    private $checklistItemModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->checklistModel = $this->model('AuditChecklist');
        $this->checklistItemModel = $this->model('ChecklistItem');
    }

    /**
     * List all checklists
     */
    public function index() {
        $checklists = $this->checklistModel->getAll();

        $this->view('checklist/index', [
            'page_title' => 'Audit Checklists',
            'checklists' => $checklists
        ]);
    }

    /**
     * Show checklist creation form
     */
    public function create() {
        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $auditProgramModel = $this->model('AuditProgram');
        $programs = $auditProgramModel->getAll();

        $this->view('checklist/create', [
            'page_title' => 'Create Checklist',
            'programs' => $programs
        ]);
    }

    /**
     * Store new checklist
     */
    public function store() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/checklist');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $data = [
            'audit_program_id' => $this->post('audit_program_id'),
            'title' => $this->sanitize($this->post('title')),
            'description' => $this->sanitize($this->post('description')),
            'version' => $this->sanitize($this->post('version', '1.0')),
            'status' => 'draft'
        ];

        $validator = new Validator($data);
        $validator->required('audit_program_id');
        $validator->required('title');
        $validator->integer('audit_program_id');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/checklist/create');
        }

        try {
            $id = $this->checklistModel->create($data);
            $this->logActivity('created', 'checklist', $id, 'Created checklist: ' . $data['title']);
            $this->setSuccess('Checklist created successfully.');
            $this->redirect('/checklist/view/' . $id);

        } catch (Exception $e) {
            error_log('Checklist creation error: ' . $e->getMessage());
            $this->setError('Failed to create checklist.');
            $this->redirect('/checklist/create');
        }
    }

    /**
     * View checklist with items
     */
    public function view($id) {
        $checklist = $this->checklistModel->findById($id);

        if (!$checklist) {
            $this->setError('Checklist not found.');
            $this->redirect('/checklist');
        }

        $items = $this->checklistItemModel->getByChecklist($id);

        $this->view('checklist/view', [
            'page_title' => 'Checklist: ' . $checklist['title'],
            'checklist' => $checklist,
            'items' => $items
        ]);
    }

    /**
     * Show edit checklist form
     */
    public function edit($id) {
        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $checklist = $this->checklistModel->findById($id);

        if (!$checklist) {
            $this->setError('Checklist not found.');
            $this->redirect('/checklist');
        }

        $auditProgramModel = $this->model('AuditProgram');
        $programs = $auditProgramModel->getAll();

        $this->view('checklist/edit', [
            'page_title' => 'Edit Checklist',
            'checklist' => $checklist,
            'programs' => $programs
        ]);
    }

    /**
     * Update checklist
     */
    public function update($id) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/checklist');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $checklist = $this->checklistModel->findById($id);

        if (!$checklist) {
            $this->setError('Checklist not found.');
            $this->redirect('/checklist');
        }

        $data = [
            'audit_program_id' => $this->post('audit_program_id'),
            'title' => $this->sanitize($this->post('title')),
            'description' => $this->sanitize($this->post('description')),
            'version' => $this->sanitize($this->post('version')),
            'status' => $this->post('status')
        ];

        $validator = new Validator($data);
        $validator->required('title');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/checklist/edit/' . $id);
        }

        try {
            $this->checklistModel->update($id, $data);
            $this->logActivity('updated', 'checklist', $id, 'Updated checklist: ' . $data['title']);
            $this->setSuccess('Checklist updated successfully.');
            $this->redirect('/checklist/view/' . $id);

        } catch (Exception $e) {
            error_log('Checklist update error: ' . $e->getMessage());
            $this->setError('Failed to update checklist.');
            $this->redirect('/checklist/edit/' . $id);
        }
    }

    /**
     * Add item to checklist
     */
    public function addItem($checklistId) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/checklist/view/' . $checklistId);
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $data = [
            'checklist_id' => $checklistId,
            'item_text' => $this->sanitize($this->post('item_text')),
            'procedure' => $this->sanitize($this->post('procedure')),
            'risk_reference' => $this->sanitize($this->post('risk_reference')),
            'requirement_reference' => $this->sanitize($this->post('requirement_reference')),
            'sort_order' => $this->post('sort_order', 0)
        ];

        $validator = new Validator($data);
        $validator->required('item_text');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/checklist/view/' . $checklistId);
        }

        try {
            $id = $this->checklistItemModel->create($data);
            $this->logActivity('created', 'checklist_item', $id, 'Added item to checklist');
            $this->setSuccess('Checklist item added successfully.');
            $this->redirect('/checklist/view/' . $checklistId);

        } catch (Exception $e) {
            error_log('Checklist item creation error: ' . $e->getMessage());
            $this->setError('Failed to add checklist item.');
            $this->redirect('/checklist/view/' . $checklistId);
        }
    }

    /**
     * Delete checklist item
     */
    public function deleteItem($itemId) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/checklist');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $item = $this->checklistItemModel->findById($itemId);

        if (!$item) {
            $this->setError('Checklist item not found.');
            $this->redirect('/checklist');
        }

        try {
            $this->checklistItemModel->delete($itemId);
            $this->logActivity('deleted', 'checklist_item', $itemId, 'Deleted checklist item');
            $this->setSuccess('Checklist item deleted successfully.');
            $this->redirect('/checklist/view/' . $item['checklist_id']);

        } catch (Exception $e) {
            error_log('Checklist item deletion error: ' . $e->getMessage());
            $this->setError('Failed to delete checklist item.');
            $this->redirect('/checklist/view/' . $item['checklist_id']);
        }
    }

    /**
     * Publish checklist
     */
    public function publish($id) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/checklist/view/' . $id);
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        try {
            $this->checklistModel->update($id, ['status' => 'active']);
            $this->logActivity('updated', 'checklist', $id, 'Published checklist');
            $this->setSuccess('Checklist published successfully.');
            $this->redirect('/checklist/view/' . $id);

        } catch (Exception $e) {
            error_log('Checklist publish error: ' . $e->getMessage());
            $this->setError('Failed to publish checklist.');
            $this->redirect('/checklist/view/' . $id);
        }
    }
}
