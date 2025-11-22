<?php
// FILE: /app/helpers/Email.php

class Email {
    /**
     * Send email (Simulated - logs to database and file)
     */
    public static function send($to, $subject, $body, $type = 'general') {
        $db = Database::getInstance();

        // Determine tenant_id and user_id
        $tenantId = Auth::tenantId() ?? 1;
        $userId = null;

        // Try to find user by email
        if (Auth::check()) {
            $userId = Auth::id();
        } else {
            $user = $db->fetchOne(
                'SELECT id FROM users WHERE email = ? AND tenant_id = ? LIMIT 1',
                [$to, $tenantId]
            );
            if ($user) {
                $userId = $user['id'];
            }
        }

        // Insert notification
        try {
            $db->query(
                'INSERT INTO notifications (tenant_id, user_id, email, subject, body, type, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
                [
                    $tenantId,
                    $userId,
                    $to,
                    $subject,
                    $body,
                    $type,
                    'sent'
                ]
            );

            // Log to file
            self::logEmail($to, $subject, $body);

            return true;
        } catch (Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log email to file
     */
    private static function logEmail($to, $subject, $body) {
        $logFile = LOGS_PATH . '/emails.log';
        $logEntry = sprintf(
            "[%s] TO: %s | SUBJECT: %s | BODY: %s\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            strip_tags($body)
        );

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Send audit deadline notification
     */
    public static function sendAuditDeadlineNotification($auditPlan, $user) {
        $subject = 'Upcoming Audit Deadline: ' . $auditPlan['title'];
        $body = sprintf(
            "Hello %s,\n\nThis is a reminder that the audit plan '%s' is due on %s.\n\nPlease ensure all tasks are completed on time.\n\nBest regards,\nSplashAudit Team",
            $user['first_name'],
            $auditPlan['title'],
            $auditPlan['planned_end']
        );

        return self::send($user['email'], $subject, $body, 'audit_deadline');
    }

    /**
     * Send corrective action overdue notification
     */
    public static function sendCorrectiveActionOverdueNotification($correctiveAction, $user) {
        $subject = 'Overdue Corrective Action';
        $body = sprintf(
            "Hello %s,\n\nThe corrective action '%s' is now overdue.\n\nDue Date: %s\n\nPlease complete it as soon as possible.\n\nBest regards,\nSplashAudit Team",
            $user['first_name'],
            $correctiveAction['action_description'],
            $correctiveAction['due_date']
        );

        return self::send($user['email'], $subject, $body, 'corrective_action_overdue');
    }

    /**
     * Send finding assigned notification
     */
    public static function sendFindingAssignedNotification($finding, $user) {
        $subject = 'New Finding Assigned to You';
        $body = sprintf(
            "Hello %s,\n\nA new finding has been assigned to you.\n\nTitle: %s\nSeverity: %s\n\nPlease review it in your dashboard.\n\nBest regards,\nSplashAudit Team",
            $user['first_name'],
            $finding['title'],
            ucfirst($finding['severity'])
        );

        return self::send($user['email'], $subject, $body, 'finding_assigned');
    }

    /**
     * Send evidence requested notification
     */
    public static function sendEvidenceRequestedNotification($module, $user, $details) {
        $subject = 'Evidence Requested';
        $body = sprintf(
            "Hello %s,\n\nEvidence has been requested for: %s\n\nDetails: %s\n\nPlease upload the required evidence.\n\nBest regards,\nSplashAudit Team",
            $user['first_name'],
            $module,
            $details
        );

        return self::send($user['email'], $subject, $body, 'evidence_requested');
    }

    /**
     * Send welcome email to new user
     */
    public static function sendWelcomeEmail($user, $password = null) {
        $subject = 'Welcome to ' . APP_NAME;
        $body = sprintf(
            "Hello %s,\n\nWelcome to %s!\n\nYour account has been created successfully.\n\nEmail: %s\n%s\nYou can login at: %s\n\nBest regards,\n%s Team",
            $user['first_name'],
            APP_NAME,
            $user['email'],
            $password ? "Temporary Password: $password\n" : '',
            APP_URL,
            APP_NAME
        );

        return self::send($user['email'], $subject, $body, 'welcome');
    }

    /**
     * Send password reset email
     */
    public static function sendPasswordResetEmail($user, $resetToken) {
        $subject = 'Password Reset Request';
        $resetUrl = APP_URL . '/auth/reset-password?token=' . $resetToken;
        $body = sprintf(
            "Hello %s,\n\nWe received a request to reset your password.\n\nClick the link below to reset your password:\n%s\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\n%s Team",
            $user['first_name'],
            $resetUrl,
            APP_NAME
        );

        return self::send($user['email'], $subject, $body, 'password_reset');
    }
}
