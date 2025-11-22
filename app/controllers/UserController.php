<?php
// FILE: /app/controllers/UserController.php

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        Auth::requireAuth();
        Auth::requireRole(['platform_admin', 'tenant_admin']);
        $this->userModel = $this->model('User');
    }

    /**
     * List users
     */
    public function index() {
        $users = $this->userModel->findAll([], 'created_at DESC');

        $this->view('user/index', [
            'page_title' => 'Users',
            'users' => $users
        ]);
    }

    /**
     * Show create user form
     */
    public function create() {
        $this->view('user/create', [
            'page_title' => 'Create User'
        ]);
    }

    /**
     * Store new user
     */
    public function store() {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/user');
        }

        // Check quota
        $quotaChecker = new QuotaChecker();
        if (!$quotaChecker->canCreateUser()) {
            $this->setError('User limit reached for your subscription plan.');
            $this->redirect('/user');
        }

        $data = [
            'first_name' => $this->sanitize($this->post('first_name')),
            'last_name' => $this->sanitize($this->post('last_name')),
            'email' => $this->sanitize($this->post('email')),
            'role' => $this->sanitize($this->post('role')),
            'password' => $this->post('password')
        ];

        // Validate
        $validator = new Validator($data);
        $validator->required('first_name');
        $validator->required('last_name');
        $validator->required('email');
        $validator->email('email');
        $validator->required('role');
        $validator->required('password');
        $validator->minLength('password', 8);
        $validator->unique('email', 'users');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/user/create');
        }

        try {
            $userId = $this->userModel->createUser($data);

            $this->logActivity('created', 'user', $userId, 'User created: ' . $data['email']);

            // Send welcome email
            $user = $this->userModel->findById($userId);
            Email::sendWelcomeEmail($user, $data['password']);

            $this->setSuccess('User created successfully.');
            $this->redirect('/user');

        } catch (Exception $e) {
            error_log('User creation error: ' . $e->getMessage());
            $this->setError('Failed to create user.');
            $this->redirect('/user/create');
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id) {
        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->setError('User not found.');
            $this->redirect('/user');
        }

        $this->view('user/edit', [
            'page_title' => 'Edit User',
            'user' => $user
        ]);
    }

    /**
     * Update user
     */
    public function update($id) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/user');
        }

        $user = $this->userModel->findById($id);

        if (!$user) {
            $this->setError('User not found.');
            $this->redirect('/user');
        }

        $data = [
            'first_name' => $this->sanitize($this->post('first_name')),
            'last_name' => $this->sanitize($this->post('last_name')),
            'email' => $this->sanitize($this->post('email')),
            'role' => $this->sanitize($this->post('role')),
            'status' => $this->sanitize($this->post('status'))
        ];

        // Validate
        $validator = new Validator($data);
        $validator->required('first_name');
        $validator->required('last_name');
        $validator->required('email');
        $validator->email('email');

        if ($validator->fails()) {
            $this->setError($validator->getAllErrors()[0]);
            $this->redirect('/user/edit/' . $id);
        }

        try {
            $this->userModel->update($id, $data);
            $this->logActivity('updated', 'user', $id, 'User updated: ' . $data['email']);
            $this->setSuccess('User updated successfully.');
            $this->redirect('/user');

        } catch (Exception $e) {
            error_log('User update error: ' . $e->getMessage());
            $this->setError('Failed to update user.');
            $this->redirect('/user/edit/' . $id);
        }
    }

    /**
     * Delete user
     */
    public function delete($id) {
        if (!$this->isPost() || !$this->validateCsrf()) {
            $this->redirect('/user');
        }

        try {
            $user = $this->userModel->findById($id);

            if ($user) {
                $this->userModel->delete($id);
                $this->logActivity('deleted', 'user', $id, 'User deleted: ' . $user['email']);
                $this->setSuccess('User deleted successfully.');
            }

        } catch (Exception $e) {
            error_log('User deletion error: ' . $e->getMessage());
            $this->setError('Failed to delete user.');
        }

        $this->redirect('/user');
    }
}
