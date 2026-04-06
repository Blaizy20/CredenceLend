-- CredenceLend manual backup
-- Generated at 2026-04-01 21:42:47

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

--
-- Table structure for activity_logs
--
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_role` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `reference_no` varchar(40) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_activity_created_at` (`created_at`),
  KEY `idx_activity_user_id` (`user_id`),
  KEY `idx_activity_action` (`action`),
  KEY `idx_activity_loan_id` (`loan_id`),
  KEY `fk_activity_tenant` (`tenant_id`),
  KEY `fk_activity_customer` (`customer_id`),
  CONSTRAINT `fk_activity_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_activity_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_activity_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for activity_logs
--
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('1', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 03:21:18');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('2', NULL, '1', 'SUPER_ADMIN', 'STAFF_CREATED', 'Admin owner account created: Admin owner 1 (ADMIN)', NULL, NULL, NULL, '2026-04-02 03:22:42');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('3', '1', '1', 'SUPER_ADMIN', 'TENANT_CREATED', 'Tenant created: tenant 1 (tenant1). Status: Pending Approval. Plan: Basic.', NULL, NULL, NULL, '2026-04-02 03:25:52');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('4', NULL, '1', 'SUPER_ADMIN', 'STAFF_CREATED', 'Admin owner account created: Admin owner 2 (ADMIN)', NULL, NULL, NULL, '2026-04-02 03:26:21');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('5', '2', '1', 'SUPER_ADMIN', 'TENANT_CREATED', 'Tenant created: tenant_2 (tenant2). Status: Pending Approval. Plan: Basic.', NULL, NULL, NULL, '2026-04-02 03:26:54');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('6', '1', '1', 'SUPER_ADMIN', 'TENANT_APPROVED', 'Tenant workflow updated for tenant 1 (tenant1): Pending Approval -> Active. Plan: Basic.', NULL, NULL, NULL, '2026-04-02 03:27:20');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('7', '1', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant 1 (tenant1). Previous owner: Admin owner 1. New owner: Admin owner 1.', NULL, NULL, NULL, '2026-04-02 03:27:23');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('8', '2', '1', 'SUPER_ADMIN', 'TENANT_APPROVED', 'Tenant workflow updated for tenant_2 (tenant2): Pending Approval -> Active. Plan: Basic.', NULL, NULL, NULL, '2026-04-02 03:27:38');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('9', '2', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_2 (tenant2). Previous owner: Admin owner 2. New owner: Admin owner 2.', NULL, NULL, NULL, '2026-04-02 03:27:40');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('10', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 03:28:16');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('11', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: Manager tenant 1 (MANAGER)', NULL, NULL, NULL, '2026-04-02 03:30:08');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('12', '1', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 03:33:48');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('13', '1', '4', 'MANAGER', 'USER_LOGIN', 'Staff user logged in: Manager tenant 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 03:34:34');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('14', '2', '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 03:38:36');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('15', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 03:38:44');

--
-- Table structure for api_auth_tokens
--
DROP TABLE IF EXISTS `api_auth_tokens`;
CREATE TABLE `api_auth_tokens` (
  `token_id` int(11) NOT NULL AUTO_INCREMENT,
  `token_hash` char(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`token_id`),
  UNIQUE KEY `unique_token_hash` (`token_hash`),
  KEY `idx_api_tokens_user` (`user_id`),
  KEY `idx_api_tokens_expiry` (`expires_at`),
  CONSTRAINT `fk_api_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for backup_logs
