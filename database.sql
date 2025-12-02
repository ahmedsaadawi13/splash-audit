-- FILE: /database.sql
-- SplashAudit - Multi-Tenant Internal Audit SaaS System
-- Database Schema for MySQL 5.7+ / InnoDB
-- All timestamps stored in UTC

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- PLATFORM TABLES (No tenant_id)
-- ============================================

-- Tenants (Companies)
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(255) NOT NULL,
  `subdomain` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('active', 'suspended', 'canceled') DEFAULT 'active',
  `subscription_plan_id` INT UNSIGNED DEFAULT NULL,
  `trial_ends_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_subdomain` (`subdomain`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subscription Plans
CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_name` VARCHAR(100) NOT NULL,
  `plan_code` VARCHAR(50) NOT NULL UNIQUE,
  `price_monthly` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `price_yearly` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `max_audit_plans` INT NOT NULL DEFAULT 5,
  `max_checklists` INT NOT NULL DEFAULT 10,
  `max_users` INT NOT NULL DEFAULT 5,
  `max_storage_mb` INT NOT NULL DEFAULT 1024,
  `max_corrective_actions` INT NOT NULL DEFAULT 50,
  `features` JSON DEFAULT NULL COMMENT 'custom_fields, advanced_reports, api_access',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_plan_code` (`plan_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default subscription plans
INSERT INTO `subscription_plans` (`plan_name`, `plan_code`, `price_monthly`, `price_yearly`, `max_audit_plans`, `max_checklists`, `max_users`, `max_storage_mb`, `max_corrective_actions`, `features`) VALUES
('Basic', 'basic', 29.00, 290.00, 5, 10, 5, 1024, 50, '{"custom_fields": false, "advanced_reports": false, "api_access": false}'),
('Professional', 'professional', 99.00, 990.00, 20, 50, 20, 5120, 200, '{"custom_fields": true, "advanced_reports": true, "api_access": false}'),
('Enterprise', 'enterprise', 299.00, 2990.00, -1, -1, -1, 51200, -1, '{"custom_fields": true, "advanced_reports": true, "api_access": true}');

-- Tenant Subscriptions
CREATE TABLE IF NOT EXISTS `tenant_subscriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `subscription_plan_id` INT UNSIGNED NOT NULL,
  `status` ENUM('trialing', 'active', 'past_due', 'canceled') DEFAULT 'trialing',
  `current_period_start` DATETIME DEFAULT NULL,
  `current_period_end` DATETIME DEFAULT NULL,
  `canceled_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `subscription_plan_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
  `due_date` DATE NOT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_invoice_number` (`invoice_number`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `invoice_id` INT UNSIGNED DEFAULT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'dummy_gateway',
  `transaction_id` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) DEFAULT 'USD',
  `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
  `metadata` JSON DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_invoice` (`invoice_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MULTI-TENANT TABLES (All include tenant_id)
-- ============================================

-- Users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'audit_manager', 'internal_auditor', 'process_owner', 'reviewer', 'viewer') NOT NULL DEFAULT 'viewer',
  `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tenant_email` (`tenant_id`, `email`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_role` (`role`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Keys
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `api_key` VARCHAR(64) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_api_key` (`api_key`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Universe (Auditable Entities)
CREATE TABLE IF NOT EXISTS `audit_universe` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) DEFAULT NULL COMMENT 'process, department, branch, system',
  `owner_user_id` INT UNSIGNED DEFAULT NULL,
  `risk_score` DECIMAL(5,2) DEFAULT 0.00,
  `last_audit_date` DATE DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_owner` (`owner_user_id`),
  KEY `idx_category` (`category`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Programs
CREATE TABLE IF NOT EXISTS `audit_programs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `year` INT NOT NULL,
  `scope` TEXT DEFAULT NULL,
  `objectives` TEXT DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `status` ENUM('draft', 'approved', 'archived') DEFAULT 'draft',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_year` (`year`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Program Universe Items (Link programs to universe)
CREATE TABLE IF NOT EXISTS `program_universe_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `program_id` INT UNSIGNED NOT NULL,
  `universe_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_program` (`program_id`),
  KEY `idx_universe` (`universe_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`program_id`) REFERENCES `audit_programs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`universe_id`) REFERENCES `audit_universe`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Risk Register
CREATE TABLE IF NOT EXISTS `risks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL COMMENT 'operational, financial, compliance, strategic',
  `owner_user_id` INT UNSIGNED DEFAULT NULL,
  `likelihood` TINYINT NOT NULL DEFAULT 1 COMMENT '1-5 scale',
  `impact` TINYINT NOT NULL DEFAULT 1 COMMENT '1-5 scale',
  `inherent_risk` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Auto-calculated: likelihood * impact',
  `control_effectiveness` TINYINT DEFAULT 0 COMMENT '0-5 scale',
  `residual_risk` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Auto-calculated: inherent_risk - control_effectiveness',
  `controls_description` TEXT DEFAULT NULL,
  `status` ENUM('open', 'mitigated', 'accepted', 'transferred') DEFAULT 'open',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_owner` (`owner_user_id`),
  KEY `idx_category` (`category`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Plans
CREATE TABLE IF NOT EXISTS `audit_plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `program_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `audit_type` ENUM('internal', 'compliance', 'surprise', 'operational', 'financial') DEFAULT 'internal',
  `planned_start` DATE DEFAULT NULL,
  `planned_end` DATE DEFAULT NULL,
  `actual_start` DATE DEFAULT NULL,
  `actual_end` DATE DEFAULT NULL,
  `lead_auditor_id` INT UNSIGNED DEFAULT NULL,
  `team_members` JSON DEFAULT NULL COMMENT 'Array of user IDs',
  `scope` TEXT DEFAULT NULL,
  `objectives` TEXT DEFAULT NULL,
  `methodology` TEXT DEFAULT NULL,
  `status` ENUM('scheduled', 'in_progress', 'completed', 'canceled') DEFAULT 'scheduled',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_program` (`program_id`),
  KEY `idx_lead_auditor` (`lead_auditor_id`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`program_id`) REFERENCES `audit_programs`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`lead_auditor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Checklists
CREATE TABLE IF NOT EXISTS `audit_checklists` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `checklist_type` VARCHAR(100) DEFAULT NULL COMMENT 'financial, operational, compliance, IT',
  `status` ENUM('active', 'inactive', 'archived') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_type` (`checklist_type`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Checklist Items
CREATE TABLE IF NOT EXISTS `checklist_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `checklist_id` INT UNSIGNED NOT NULL,
  `item_text` TEXT NOT NULL,
  `procedure` TEXT DEFAULT NULL,
  `risk_reference` VARCHAR(255) DEFAULT NULL,
  `requirement_reference` VARCHAR(255) DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_checklist` (`checklist_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`checklist_id`) REFERENCES `audit_checklists`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit Work Items (Checklist execution)
CREATE TABLE IF NOT EXISTS `audit_work_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `audit_plan_id` INT UNSIGNED NOT NULL,
  `checklist_item_id` INT UNSIGNED NOT NULL,
  `auditor_id` INT UNSIGNED DEFAULT NULL,
  `result` ENUM('pass', 'fail', 'not_applicable', 'pending') DEFAULT 'pending',
  `evidence_notes` TEXT DEFAULT NULL,
  `evidence_file` VARCHAR(255) DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_audit_plan` (`audit_plan_id`),
  KEY `idx_checklist_item` (`checklist_item_id`),
  KEY `idx_auditor` (`auditor_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`audit_plan_id`) REFERENCES `audit_plans`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`checklist_item_id`) REFERENCES `checklist_items`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`auditor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Findings
CREATE TABLE IF NOT EXISTS `findings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `audit_plan_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `severity` ENUM('high', 'medium', 'low') DEFAULT 'medium',
  `cause` TEXT DEFAULT NULL,
  `effect` TEXT DEFAULT NULL,
  `criteria` TEXT DEFAULT NULL COMMENT 'Standard or policy violated',
  `risk_rating` DECIMAL(5,2) DEFAULT 0.00,
  `assigned_to_user_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('open', 'in_progress', 'closed', 'deferred') DEFAULT 'open',
  `closed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_audit_plan` (`audit_plan_id`),
  KEY `idx_assigned_to` (`assigned_to_user_id`),
  KEY `idx_severity` (`severity`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`audit_plan_id`) REFERENCES `audit_plans`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Corrective Actions
CREATE TABLE IF NOT EXISTS `corrective_actions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `finding_id` INT UNSIGNED NOT NULL,
  `action_description` TEXT NOT NULL,
  `due_date` DATE DEFAULT NULL,
  `responsible_user_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('open', 'pending_review', 'completed', 'overdue') DEFAULT 'open',
  `evidence_notes` TEXT DEFAULT NULL,
  `evidence_file` VARCHAR(255) DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_finding` (`finding_id`),
  KEY `idx_responsible` (`responsible_user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`finding_id`) REFERENCES `findings`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`responsible_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Corrective Action Reviews
CREATE TABLE IF NOT EXISTS `corrective_action_reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `corrective_action_id` INT UNSIGNED NOT NULL,
  `reviewer_user_id` INT UNSIGNED DEFAULT NULL,
  `review_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `comments` TEXT DEFAULT NULL,
  `status` ENUM('approved', 'rejected', 'needs_revision') DEFAULT 'approved',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_action` (`corrective_action_id`),
  KEY `idx_reviewer` (`reviewer_user_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`corrective_action_id`) REFERENCES `corrective_actions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewer_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compliance Areas
CREATE TABLE IF NOT EXISTS `compliance_areas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `area_name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `regulatory_framework` VARCHAR(255) DEFAULT NULL COMMENT 'ISO 9001, SOX, GDPR, etc',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compliance Requirements
CREATE TABLE IF NOT EXISTS `compliance_requirements` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `compliance_area_id` INT UNSIGNED NOT NULL,
  `requirement_code` VARCHAR(100) DEFAULT NULL,
  `requirement_text` TEXT NOT NULL,
  `control_objective` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_area` (`compliance_area_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`compliance_area_id`) REFERENCES `compliance_areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compliance Checks
CREATE TABLE IF NOT EXISTS `compliance_checks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `requirement_id` INT UNSIGNED NOT NULL,
  `audit_plan_id` INT UNSIGNED DEFAULT NULL,
  `check_date` DATE DEFAULT NULL,
  `checked_by_user_id` INT UNSIGNED DEFAULT NULL,
  `compliance_status` ENUM('compliant', 'non_compliant', 'partial', 'not_assessed') DEFAULT 'not_assessed',
  `evidence_notes` TEXT DEFAULT NULL,
  `evidence_file` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_requirement` (`requirement_id`),
  KEY `idx_audit_plan` (`audit_plan_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`requirement_id`) REFERENCES `compliance_requirements`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`audit_plan_id`) REFERENCES `audit_plans`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`checked_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Files (Evidence Repository)
CREATE TABLE IF NOT EXISTS `files` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `module_type` VARCHAR(50) NOT NULL COMMENT 'audit, finding, corrective_action, compliance, checklist',
  `module_id` INT UNSIGNED NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `stored_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `size_bytes` INT UNSIGNED NOT NULL,
  `uploaded_by_user_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_module` (`module_type`, `module_id`),
  KEY `idx_uploaded_by` (`uploaded_by_user_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT NULL COMMENT 'audit_deadline, corrective_action_overdue, finding_assigned',
  `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
  `sent_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`type`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usage Tracking (for quota enforcement)
CREATE TABLE IF NOT EXISTS `usage_tracking` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `metric_name` VARCHAR(100) NOT NULL COMMENT 'audit_plans_count, checklists_count, users_count, storage_mb, corrective_actions_count',
  `metric_value` INT NOT NULL DEFAULT 0,
  `recorded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_metric` (`metric_name`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs (Audit Trail)
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'created, updated, deleted, viewed',
  `module` VARCHAR(100) NOT NULL COMMENT 'audit_plan, finding, user, etc',
  `module_id` INT UNSIGNED DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INITIAL DATA
-- ============================================

-- Create Platform Tenant (ID = 1)
INSERT INTO `tenants` (`id`, `company_name`, `subdomain`, `status`, `subscription_plan_id`)
VALUES (1, 'Platform Admin', 'platform', 'active', NULL);

-- Create Platform Admin User
-- Password: Admin@123
INSERT INTO `users` (`tenant_id`, `email`, `password`, `first_name`, `last_name`, `role`, `status`)
VALUES (1, 'admin@splashaudit.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Platform', 'Admin', 'platform_admin', 'active');

-- Sample Demo Tenant
INSERT INTO `tenants` (`company_name`, `subdomain`, `status`, `subscription_plan_id`, `trial_ends_at`)
VALUES ('Demo Company', 'demo', 'active', 1, DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Sample Demo Tenant Subscription
INSERT INTO `tenant_subscriptions` (`tenant_id`, `subscription_plan_id`, `status`, `current_period_start`, `current_period_end`)
VALUES (2, 1, 'trialing', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY));

-- Demo Tenant Admin
-- Password: Demo@123
INSERT INTO `users` (`tenant_id`, `email`, `password`, `first_name`, `last_name`, `role`, `status`)
VALUES (2, 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo', 'Admin', 'tenant_admin', 'active');

-- Demo API Key for tenant 2
INSERT INTO `api_keys` (`tenant_id`, `api_key`, `name`, `is_active`)
VALUES (2, 'demo_api_key_1234567890abcdef1234567890abcdef12345678', 'Demo API Key', 1);
