<?php
// FILE: /app/controllers/ComplianceController.php

class ComplianceController extends Controller {
    private $complianceAreaModel;
    private $requirementModel;
    private $checkModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        $this->complianceAreaModel = $this->model('ComplianceArea');
        $this->requirementModel = $this->model('ComplianceRequirement');
        $this->checkModel = $this->model('ComplianceCheck');
    }

    /**
     * List compliance areas
     */
    public function index() {
        $areas = $this->complianceAreaModel->getAll();

        $this->view('compliance/index', [
            'page_title' => 'Compliance Management',
            'areas' => $areas
        ]);
    }

    /**
     * View compliance area with requirements
     */
    public function view($id) {
        $area = $this->complianceAreaModel->findById($id);

        if (!$area) {
            $this->setError('Compliance area not found.');
            $this->redirect('/compliance');
        }

        $requirements = $this->requirementModel->getWithCheckStatus($id);
        $statistics = $this->requirementModel->getAreaStatistics($id);

        $this->view('compliance/view', [
            'page_title' => $area['name'],
            'area' => $area,
            'requirements' => $requirements,
            'statistics' => $statistics
        ]);
    }

    /**
     * Show create compliance area form
     */
    public function createArea() {
        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $this->view('compliance/create_area', [
            'page_title' => 'Create Compliance Area'
        ]);
    }

    /**
     * Store new compliance area
     */
    public function storeArea() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/compliance');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $data = [
            'name' => $this->sanitize($this->post('name')),
            'description' => $this->sanitize($this->post('description')),
            'framework' => $this->sanitize($this->post('framework'))
        ];

        $validator = new Validator($data);
        $validator->required('name');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/compliance/create-area');
        }

        try {
            $id = $this->complianceAreaModel->create($data);
            $this->logActivity('created', 'compliance_area', $id, 'Created compliance area: ' . $data['name']);
            $this->setSuccess('Compliance area created successfully.');
            $this->redirect('/compliance/view/' . $id);

        } catch (Exception $e) {
            error_log('Compliance area creation error: ' . $e->getMessage());
            $this->setError('Failed to create compliance area.');
            $this->redirect('/compliance/create-area');
        }
    }

    /**
     * Add requirement to compliance area
     */
    public function addRequirement($areaId) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/compliance/view/' . $areaId);
        }

        Auth::requireRole(['tenant_admin', 'audit_manager']);

        $data = [
            'compliance_area_id' => $areaId,
            'requirement_code' => $this->sanitize($this->post('requirement_code')),
            'requirement_text' => $this->sanitize($this->post('requirement_text')),
            'control_objective' => $this->sanitize($this->post('control_objective'))
        ];

        $validator = new Validator($data);
        $validator->required('requirement_text');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/compliance/view/' . $areaId);
        }

        try {
            $id = $this->requirementModel->create($data);
            $this->logActivity('created', 'compliance_requirement', $id, 'Added requirement to area');
            $this->setSuccess('Compliance requirement added successfully.');
            $this->redirect('/compliance/view/' . $areaId);

        } catch (Exception $e) {
            error_log('Compliance requirement creation error: ' . $e->getMessage());
            $this->setError('Failed to add compliance requirement.');
            $this->redirect('/compliance/view/' . $areaId);
        }
    }

    /**
     * Show compliance check form
     */
    public function check($requirementId) {
        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor']);

        $requirement = $this->requirementModel->findById($requirementId);

        if (!$requirement) {
            $this->setError('Compliance requirement not found.');
            $this->redirect('/compliance');
        }

        $auditPlanModel = $this->model('AuditPlan');
        $auditPlans = $auditPlanModel->getActiveAudits();

        $this->view('compliance/check', [
            'page_title' => 'Compliance Check',
            'requirement' => $requirement,
            'auditPlans' => $auditPlans
        ]);
    }

    /**
     * Store compliance check result
     */
    public function storeCheck() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/compliance');
        }

        Auth::requireRole(['tenant_admin', 'audit_manager', 'internal_auditor']);

        $data = [
            'requirement_id' => $this->post('requirement_id'),
            'audit_plan_id' => $this->post('audit_plan_id'),
            'check_date' => $this->post('check_date', date('Y-m-d')),
            'checked_by_user_id' => Auth::userId(),
            'compliance_status' => $this->post('compliance_status'),
            'evidence_notes' => $this->sanitize($this->post('evidence_notes')),
            'evidence_file' => null // Handle file upload separately
        ];

        $validator = new Validator($data);
        $validator->required('requirement_id');
        $validator->required('compliance_status');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/compliance');
        }

        // Handle file upload if present
        if (!empty($_FILES['evidence_file']['name'])) {
            try {
                $uploader = new FileUpload('evidence_file');
                $uploader->setAllowedTypes(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);
                $uploader->setMaxSize(10); // 10MB

                if ($uploader->upload()) {
                    $data['evidence_file'] = $uploader->getUploadedFileName();
                }
            } catch (Exception $e) {
                error_log('File upload error: ' . $e->getMessage());
                $this->setError('File upload failed: ' . $e->getMessage());
                $this->redirect('/compliance/check/' . $data['requirement_id']);
            }
        }

        try {
            $id = $this->checkModel->create($data);
            $this->logActivity('created', 'compliance_check', $id, 'Performed compliance check');
            $this->setSuccess('Compliance check recorded successfully.');

            // If non-compliant, create finding
            if ($data['compliance_status'] === 'non_compliant' && !empty($data['audit_plan_id'])) {
                $this->createFindingFromCheck($id, $data);
            }

            $requirement = $this->requirementModel->findById($data['requirement_id']);
            $this->redirect('/compliance/view/' . $requirement['compliance_area_id']);

        } catch (Exception $e) {
            error_log('Compliance check creation error: ' . $e->getMessage());
            $this->setError('Failed to record compliance check.');
            $this->redirect('/compliance');
        }
    }

    /**
     * Create finding from non-compliant check
     */
    private function createFindingFromCheck($checkId, $checkData) {
        try {
            $requirement = $this->requirementModel->findById($checkData['requirement_id']);

            $findingModel = $this->model('Finding');
            $findingData = [
                'audit_plan_id' => $checkData['audit_plan_id'],
                'title' => 'Non-compliance: ' . $requirement['requirement_code'],
                'description' => $requirement['requirement_text'],
                'severity' => 'high',
                'criteria' => $requirement['control_objective'],
                'status' => 'open',
                'assigned_to_user_id' => Auth::userId()
            ];

            $findingId = $findingModel->create($findingData);
            $this->logActivity('created', 'finding', $findingId, 'Created finding from compliance check');

        } catch (Exception $e) {
            error_log('Finding creation from check error: ' . $e->getMessage());
        }
    }

    /**
     * Compliance dashboard
     */
    public function dashboard() {
        $statistics = $this->checkModel->getStatistics();
        $nonCompliant = $this->checkModel->getNonCompliant();
        $areas = $this->complianceAreaModel->getAll();

        $this->view('compliance/dashboard', [
            'page_title' => 'Compliance Dashboard',
            'statistics' => $statistics,
            'nonCompliant' => $nonCompliant,
            'areas' => $areas
        ]);
    }

    /**
     * View compliance check detail
     */
    public function viewCheck($id) {
        $check = $this->checkModel->findById($id);

        if (!$check) {
            $this->setError('Compliance check not found.');
            $this->redirect('/compliance');
        }

        $checkDetails = $this->db->fetchOne(
            "SELECT cc.*,
                    cr.requirement_code,
                    cr.requirement_text,
                    ca.name as area_name,
                    u.first_name,
                    u.last_name,
                    ap.title as audit_title
             FROM compliance_checks cc
             JOIN compliance_requirements cr ON cc.requirement_id = cr.id
             JOIN compliance_areas ca ON cr.compliance_area_id = ca.id
             LEFT JOIN users u ON cc.checked_by_user_id = u.id
             LEFT JOIN audit_plans ap ON cc.audit_plan_id = ap.id
             WHERE cc.id = ? AND cc.tenant_id = ?",
            [$id, Auth::tenantId()]
        );

        $this->view('compliance/view_check', [
            'page_title' => 'Compliance Check Details',
            'check' => $checkDetails
        ]);
    }
}
