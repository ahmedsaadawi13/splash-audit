<?php
/**
 * Email Notification System
 * Handles all email notifications in the system
 */

class EmailNotification {
    private $email;

    public function __construct() {
        $this->email = new Email();
    }

    /**
     * Send finding assignment notification
     */
    public function sendFindingAssigned($finding, $user) {
        $subject = "New Finding Assigned: {$finding['title']}";

        $body = "
            <h2>New Finding Assigned</h2>
            <p>Hello {$user['first_name']},</p>
            <p>A new finding has been assigned to you:</p>
            <ul>
                <li><strong>Title:</strong> {$finding['title']}</li>
                <li><strong>Severity:</strong> {$finding['severity']}</li>
                <li><strong>Status:</strong> {$finding['status']}</li>
            </ul>
            <p><strong>Description:</strong></p>
            <p>{$finding['description']}</p>
            <p><a href=\"" . $_ENV['APP_URL'] . "/finding/view/{$finding['id']}\">View Finding</a></p>
        ";

        return $this->email->send($user['email'], $subject, $body);
    }

    /**
     * Send corrective action due reminder
     */
    public function sendCorrectiveActionReminder($action, $user) {
        $dueDate = date('M d, Y', strtotime($action['due_date']));
        $daysRemaining = ceil((strtotime($action['due_date']) - time()) / 86400);

        $subject = "Corrective Action Due: {$action['action_description']}";

        $body = "
            <h2>Corrective Action Reminder</h2>
            <p>Hello {$user['first_name']},</p>
            <p>This is a reminder that you have a corrective action due:</p>
            <ul>
                <li><strong>Action:</strong> {$action['action_description']}</li>
                <li><strong>Due Date:</strong> $dueDate</li>
                <li><strong>Days Remaining:</strong> $daysRemaining days</li>
                <li><strong>Status:</strong> {$action['status']}</li>
            </ul>
            <p><a href=\"" . $_ENV['APP_URL'] . "/corrective-action/view/{$action['id']}\">View Action</a></p>
        ";

        return $this->email->send($user['email'], $subject, $body);
    }

    /**
     * Send audit deadline alert
     */
    public function sendAuditDeadlineAlert($auditPlan, $users) {
        $deadline = date('M d, Y', strtotime($auditPlan['planned_end']));
        $subject = "Audit Deadline Approaching: {$auditPlan['title']}";

        $body = "
            <h2>Audit Deadline Alert</h2>
            <p>The following audit is approaching its deadline:</p>
            <ul>
                <li><strong>Audit:</strong> {$auditPlan['title']}</li>
                <li><strong>Deadline:</strong> $deadline</li>
                <li><strong>Status:</strong> {$auditPlan['status']}</li>
            </ul>
            <p><a href=\"" . $_ENV['APP_URL'] . "/audit/plan/view/{$auditPlan['id']}\">View Audit</a></p>
        ";

        foreach ($users as $user) {
            $this->email->send($user['email'], $subject, $body);
        }

        return true;
    }

    /**
     * Send weekly summary report
     */
    public function sendWeeklySummary($tenantId, $user) {
        $db = Database::getInstance();

        // Get statistics
        $openFindings = $db->fetchOne(
            "SELECT COUNT(*) as count FROM findings WHERE tenant_id = ? AND status = 'open'",
            [$tenantId]
        );

        $overdueActions = $db->fetchOne(
            "SELECT COUNT(*) as count FROM corrective_actions
             WHERE tenant_id = ? AND status != 'completed' AND due_date < CURDATE()",
            [$tenantId]
        );

        $activeAudits = $db->fetchOne(
            "SELECT COUNT(*) as count FROM audit_plans
             WHERE tenant_id = ? AND status = 'in_progress'",
            [$tenantId]
        );

        $subject = "Weekly Audit Summary";

        $body = "
            <h2>Weekly Audit Summary</h2>
            <p>Hello {$user['first_name']},</p>
            <p>Here's your weekly audit summary:</p>
            <h3>Key Metrics:</h3>
            <ul>
                <li><strong>Open Findings:</strong> {$openFindings['count']}</li>
                <li><strong>Overdue Actions:</strong> {$overdueActions['count']}</li>
                <li><strong>Active Audits:</strong> {$activeAudits['count']}</li>
            </ul>
            <p><a href=\"" . $_ENV['APP_URL'] . "/dashboard\">View Dashboard</a></p>
        ";

        return $this->email->send($user['email'], $subject, $body);
    }

    /**
     * Send compliance issue notification
     */
    public function sendComplianceIssue($check, $users) {
        $subject = "Compliance Issue Identified";

        $body = "
            <h2>Compliance Issue Alert</h2>
            <p>A compliance issue has been identified:</p>
            <ul>
                <li><strong>Status:</strong> {$check['compliance_status']}</li>
                <li><strong>Date:</strong> " . date('M d, Y', strtotime($check['check_date'])) . "</li>
            </ul>
            <p><a href=\"" . $_ENV['APP_URL'] . "/compliance/view-check/{$check['id']}\">View Details</a></p>
        ";

        foreach ($users as $user) {
            $this->email->send($user['email'], $subject, $body);
        }

        return true;
    }

    /**
     * Send user account created notification
     */
    public function sendAccountCreated($user, $tempPassword) {
        $subject = "Welcome to SplashAudit";

        $body = "
            <h2>Welcome to SplashAudit!</h2>
            <p>Hello {$user['first_name']},</p>
            <p>Your account has been created successfully.</p>
            <h3>Login Credentials:</h3>
            <ul>
                <li><strong>Email:</strong> {$user['email']}</li>
                <li><strong>Temporary Password:</strong> $tempPassword</li>
                <li><strong>Role:</strong> {$user['role']}</li>
            </ul>
            <p><strong>Important:</strong> Please change your password after first login.</p>
            <p><a href=\"" . $_ENV['APP_URL'] . "/login\">Login Now</a></p>
        ";

        return $this->email->send($user['email'], $subject, $body);
    }
}