--
DROP TABLE IF EXISTS `backup_logs`;
CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `status` enum('RUNNING','SUCCESS','FAILED') NOT NULL DEFAULT 'RUNNING',
  `details` text DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_backup_logs_status` (`status`),
  KEY `idx_backup_logs_created_at` (`created_at`),
  KEY `idx_backup_logs_requested_by` (`requested_by`),
  CONSTRAINT `fk_backup_logs_user` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for backup_logs
--
INSERT INTO `backup_logs` (`id`, `filename`, `status`, `details`, `file_size`, `requested_by`, `created_at`, `completed_at`) VALUES ('1', 'backup_20260401_214247.sql', 'RUNNING', NULL, NULL, '1', '2026-04-02 03:42:47', NULL);

--
-- Table structure for customers
--
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_no` varchar(30) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `contact_no` varchar(30) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `province` varchar(80) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `barangay` varchar(80) DEFAULT NULL,
  `street` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `unique_tenant_customer_no` (`tenant_id`,`customer_no`),
  KEY `fk_customers_user` (`user_id`),
  CONSTRAINT `fk_customers_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_customers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for interest_rate_history
--
DROP TABLE IF EXISTS `interest_rate_history`;
CREATE TABLE `interest_rate_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `old_rate` decimal(5,2) NOT NULL,
  `new_rate` decimal(5,2) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `idx_irh_loan_id` (`loan_id`),
  KEY `fk_irh_tenant` (`tenant_id`),
  KEY `fk_irh_user` (`changed_by`),
  CONSTRAINT `fk_irh_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_irh_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_irh_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for loans
--
DROP TABLE IF EXISTS `loans`;
CREATE TABLE `loans` (
  `loan_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `reference_no` varchar(40) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `principal_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `interest_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `payment_term` varchar(20) DEFAULT NULL,
  `term_months` int(11) NOT NULL DEFAULT 0,
  `total_payable` decimal(12,2) DEFAULT NULL,
  `remaining_balance` decimal(12,2) DEFAULT NULL,
  `status` enum('PENDING','CI_REVIEWED','APPROVED','DENIED','ACTIVE','OVERDUE','CLOSED') NOT NULL DEFAULT 'PENDING',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ci_by` int(11) DEFAULT NULL,
  `ci_at` timestamp NULL DEFAULT NULL,
  `manager_by` int(11) DEFAULT NULL,
  `manager_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `loan_officer_id` int(11) DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `denial_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`loan_id`),
  UNIQUE KEY `unique_tenant_reference_no` (`tenant_id`,`reference_no`),
  KEY `idx_loans_status` (`status`),
  KEY `idx_loans_customer_id` (`customer_id`),
  KEY `fk_loans_ci` (`ci_by`),
  KEY `fk_loans_manager` (`manager_by`),
  KEY `fk_loans_officer` (`loan_officer_id`),
  CONSTRAINT `fk_loans_ci` FOREIGN KEY (`ci_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_loans_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_loans_manager` FOREIGN KEY (`manager_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_loans_officer` FOREIGN KEY (`loan_officer_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_loans_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for money_release_vouchers
--
DROP TABLE IF EXISTS `money_release_vouchers`;
CREATE TABLE `money_release_vouchers` (
  `voucher_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `voucher_no` varchar(40) NOT NULL,
  `release_date` date NOT NULL,
  `check_no` varchar(40) DEFAULT NULL,
  `check_amount` decimal(12,2) DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `prepared_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `audited_by` int(11) DEFAULT NULL,
  `received_by_name` varchar(120) DEFAULT NULL,
  `received_by_date` date DEFAULT NULL,
  `voucher_data` longtext DEFAULT NULL,
  `status` enum('DRAFT','RELEASED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`voucher_id`),
  UNIQUE KEY `unique_tenant_voucher_no` (`tenant_id`,`voucher_no`),
  KEY `idx_voucher_loan_id` (`loan_id`),
  KEY `idx_voucher_no` (`voucher_no`),
  KEY `idx_voucher_status` (`status`),
  KEY `fk_voucher_prepared` (`prepared_by`),
  KEY `fk_voucher_approved` (`approved_by`),
  KEY `fk_voucher_audited` (`audited_by`),
  CONSTRAINT `fk_voucher_approved` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_voucher_audited` FOREIGN KEY (`audited_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_voucher_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_voucher_prepared` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_voucher_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for payment_edit_otp
--
DROP TABLE IF EXISTS `payment_edit_otp`;
CREATE TABLE `payment_edit_otp` (
  `otp_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(4) DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`otp_id`),
  KEY `idx_payment_user` (`payment_id`,`user_id`),
  KEY `idx_payment_otp_expires` (`expires_at`),
  KEY `fk_user_otp` (`user_id`),
  CONSTRAINT `fk_payment_otp` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_otp` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for payments
--
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `method` enum('CASH','CHEQUE','GCASH','DIGITAL','OTHER','BANK') NOT NULL DEFAULT 'CASH',
  `cheque_number` varchar(50) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `bank_name` varchar(120) DEFAULT NULL,
  `account_holder` varchar(120) DEFAULT NULL,
  `bank_reference_no` varchar(50) DEFAULT NULL,
  `gcash_reference_no` varchar(50) DEFAULT NULL,
  `or_no` varchar(40) NOT NULL,
  `loan_officer_id` int(11) DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `unique_tenant_or_no` (`tenant_id`,`or_no`),
  KEY `idx_payments_payment_date` (`payment_date`),
  KEY `idx_payments_loan_id` (`loan_id`),
  KEY `fk_pay_officer` (`loan_officer_id`),
  KEY `fk_pay_user` (`received_by`),
  CONSTRAINT `fk_pay_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_officer` FOREIGN KEY (`loan_officer_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pay_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_user` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for requirements
--
DROP TABLE IF EXISTS `requirements`;
CREATE TABLE `requirements` (
  `requirement_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `requirement_code` varchar(50) NOT NULL,
  `requirement_name` varchar(120) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `uploaded_by_role` enum('CUSTOMER','STAFF') NOT NULL DEFAULT 'STAFF',
  `uploaded_by_user` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`requirement_id`),
  UNIQUE KEY `unique_tenant_loan_req_code` (`tenant_id`,`loan_id`,`requirement_code`),
  KEY `idx_requirements_loan_id` (`loan_id`),
  KEY `fk_requirements_user` (`uploaded_by_user`),
  CONSTRAINT `fk_requirements_loan` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`loan_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_requirements_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_requirements_user` FOREIGN KEY (`uploaded_by_user`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Table structure for system_settings
--
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `system_name` varchar(255) NOT NULL DEFAULT 'CredenceLend',
  `logo_path` varchar(500) DEFAULT NULL,
  `primary_color` varchar(7) NOT NULL DEFAULT '#2c3ec5',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `unique_tenant_setting` (`tenant_id`),
  CONSTRAINT `fk_settings_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for system_settings
--
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('1', '1', 'tenant 1', NULL, '#2c3ec5', '2026-04-02 03:25:52', '2026-04-02 03:25:52');
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('2', '2', 'tenant_2', NULL, '#2c3ec5', '2026-04-02 03:26:54', '2026-04-02 03:26:54');

--
-- Table structure for tenant_admins
--
DROP TABLE IF EXISTS `tenant_admins`;
CREATE TABLE `tenant_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_primary_owner` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_owner` (`tenant_id`),
  KEY `idx_tenant_admin_user` (`user_id`),
  KEY `idx_tenant_admin_primary` (`tenant_id`,`is_primary_owner`),
  CONSTRAINT `fk_tenant_admins_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tenant_admins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for tenant_admins
--
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('3', '1', '2', '1', '2026-04-02 03:27:23');
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('4', '2', '3', '1', '2026-04-02 03:27:40');

--
-- Table structure for tenants
--
DROP TABLE IF EXISTS `tenants`;
CREATE TABLE `tenants` (
  `tenant_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_name` varchar(120) NOT NULL,
  `subdomain` varchar(50) NOT NULL,
  `display_name` varchar(120) NOT NULL,
  `tenant_status` enum('PENDING','ACTIVE','INACTIVE','SUSPENDED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `plan_code` enum('BASIC','PROFESSIONAL','ENTERPRISE') NOT NULL DEFAULT 'BASIC',
  `logo_path` varchar(500) DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT '#2c3ec5',
  `is_active` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`tenant_id`),
  UNIQUE KEY `tenant_name` (`tenant_name`),
  UNIQUE KEY `subdomain` (`subdomain`),
  KEY `idx_tenants_status` (`tenant_status`),
  KEY `idx_tenants_plan` (`plan_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for tenants
--
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'tenant 1', 'tenant1', 'tenant 1', 'ACTIVE', 'BASIC', NULL, '#2c3ec5', '1', '2026-04-02 03:25:52', '2026-04-02 03:27:20');
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'tenant_2', 'tenant2', 'tenant_2', 'ACTIVE', 'BASIC', NULL, '#2c3ec5', '1', '2026-04-02 03:26:54', '2026-04-02 03:27:38');

