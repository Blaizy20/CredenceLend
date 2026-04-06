-- CredenceLend manual backup
-- Generated at 2026-04-05 11:46:21
-- Label: Pre-restore safety backup before first test

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
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for activity_logs
--
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('1', '1', '1', 'SUPER_ADMIN', 'TENANT_CREATED', 'Tenant created: tenant_1 (tenant1). Status: Pending Approval. Plan: Basic.', NULL, NULL, NULL, '2026-04-05 13:58:42');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('2', NULL, '1', 'SUPER_ADMIN', 'STAFF_CREATED', 'Admin owner account created: admin 1 (ADMIN)', NULL, NULL, NULL, '2026-04-05 13:59:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('3', NULL, '2', 'ADMIN', 'PASSWORD_SETUP_COMPLETED', 'Initial password setup completed for admin 1.', NULL, NULL, NULL, '2026-04-05 14:00:02');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('4', NULL, '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:00:10');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('5', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 14:00:45');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('6', NULL, '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 14:01:41');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('7', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 14:01:46');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('8', '1', '1', 'SUPER_ADMIN', 'TENANT_APPROVED', 'Tenant workflow updated for tenant_1 (tenant1): Pending Approval -> Active. Plan: Basic.', NULL, NULL, NULL, '2026-04-05 14:01:57');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('9', '1', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_1 (tenant1). Previous owner: No owner assigned. New owner: admin 1.', NULL, NULL, NULL, '2026-04-05 14:02:43');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('10', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:03:10');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('11', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 14:05:00');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('12', '2', '1', 'SUPER_ADMIN', 'TENANT_CREATED', 'Tenant created: tenant_2 (tenant2). Status: Pending Approval. Plan: Basic. Subscription start: 2026-04-05 08:16:06. Subscription expiry: 2026-05-05 08:16:06.', NULL, NULL, NULL, '2026-04-05 14:16:06');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('13', '1', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:16:33');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('14', '2', '1', 'SUPER_ADMIN', 'TENANT_APPROVED', 'Tenant workflow updated for tenant_2 (tenant2): Pending Approval -> Active. Plan: Basic.', NULL, NULL, NULL, '2026-04-05 14:16:51');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('15', '2', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_2 (tenant2). Previous owner: admin 1. New owner: admin 1.', NULL, NULL, NULL, '2026-04-05 14:16:52');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('16', '2', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:17:06');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('17', '2', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:18:01');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('18', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 14:18:05');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('19', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Basic -> Professional.', NULL, NULL, NULL, '2026-04-05 14:20:00');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('20', NULL, '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 14:20:29');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('21', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:20:37');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('22', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Plan: Basic -> Professional.', NULL, NULL, NULL, '2026-04-05 14:21:14');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('23', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Professional -> Basic.', NULL, NULL, NULL, '2026-04-05 14:21:38');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('24', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Plan and status were re-saved with no value change.', NULL, NULL, NULL, '2026-04-05 14:21:40');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('25', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Plan: Professional -> Basic.', NULL, NULL, NULL, '2026-04-05 14:21:44');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('26', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Plan and status were re-saved with no value change.', NULL, NULL, NULL, '2026-04-05 14:23:43');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('27', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Status: Active -> Trial.', NULL, NULL, NULL, '2026-04-05 14:23:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('28', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Plan and status were re-saved with no value change.', NULL, NULL, NULL, '2026-04-05 14:24:20');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('29', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Status: Trial -> Past Due.', NULL, NULL, NULL, '2026-04-05 14:25:40');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('30', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Status: Past Due -> Suspended.', NULL, NULL, NULL, '2026-04-05 14:27:47');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('31', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Status: Suspended -> Cancelled.', NULL, NULL, NULL, '2026-04-05 14:28:27');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('32', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Status: Cancelled -> Expired.', NULL, NULL, NULL, '2026-04-05 14:28:52');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('33', '3', '1', 'SUPER_ADMIN', 'TENANT_CREATED', 'Tenant created: tenant_3 (tenant3). Status: Pending Approval. Plan: Basic. Subscription start: 2026-04-05 08:31:56. Subscription expiry: 2026-05-05 08:31:56.', NULL, NULL, NULL, '2026-04-05 14:31:56');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('34', '2', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:32:19');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('35', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:39:01');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('36', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Status: Active -> Trial.', NULL, NULL, NULL, '2026-04-05 14:41:28');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('37', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Status: Trial -> Past Due.', NULL, NULL, NULL, '2026-04-05 14:42:25');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('38', '2', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_2 (tenant2). Status: Expired -> Active.', NULL, NULL, NULL, '2026-04-05 14:43:34');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('39', '2', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 14:44:11');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('40', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Status: Past Due -> Expired.', NULL, NULL, NULL, '2026-04-05 14:45:40');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('41', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 15:15:34');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('42', '3', '1', 'SUPER_ADMIN', 'TENANT_APPROVED', 'Tenant workflow updated for tenant_3 (tenant3): Pending Approval -> Active. Plan: Basic.', NULL, NULL, NULL, '2026-04-05 15:15:59');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('43', '3', '1', 'SUPER_ADMIN', 'TENANT_DEACTIVATED', 'Tenant workflow updated for tenant_3 (tenant3): Active -> Inactive. Plan: Basic.', NULL, NULL, NULL, '2026-04-05 15:16:04');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('44', '3', '1', 'SUPER_ADMIN', 'TENANT_ACTIVATED', 'Tenant workflow updated for tenant_3 (tenant3): Inactive -> Active. Plan: Basic.', NULL, NULL, NULL, '2026-04-05 15:16:12');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('45', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Status: Expired -> Active.', NULL, NULL, NULL, '2026-04-05 15:16:25');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('46', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 15:16:55');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('47', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: manager 1 (MANAGER)', NULL, NULL, NULL, '2026-04-05 15:21:37');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('48', '1', '3', 'MANAGER', 'PASSWORD_SETUP_COMPLETED', 'Initial password setup completed for manager 1.', NULL, NULL, NULL, '2026-04-05 15:22:16');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('49', NULL, '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 15:22:58');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('50', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 15:23:08');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('51', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: credit 1 (CREDIT_INVESTIGATOR)', NULL, NULL, NULL, '2026-04-05 15:31:32');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('52', '1', '4', 'CREDIT_INVESTIGATOR', 'PASSWORD_SETUP_COMPLETED', 'Initial password setup completed for credit 1.', NULL, NULL, NULL, '2026-04-05 15:32:07');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('53', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: cashier 1 (CASHIER)', NULL, NULL, NULL, '2026-04-05 15:33:13');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('54', '1', NULL, 'CASHIER', 'PASSWORD_SETUP_COMPLETED', 'Initial password setup completed for cashier 1.', NULL, NULL, NULL, '2026-04-05 15:35:38');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('55', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: loan officer 1 (LOAN_OFFICER)', NULL, NULL, NULL, '2026-04-05 15:36:25');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('56', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: loan officer 2 (LOAN_OFFICER)', NULL, NULL, NULL, '2026-04-05 15:37:03');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('57', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-05 15:37:29');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('58', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Basic -> Professional.', NULL, NULL, NULL, '2026-04-05 15:37:43');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('59', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: cashier 3 (CASHIER)', NULL, NULL, NULL, '2026-04-05 15:37:50');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('60', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Status: Active -> Expired.', NULL, NULL, NULL, '2026-04-05 15:38:12');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('61', '1', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 15:38:16');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('62', '1', NULL, 'CASHIER', 'USER_LOGIN', 'Staff user logged in: cashier 1 (CASHIER).', NULL, NULL, NULL, '2026-04-05 15:38:24');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('63', '1', NULL, 'CASHIER', 'USER_LOGOUT', 'Staff user logged out: cashier 1 (CASHIER).', NULL, NULL, NULL, '2026-04-05 15:38:33');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('64', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Status: Expired -> Active.', NULL, NULL, NULL, '2026-04-05 15:38:39');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('65', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Professional -> Basic.', NULL, NULL, NULL, '2026-04-05 15:39:39');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('66', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 15:39:48');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('67', '1', '2', 'ADMIN', 'Staff Deactivated', 'Staff cashier 3 deactivated', NULL, NULL, NULL, '2026-04-05 15:40:08');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('68', '1', '2', 'ADMIN', 'STAFF_DELETED', 'Staff account deleted: cashier 3', NULL, NULL, NULL, '2026-04-05 15:43:57');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('69', '1', '2', 'ADMIN', 'STAFF_DELETED', 'Staff account deleted: cashier 1', NULL, NULL, NULL, '2026-04-05 15:45:00');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('70', '1', '2', 'ADMIN', 'STAFF_DELETED', 'Staff account deleted: loan officer 2', NULL, NULL, NULL, '2026-04-05 15:45:58');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('71', '1', '2', 'ADMIN', 'Customer Registered', 'New customer Fritz Harly Degamo registered', NULL, '1', 'CUST-2026-0001', '2026-04-05 15:56:11');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('72', '1', '2', 'ADMIN', 'STAFF_UPDATED', 'Staff account updated: loan officer 1 (CASHIER)', NULL, NULL, NULL, '2026-04-05 16:00:18');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('73', '1', '2', 'ADMIN', 'STAFF_UPDATED', 'Staff account updated: loan officer 1 (CASHIER)', NULL, NULL, NULL, '2026-04-05 16:00:29');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('74', '1', '2', 'ADMIN', 'Staff Role Changed', 'Staff loan officer 1 role changed from CASHIER to LOAN_OFFICER', NULL, NULL, NULL, '2026-04-05 16:04:47');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('75', NULL, '1', 'SUPER_ADMIN', 'STAFF_CREATED', 'Admin owner account created: admin owner 5 (ADMIN)', NULL, NULL, NULL, '2026-04-05 16:10:30');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('76', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Basic -> Enterprise.', NULL, NULL, NULL, '2026-04-05 16:14:29');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('77', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Enterprise -> Professional.', NULL, NULL, NULL, '2026-04-05 16:17:54');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('78', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Professional -> Enterprise.', NULL, NULL, NULL, '2026-04-05 16:29:48');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('79', '1', '2', 'ADMIN', 'Loan Officer Assigned', 'Loan officer assigned to loan officer 1', '2', '3', 'LN-2026-0002', '2026-04-05 16:36:27');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('80', '1', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 16:41:35');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('81', '2', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 16:43:01');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('82', '2', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 16:45:43');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('83', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: admin 1 (ADMIN).', NULL, NULL, NULL, '2026-04-05 16:51:36');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('84', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Enterprise -> Basic.', NULL, NULL, NULL, '2026-04-05 16:52:50');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('85', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Basic -> Professional.', NULL, NULL, NULL, '2026-04-05 16:53:50');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('86', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Professional -> Basic.', NULL, NULL, NULL, '2026-04-05 16:54:39');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('87', '1', '1', 'SUPER_ADMIN', 'TENANT_SUBSCRIPTION_UPDATED', 'Tenant subscription updated for tenant_1 (tenant1). Plan: Basic -> Professional.', NULL, NULL, NULL, '2026-04-05 17:00:06');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('88', '4', '1', 'SUPER_ADMIN', 'TENANT_CREATED', 'Tenant created: tenant_5 (tenant5). Status: Pending Approval. Plan: Basic. Subscription start: 2026-04-05 11:22:21. Subscription expiry: 2026-05-05 11:22:21.', NULL, NULL, NULL, '2026-04-05 17:22:21');

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
  `backup_type` enum('MANUAL','RESTORE_POINT') NOT NULL DEFAULT 'MANUAL',
  `restore_label` varchar(255) DEFAULT NULL,
  `is_restore_point` tinyint(1) NOT NULL DEFAULT 0,
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
  KEY `idx_backup_logs_type` (`backup_type`,`is_restore_point`),
  CONSTRAINT `fk_backup_logs_user` FOREIGN KEY (`requested_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for backup_logs
--
INSERT INTO `backup_logs` (`id`, `filename`, `backup_type`, `restore_label`, `is_restore_point`, `status`, `details`, `file_size`, `requested_by`, `created_at`, `completed_at`) VALUES ('1', 'restore_point_20260405_114300.sql', 'RESTORE_POINT', 'Restore point 2026-04-05 11:43:00', '1', 'SUCCESS', 'Restore point created successfully.', '77963', '1', '2026-04-05 17:43:00', '2026-04-05 17:43:00');
INSERT INTO `backup_logs` (`id`, `filename`, `backup_type`, `restore_label`, `is_restore_point`, `status`, `details`, `file_size`, `requested_by`, `created_at`, `completed_at`) VALUES ('2', 'restore_point_20260405_114514.sql', 'RESTORE_POINT', 'Restore point 2026-04-05 11:45:14', '1', 'SUCCESS', 'Restore point created successfully.', '78360', '1', '2026-04-05 17:45:14', '2026-04-05 17:45:14');
INSERT INTO `backup_logs` (`id`, `filename`, `backup_type`, `restore_label`, `is_restore_point`, `status`, `details`, `file_size`, `requested_by`, `created_at`, `completed_at`) VALUES ('3', 'restore_point_20260405_114540.sql', 'RESTORE_POINT', 'first test', '1', 'SUCCESS', 'Restore point created successfully.', '78711', '1', '2026-04-05 17:45:40', '2026-04-05 17:45:40');
INSERT INTO `backup_logs` (`id`, `filename`, `backup_type`, `restore_label`, `is_restore_point`, `status`, `details`, `file_size`, `requested_by`, `created_at`, `completed_at`) VALUES ('4', 'backup_20260405_114621.sql', 'MANUAL', 'Pre-restore safety backup before first test', '0', 'RUNNING', NULL, NULL, '1', '2026-04-05 17:46:21', NULL);

--
-- Table structure for customers
--
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for customers
--
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('1', '1', '9', 'customer1', 'CUST-2026-0001', 'Fritz Harly', 'Degamo', '123', 'fritz@harly.com', 'Metro manila', 'Taguig City', 'central signal', 'syquio', '2026-04-05 15:56:11', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('2', '1', NULL, 'maria.santos', 'CUST-1001', 'Maria', 'Santos', '09170000001', 'maria.santos@example.com', 'Metro Manila', 'Quezon City', 'Bagumbayan', '12 Sampaguita St', '2026-01-08 09:15:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('3', '1', NULL, 'john.reyes', 'CUST-1002', 'John', 'Reyes', '09170000002', 'john.reyes@example.com', 'Bulacan', 'Malolos', 'Santo Rosario', '45 Rizal Ave', '2026-01-10 10:00:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('4', '1', NULL, 'ana.cruz', 'CUST-1003', 'Ana', 'Cruz', '09170000003', 'ana.cruz@example.com', 'Laguna', 'Santa Rosa', 'Balibago', '89 Mabini St', '2026-01-12 08:30:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('5', '1', NULL, 'carlo.garcia', 'CUST-1004', 'Carlo', 'Garcia', '09170000004', 'carlo.garcia@example.com', 'Cavite', 'Bacoor', 'Talaba', '17 Luna St', '2026-01-14 13:10:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('6', '1', NULL, 'liza.mendoza', 'CUST-1005', 'Liza', 'Mendoza', '09170000005', 'liza.mendoza@example.com', 'Pampanga', 'San Fernando', 'Del Pilar', '102 Orchid Rd', '2026-01-18 14:20:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('7', '1', NULL, 'paolo.dizon', 'CUST-1006', 'Paolo', 'Dizon', '09170000006', 'paolo.dizon@example.com', 'Pangasinan', 'Dagupan', 'Poblacion Oeste', '7 Bonifacio Blvd', '2026-01-22 09:45:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('8', '1', NULL, 'jenny.flores', 'CUST-1007', 'Jenny', 'Flores', '09170000007', 'jenny.flores@example.com', 'Batangas', 'Lipa', 'Sabang', '33 Acacia St', '2026-01-26 11:35:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('9', '1', NULL, 'mark.valdez', 'CUST-1008', 'Mark', 'Valdez', '09170000008', 'mark.valdez@example.com', 'Nueva Ecija', 'Cabanatuan', 'Sangitan', '51 Maharlika Hwy', '2026-02-02 15:05:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('10', '1', NULL, 'ella.ramos', 'CUST-1009', 'Ella', 'Ramos', '09170000009', 'ella.ramos@example.com', 'Tarlac', 'Tarlac City', 'San Vicente', '66 J Luna St', '2026-02-08 10:50:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('11', '1', NULL, 'nico.castillo', 'CUST-1010', 'Nico', 'Castillo', '09170000010', 'nico.castillo@example.com', 'Metro Manila', 'Pasig', 'Santolan', '18 Emerald Ave', '2026-02-11 16:25:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('12', '1', NULL, 'rose.navarro', 'CUST-1011', 'Rose', 'Navarro', '09170000011', 'rose.navarro@example.com', 'Bataan', 'Balanga', 'Cupang Proper', '120 Mabuhay Rd', '2026-02-16 09:10:00', '1');
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `username`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('13', '1', NULL, 'leo.manalo', 'CUST-1012', 'Leo', 'Manalo', '09170000012', 'leo.manalo@example.com', 'Zambales', 'Olongapo', 'East Bajac-Bajac', '27 Harbor Point', '2026-02-20 13:40:00', '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for loans
--
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('1', '1', 'LN-2026-0001', '2', '15000.00', '12.00', 'MONTHLY', '6', '15600.00', '10000.00', 'ACTIVE', '2026-01-09 08:00:00', NULL, '2026-01-09 13:00:00', NULL, '2026-01-10 09:00:00', '2026-01-10 11:30:00', NULL, '2026-01-11 09:00:00', '2026-07-11', NULL, 'Regular appliance loan.', '2026-01-09 08:00:00', '2026-04-05 16:36:35', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('2', '1', 'LN-2026-0002', '3', '25000.00', '10.00', 'MONTHLY', '10', '26000.00', '0.00', 'CLOSED', '2026-01-12 10:15:00', NULL, '2026-01-12 15:00:00', NULL, '2026-01-13 10:00:00', '2026-01-13 14:30:00', '6', '2026-01-14 10:00:00', '2026-11-14', NULL, 'Closed small business loan.', '2026-01-12 10:15:00', '2026-04-05 16:36:27', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('3', '1', 'LN-2026-0003', '4', '18000.00', '11.50', 'MONTHLY', '8', '19680.00', '19680.00', 'APPROVED', '2026-01-15 11:00:00', NULL, '2026-01-15 16:30:00', NULL, '2026-01-16 09:40:00', '2026-01-16 12:15:00', NULL, NULL, NULL, NULL, 'Approved and pending release.', '2026-01-15 11:00:00', '2026-04-05 16:23:19', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('4', '1', 'LN-2026-0004', '5', '32000.00', '13.00', 'MONTHLY', '12', '36992.00', '36992.00', 'CI_REVIEWED', '2026-01-19 09:10:00', NULL, '2026-01-19 15:45:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Awaiting manager approval after CI review.', '2026-01-19 09:10:00', '2026-04-05 16:23:19', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('5', '1', 'LN-2026-0005', '6', '22000.00', '9.50', 'MONTHLY', '6', '23254.00', '23254.00', 'PENDING', '2026-01-24 14:30:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Freshly submitted salary loan.', '2026-01-24 14:30:00', '2026-04-05 16:23:19', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('6', '1', 'LN-2026-0006', '7', '40000.00', '14.00', 'MONTHLY', '12', '41600.00', '34000.00', 'ACTIVE', '2026-01-27 08:45:00', NULL, '2026-01-27 13:20:00', NULL, '2026-01-28 09:10:00', '2026-01-28 11:45:00', NULL, '2026-01-29 10:00:00', '2027-01-29', NULL, 'Vehicle repair capital.', '2026-01-27 08:45:00', '2026-04-05 16:36:35', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('7', '1', 'LN-2026-0007', '8', '12000.00', '10.00', 'MONTHLY', '4', '12640.00', '6400.00', 'OVERDUE', '2026-02-01 09:00:00', NULL, '2026-02-01 14:15:00', NULL, '2026-02-02 10:05:00', '2026-02-02 13:20:00', NULL, '2026-02-03 09:10:00', '2026-03-03', NULL, 'Past due one installment.', '2026-02-01 09:00:00', '2026-04-05 16:36:35', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('8', '1', 'LN-2026-0008', '9', '27000.00', '12.50', 'MONTHLY', '9', '30037.50', '0.00', 'DENIED', '2026-02-05 10:20:00', NULL, '2026-02-05 16:05:00', NULL, '2026-02-06 09:25:00', NULL, NULL, NULL, NULL, 'Insufficient repayment capacity based on submitted documents.', 'Denied after review.', '2026-02-05 10:20:00', '2026-04-05 16:23:19', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('9', '1', 'LN-2026-0009', '10', '50000.00', '15.00', 'MONTHLY', '12', '52000.00', '37625.00', 'ACTIVE', '2026-02-09 08:20:00', NULL, '2026-02-09 13:40:00', NULL, '2026-02-10 10:15:00', '2026-02-10 14:00:00', NULL, '2026-02-11 09:00:00', '2027-02-11', NULL, 'Inventory expansion loan.', '2026-02-09 08:20:00', '2026-04-05 16:36:35', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('10', '1', 'LN-2026-0010', '11', '14500.00', '9.00', 'MONTHLY', '5', '15152.50', '15152.50', 'PENDING', '2026-02-12 15:10:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Pending identity verification.', '2026-02-12 15:10:00', '2026-04-05 16:23:19', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('11', '1', 'LN-2026-0011', '12', '36000.00', '13.50', 'MONTHLY', '12', '37680.00', '30870.00', 'OVERDUE', '2026-02-17 09:35:00', NULL, '2026-02-17 14:10:00', NULL, '2026-02-18 09:55:00', '2026-02-18 13:05:00', NULL, '2026-02-19 08:45:00', '2026-03-19', NULL, 'Needs collection follow-up.', '2026-02-17 09:35:00', '2026-04-05 16:36:35', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('12', '1', 'LN-2026-0012', '13', '21000.00', '11.00', 'MONTHLY', '7', '22617.00', '22617.00', 'CI_REVIEWED', '2026-02-22 11:50:00', NULL, '2026-02-22 16:20:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Waiting for manager queue.', '2026-02-22 11:50:00', '2026-04-05 16:23:19', '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for payments
--
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '2800.00', '2026-02-11', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1001', NULL, NULL, 'First monthly payment.', '2026-02-11 10:05:00', '2026-04-05 16:28:23');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('2', '1', '1', '2800.00', '2026-03-11', 'GCASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1002', NULL, NULL, 'Second monthly payment.', '2026-03-11 09:20:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('3', '1', '2', '5500.00', '2026-02-14', 'BANK', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1003', NULL, NULL, 'Initial payment for closed account.', '2026-02-14 13:15:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('4', '1', '2', '5500.00', '2026-03-14', 'BANK', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1004', NULL, NULL, 'Second payment for closed account.', '2026-03-14 14:05:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('5', '1', '2', '5500.00', '2026-04-14', 'CHEQUE', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1005', NULL, NULL, 'Third payment for closed account.', '2026-04-14 11:10:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('6', '1', '2', '5500.00', '2026-05-14', 'CHEQUE', 'CHK-22014', '2026-05-14', 'MetroBank', 'John Reyes', NULL, NULL, 'OR-2026-1006', NULL, NULL, 'Fourth payment for closed account.', '2026-05-14 15:30:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('7', '1', '2', '5500.00', '2026-06-14', 'DIGITAL', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1007', NULL, NULL, 'Final payment for closed account.', '2026-06-14 10:40:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('8', '1', '6', '3800.00', '2026-02-28', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1008', NULL, NULL, 'First payment for active loan.', '2026-02-28 09:55:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('9', '1', '6', '3800.00', '2026-03-29', 'GCASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1009', NULL, NULL, 'Second payment for active loan.', '2026-03-29 16:05:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('10', '1', '7', '6240.00', '2026-03-03', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1010', NULL, NULL, 'First installment for overdue account.', '2026-03-03 11:25:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('11', '1', '9', '7187.50', '2026-03-11', 'BANK', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1011', NULL, NULL, 'Initial payment for enterprise inventory loan.', '2026-03-11 10:15:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('12', '1', '9', '7187.50', '2026-04-11', 'BANK', NULL, NULL, NULL, 'Ella Ramos', 'BNK-981223', NULL, 'OR-2026-1012', NULL, NULL, 'Second payment for enterprise inventory loan.', '2026-04-11 09:45:00', '2026-04-05 16:28:24');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('13', '1', '11', '6810.00', '2026-03-19', 'OTHER', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-2026-1013', NULL, NULL, 'Partial payment received before follow-up.', '2026-03-19 14:50:00', '2026-04-05 16:28:24');

--
-- Table structure for plans
--
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `monthly_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `max_total_users` int(11) NOT NULL DEFAULT 0,
  `max_admins` int(11) NOT NULL DEFAULT 0,
  `max_managers` int(11) NOT NULL DEFAULT 0,
  `max_credit_investigators` int(11) NOT NULL DEFAULT 0,
  `max_loan_officers` int(11) NOT NULL DEFAULT 0,
  `max_cashiers` int(11) NOT NULL DEFAULT 0,
  `feature_reports` tinyint(1) NOT NULL DEFAULT 0,
  `feature_exports` tinyint(1) NOT NULL DEFAULT 0,
  `feature_audit_logs` tinyint(1) NOT NULL DEFAULT 0,
  `feature_logo_upload` tinyint(1) NOT NULL DEFAULT 0,
  `feature_theme_customization` tinyint(1) NOT NULL DEFAULT 0,
  `feature_editable_receipts` tinyint(1) NOT NULL DEFAULT 0,
  `feature_editable_vouchers` tinyint(1) NOT NULL DEFAULT 0,
  `feature_custom_loan_config` tinyint(1) NOT NULL DEFAULT 0,
  `feature_advanced_reports` tinyint(1) NOT NULL DEFAULT 0,
  `feature_priority_support` tinyint(1) NOT NULL DEFAULT 0,
  `feature_system_name_customization` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`plan_id`),
  UNIQUE KEY `unique_plan_code` (`code`),
  KEY `idx_plans_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for plans
--
INSERT INTO `plans` (`plan_id`, `code`, `name`, `monthly_price`, `description`, `max_total_users`, `max_admins`, `max_managers`, `max_credit_investigators`, `max_loan_officers`, `max_cashiers`, `feature_reports`, `feature_exports`, `feature_audit_logs`, `feature_logo_upload`, `feature_theme_customization`, `feature_editable_receipts`, `feature_editable_vouchers`, `feature_custom_loan_config`, `feature_advanced_reports`, `feature_priority_support`, `feature_system_name_customization`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'BASIC', 'Basic', '999.00', 'Starter plan for small lending teams with core operations only.', '6', '1', '1', '1', '2', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1', '1', '2026-04-05 13:57:48', '2026-04-05 15:13:58');
INSERT INTO `plans` (`plan_id`, `code`, `name`, `monthly_price`, `description`, `max_total_users`, `max_admins`, `max_managers`, `max_credit_investigators`, `max_loan_officers`, `max_cashiers`, `feature_reports`, `feature_exports`, `feature_audit_logs`, `feature_logo_upload`, `feature_theme_customization`, `feature_editable_receipts`, `feature_editable_vouchers`, `feature_custom_loan_config`, `feature_advanced_reports`, `feature_priority_support`, `feature_system_name_customization`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'PROFESSIONAL', 'Professional', '1499.00', 'Growth plan with branding, exports, and editable receipt and voucher support.', '15', '1', '2', '2', '7', '3', '1', '1', '0', '1', '1', '1', '1', '0', '0', '0', '1', '1', '2026-04-05 13:57:48', '2026-04-05 15:13:58');
INSERT INTO `plans` (`plan_id`, `code`, `name`, `monthly_price`, `description`, `max_total_users`, `max_admins`, `max_managers`, `max_credit_investigators`, `max_loan_officers`, `max_cashiers`, `feature_reports`, `feature_exports`, `feature_audit_logs`, `feature_logo_upload`, `feature_theme_customization`, `feature_editable_receipts`, `feature_editable_vouchers`, `feature_custom_loan_config`, `feature_advanced_reports`, `feature_priority_support`, `feature_system_name_customization`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'ENTERPRISE', 'Enterprise', '1999.00', 'Full feature plan with automation, audit visibility, and advanced loan configuration.', '30', '1', '4', '4', '15', '6', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '2026-04-05 13:57:48', '2026-04-05 15:13:58');

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
-- Table structure for restore_logs
--
DROP TABLE IF EXISTS `restore_logs`;
CREATE TABLE `restore_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_log_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `restored_by` int(11) DEFAULT NULL,
  `status` enum('RUNNING','SUCCESS','FAILED') NOT NULL DEFAULT 'RUNNING',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_restore_logs_status` (`status`),
  KEY `idx_restore_logs_backup` (`backup_log_id`),
  KEY `idx_restore_logs_created_at` (`created_at`),
  KEY `fk_restore_logs_user` (`restored_by`),
  CONSTRAINT `fk_restore_logs_backup` FOREIGN KEY (`backup_log_id`) REFERENCES `backup_logs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_restore_logs_user` FOREIGN KEY (`restored_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for restore_logs
--
INSERT INTO `restore_logs` (`id`, `backup_log_id`, `filename`, `restored_by`, `status`, `details`, `created_at`, `completed_at`) VALUES ('1', '3', 'restore_point_20260405_114540.sql', '1', 'RUNNING', 'Restore started for first test.', '2026-04-05 17:46:21', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for system_settings
--
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('1', '1', 'tenant_1', NULL, '#0f1b35', '2026-04-05 13:58:42', '2026-04-05 17:14:54');
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('2', '2', 'tenant_2', NULL, '#2c3ec5', '2026-04-05 14:16:06', '2026-04-05 14:16:06');
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('3', '3', 'tenant_3', NULL, '#2c3ec5', '2026-04-05 14:31:56', '2026-04-05 14:31:56');
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('4', '4', 'tenant_5', NULL, '#2c3ec5', '2026-04-05 17:22:21', '2026-04-05 17:22:21');

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
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('1', '1', '2', '1', '2026-04-05 14:02:43');
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('3', '2', '2', '1', '2026-04-05 14:16:52');
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('4', '3', '2', '1', '2026-04-05 14:31:56');

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
  `plan_id` int(11) DEFAULT NULL,
  `subscription_status` enum('TRIAL','ACTIVE','PAST_DUE','SUSPENDED','CANCELLED','EXPIRED') NOT NULL DEFAULT 'ACTIVE',
  `subscription_started_at` datetime DEFAULT NULL,
  `subscription_expires_at` datetime DEFAULT NULL,
  `logo_path` varchar(500) DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT '#2c3ec5',
  `is_active` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`tenant_id`),
  UNIQUE KEY `tenant_name` (`tenant_name`),
  UNIQUE KEY `subdomain` (`subdomain`),
  KEY `idx_tenants_status` (`tenant_status`),
  KEY `idx_tenants_plan` (`plan_code`),
  KEY `idx_tenants_plan_id` (`plan_id`),
  KEY `idx_tenants_subscription` (`subscription_status`,`subscription_expires_at`),
  CONSTRAINT `fk_tenants_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`plan_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for tenants
--
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `plan_id`, `subscription_status`, `subscription_started_at`, `subscription_expires_at`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'tenant_1', 'tenant1', 'tenant_1', 'ACTIVE', 'PROFESSIONAL', '2', 'ACTIVE', '2026-04-05 08:20:00', NULL, NULL, '#2c3ec5', '1', '2026-04-05 13:58:42', '2026-04-05 17:00:06');
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `plan_id`, `subscription_status`, `subscription_started_at`, `subscription_expires_at`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'tenant_2', 'tenant2', 'tenant_2', 'ACTIVE', 'BASIC', '1', 'ACTIVE', '2026-04-05 08:16:06', '2026-05-05 08:16:06', NULL, '#2c3ec5', '1', '2026-04-05 14:16:06', '2026-04-05 14:43:34');
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `plan_id`, `subscription_status`, `subscription_started_at`, `subscription_expires_at`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'tenant_3', 'tenant3', 'tenant_3', 'ACTIVE', 'BASIC', '1', 'ACTIVE', '2026-04-05 08:31:56', '2026-05-05 08:31:56', NULL, '#2c3ec5', '1', '2026-04-05 14:31:56', '2026-04-05 15:16:12');
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `plan_id`, `subscription_status`, `subscription_started_at`, `subscription_expires_at`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'tenant_5', 'tenant5', 'tenant_5', 'PENDING', 'BASIC', '1', 'ACTIVE', '2026-04-05 11:22:21', '2026-05-05 11:22:21', NULL, '#2c3ec5', '0', '2026-04-05 17:22:21', '2026-04-05 17:22:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for users
--
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('1', NULL, 'admin', '$2y$10$yMa4cQxzEvHF2jzoR31gsuMRA8fmecaIt9jVi62gaGbYQNLphVH5i', NULL, NULL, 'System Super Admin', 'SUPER_ADMIN', NULL, 'superadmin@localhost', '2026-04-05 13:57:48', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('2', NULL, 'admin1', '$2y$10$w9vNnxocFgoNEFL6.o4Pyudnd4m1qzuBT8D6qsuQvHOt6pe8k1h0W', NULL, NULL, 'admin 1', 'ADMIN', NULL, 'fritzharlydegamo@gmail.com', '2026-04-05 13:59:22', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('3', '1', 'manager1', '$2y$10$cvvDmAd0NB/sDVztRzxI8.1uVet5rwvKfYsQrvRUT0WVJjJWmUCJW', NULL, NULL, 'manager 1', 'MANAGER', NULL, 'fritzharlydegamo@gmail.com', '2026-04-05 15:21:33', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('4', '1', 'credit1', '$2y$10$a689WQvK4eOLJtHyu8vNwOxb1zHQaYFfDjv/1hA.U8wf9GeMi/j56', NULL, NULL, 'credit 1', 'CREDIT_INVESTIGATOR', NULL, 'fritzharlydegamo@gmail.com', '2026-04-05 15:31:28', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('6', '1', 'lofficer1', '$2y$10$ID2hnfveyHMBGekJLkhDrOCpbqJAIwwWd3V8J/drkSQh8WNYRI1cG', '67c4cc301e7c148260ab61c52eb1c7ff4405eaa2a6b4d7abbea3aec2b9ee0cd7', '2026-04-12 15:36:21', 'loan officer 1', 'LOAN_OFFICER', NULL, 'fritzharlydegamo@gmail.com', '2026-04-05 15:36:21', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('9', '1', 'customer1', '$2y$10$a2MSGoD6R8CDTFedrU0FV.1.KzOK17EMF0ouRmF3AQ19pslbTrvNi', NULL, NULL, 'Fritz Harly Degamo', 'CUSTOMER', NULL, NULL, '2026-04-05 15:56:11', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('10', NULL, 'admin5', '$2y$10$BEb2VlKiwWjIIzshviMYC.IPlJ.l7YSRvcDZKfY8Tn4Pp773Htlke', 'f072a77b0469b621e35b10288ac2220a88f42e689bec390e3a315e1a3e79291d', '2026-04-12 16:10:26', 'admin owner 5', 'ADMIN', NULL, 'fritzharlydegamo@gmail.com', '2026-04-05 16:10:26', '1');

SET FOREIGN_KEY_CHECKS=1;
