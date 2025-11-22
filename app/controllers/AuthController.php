<?php
// FILE: /app/controllers/AuthController.php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    /**
     * Show login form
     */
    public function login() {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login');
    }

    /**
     * Process login
     */
    public function authenticate() {
        if (!$this->isPost()) {
            $this->redirect('/auth/login');
        }

        if (!$this->validateCsrf()) {
            $this->redirect('/auth/login');
        }

        $email = $this->sanitize($this->post('email'));
        $password = $this->post('password');

        // Validate input
        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->required('email');
        $validator->email('email');
        $validator->required('password');

        if ($validator->fails()) {
            Session::setFlash('error', 'Please provide valid credentials.');
            $this->redirect('/auth/login');
        }

        // Find user
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            Session::setFlash('error', 'Invalid email or password.');
            $this->redirect('/auth/login');
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            Session::setFlash('error', 'Your account has been suspended.');
            $this->redirect('/auth/login');
        }

        // Verify password
        if (!Auth::verifyPassword($password, $user['password'])) {
            Session::setFlash('error', 'Invalid email or password.');
            $this->redirect('/auth/login');
        }

        // Check if tenant is active
        $tenantModel = $this->model('Tenant');
        if (!$tenantModel->isActive($user['tenant_id'])) {
            Session::setFlash('error', 'Your organization account has been suspended.');
            $this->redirect('/auth/login');
        }

        // Login user
        Auth::login($user);

        $this->logActivity('login', 'auth', $user['id'], 'User logged in');

        Session::setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
        $this->redirect('/dashboard');
    }

    /**
     * Logout
     */
    public function logout() {
        $this->logActivity('logout', 'auth', Auth::id(), 'User logged out');
        Auth::logout();
        Session::setFlash('success', 'You have been logged out successfully.');
        $this->redirect('/auth/login');
    }

    /**
     * Show registration form
     */
    public function register() {
        $this->view('auth/register');
    }

    /**
     * Process registration
     */
    public function processRegister() {
        if (!$this->isPost()) {
            $this->redirect('/auth/register');
        }

        if (!$this->validateCsrf()) {
            $this->redirect('/auth/register');
        }

        $data = [
            'company_name' => $this->sanitize($this->post('company_name')),
            'subdomain' => $this->sanitize($this->post('subdomain')),
            'first_name' => $this->sanitize($this->post('first_name')),
            'last_name' => $this->sanitize($this->post('last_name')),
            'email' => $this->sanitize($this->post('email')),
            'password' => $this->post('password'),
            'password_confirm' => $this->post('password_confirm')
        ];

        // Validate input
        $validator = new Validator($data);
        $validator->required('company_name');
        $validator->required('subdomain');
        $validator->required('first_name');
        $validator->required('last_name');
        $validator->required('email');
        $validator->email('email');
        $validator->required('password');
        $validator->minLength('password', 8);
        $validator->matches('password_confirm', 'password', 'Passwords do not match');

        // Check if subdomain is unique
        $tenantModel = $this->model('Tenant');
        if ($tenantModel->findBySubdomain($data['subdomain'])) {
            $validator->errors['subdomain'][] = 'Subdomain already taken.';
        }

        if ($validator->fails()) {
            Session::setFlash('error', $validator->getAllErrors()[0]);
            Session::setFlash('old_input', $data);
            $this->redirect('/auth/register');
        }

        try {
            $this->db->beginTransaction();

            // Get default subscription plan
            $planModel = $this->db->fetchOne(
                "SELECT * FROM subscription_plans WHERE plan_code = ?",
                [DEFAULT_SUBSCRIPTION_PLAN]
            );

            // Create tenant
            $tenantId = $tenantModel->create([
                'company_name' => $data['company_name'],
                'subdomain' => strtolower($data['subdomain']),
                'status' => 'active',
                'subscription_plan_id' => $planModel['id'],
                'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
            ]);

            // Create subscription
            $this->db->query(
                "INSERT INTO tenant_subscriptions (tenant_id, subscription_plan_id, status, current_period_start, current_period_end)
                 VALUES (?, ?, 'trialing', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))",
                [$tenantId, $planModel['id']]
            );

            // Create user
            $userId = $this->userModel->create([
                'tenant_id' => $tenantId,
                'email' => $data['email'],
                'password' => Auth::hashPassword($data['password']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => 'tenant_admin',
                'status' => 'active'
            ]);

            // Generate API key
            $apiKey = bin2hex(random_bytes(32));
            $this->db->query(
                "INSERT INTO api_keys (tenant_id, api_key, name, is_active) VALUES (?, ?, ?, 1)",
                [$tenantId, $apiKey, 'Default API Key']
            );

            $this->db->commit();

            // Send welcome email
            $user = $this->userModel->findById($userId);
            Email::sendWelcomeEmail($user);

            Session::setFlash('success', 'Registration successful! Please login.');
            $this->redirect('/auth/login');

        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Registration error: ' . $e->getMessage());
            Session::setFlash('error', 'Registration failed. Please try again.');
            $this->redirect('/auth/register');
        }
    }
}