--
-- Table structure for users
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `full_name` varchar(120) NOT NULL,
  `role` enum('SUPER_ADMIN','ADMIN','TENANT','MANAGER','CREDIT_INVESTIGATOR','LOAN_OFFICER','CASHIER','CUSTOMER') NOT NULL,
  `contact_no` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `unique_username` (`username`),
  UNIQUE KEY `unique_tenant_username` (`tenant_id`,`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_tenant_role` (`tenant_id`,`role`),
  CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for users
--
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('1', NULL, 'admin', '$2y$10$yMa4cQxzEvHF2jzoR31gsuMRA8fmecaIt9jVi62gaGbYQNLphVH5i', NULL, NULL, 'System Super Admin', 'SUPER_ADMIN', NULL, 'superadmin@localhost', '2026-04-02 03:20:37', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('2', NULL, 'admin1', '$2y$10$v6YuHnoK7NJ10f.GYNJ.qeErHzn.m4n97d1awkTDbYtxKvdDeuBXG', NULL, NULL, 'Admin owner 1', 'ADMIN', NULL, 'admin1@gmail.com', '2026-04-02 03:22:42', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('3', NULL, 'admin2', '$2y$10$qDyfTRbrV1s3WjH4qPhKzeWEBBbvsgFVjFEAVkAtge0uz5pY4bm66', NULL, NULL, 'Admin owner 2', 'ADMIN', NULL, 'admin2@gmail.com', '2026-04-02 03:26:21', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('4', '1', 'manager1', '$2y$10$/71dWOCHb2e3SnwlTU0WG.NyiiRnVDtlUGlTRNhCxSb4tOw67gP8G', NULL, NULL, 'Manager tenant 1', 'MANAGER', NULL, 'manager1@gmail.com', '2026-04-02 03:30:08', '1');

SET FOREIGN_KEY_CHECKS=1;
