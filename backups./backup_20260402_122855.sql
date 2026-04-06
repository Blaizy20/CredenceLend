-- CredenceLend manual backup
-- Generated at 2026-04-02 12:28:55

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
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('16', '1', '4', 'MANAGER', 'USER_LOGOUT', 'Staff user logged out: Manager tenant 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 03:45:39');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('17', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 03:49:35');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('18', NULL, '1', 'SUPER_ADMIN', 'STAFF_CREATED', 'Admin owner account created: admin owner 3 (ADMIN)', NULL, NULL, NULL, '2026-04-02 03:51:39');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('19', '1', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 03:51:44');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('20', NULL, '5', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: admin owner 3 (ADMIN).', NULL, NULL, NULL, '2026-04-02 03:51:52');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('21', '3', '1', 'SUPER_ADMIN', 'TENANT_CREATED', 'Tenant created: tenant_3 (tenant3). Status: Pending Approval. Plan: Basic.', NULL, NULL, NULL, '2026-04-02 03:54:11');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('22', '3', '1', 'SUPER_ADMIN', 'TENANT_APPROVED', 'Tenant workflow updated for tenant_3 (tenant3): Pending Approval -> Active. Plan: Basic.', NULL, NULL, NULL, '2026-04-02 03:54:23');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('23', '3', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_3 (tenant3). Previous owner: admin owner 3. New owner: admin owner 3.', NULL, NULL, NULL, '2026-04-02 03:54:39');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('24', '3', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_3 (tenant3). Previous owner: admin owner 3. New owner: Admin owner 2.', NULL, NULL, NULL, '2026-04-02 03:54:51');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('25', '3', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_3 (tenant3). Previous owner: Admin owner 2. New owner: admin owner 3.', NULL, NULL, NULL, '2026-04-02 03:55:07');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('26', '3', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_3 (tenant3). Previous owner: admin owner 3. New owner: admin owner 3.', NULL, NULL, NULL, '2026-04-02 03:56:20');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('27', '4', '1', 'SUPER_ADMIN', 'TENANT_CREATED', 'Tenant created: tenant_4 (tenant4). Status: Pending Approval. Plan: Basic.', NULL, NULL, NULL, '2026-04-02 03:56:54');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('28', '4', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_4 (tenant4). Previous owner: admin owner 3. New owner: admin owner 3.', NULL, NULL, NULL, '2026-04-02 03:57:04');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('29', '4', '1', 'SUPER_ADMIN', 'TENANT_APPROVED', 'Tenant workflow updated for tenant_4 (tenant4): Pending Approval -> Active. Plan: Basic.', NULL, NULL, NULL, '2026-04-02 03:57:06');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('30', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 04:18:29');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('31', '1', '2', 'ADMIN', 'Customer Registered', 'New customer Fritz Degamo registered', NULL, '1', 'CUST-2026-0001', '2026-04-02 04:30:02');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('32', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: credit investigator 1 (CREDIT_INVESTIGATOR)', NULL, NULL, NULL, '2026-04-02 04:31:09');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('33', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: loan officer 1 (LOAN_OFFICER)', NULL, NULL, NULL, '2026-04-02 04:32:23');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('34', '1', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: cashier 1 (CASHIER)', NULL, NULL, NULL, '2026-04-02 04:33:02');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('35', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0001-864', '3', '1', 'APP-20260402-0001-864', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('36', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0001-864', '3', '1', 'APP-20260402-0001-864', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('37', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0002-895', '4', '1', 'APP-20260402-0002-895', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('38', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0002-895', '4', '1', 'APP-20260402-0002-895', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('39', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0003-742', '5', '1', 'APP-20260402-0003-742', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('40', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0003-742', '5', '1', 'APP-20260402-0003-742', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('41', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0004-770', '6', '1', 'APP-20260402-0004-770', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('42', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0004-770', '6', '1', 'APP-20260402-0004-770', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('43', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0005-911', '7', '1', 'APP-20260402-0005-911', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('44', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0006-317', '8', '1', 'APP-20260402-0006-317', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('45', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0006-317', '8', '1', 'APP-20260402-0006-317', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('46', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0007-126', '9', '1', 'APP-20260402-0007-126', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('47', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0007-126', '9', '1', 'APP-20260402-0007-126', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('48', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0008-489', '10', '1', 'APP-20260402-0008-489', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('49', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0008-489', '10', '1', 'APP-20260402-0008-489', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('50', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0009-629', '11', '1', 'APP-20260402-0009-629', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('51', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0009-629', '11', '1', 'APP-20260402-0009-629', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('52', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0010-894', '12', '1', 'APP-20260402-0010-894', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('53', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0011-454', '13', '1', 'APP-20260402-0011-454', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('54', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0011-454', '13', '1', 'APP-20260402-0011-454', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('55', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0012-269', '14', '1', 'APP-20260402-0012-269', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('56', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0012-269', '14', '1', 'APP-20260402-0012-269', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('57', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0013-729', '15', '1', 'APP-20260402-0013-729', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('58', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0013-729', '15', '1', 'APP-20260402-0013-729', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('59', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0014-913', '16', '1', 'APP-20260402-0014-913', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('60', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0014-913', '16', '1', 'APP-20260402-0014-913', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('61', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0015-961', '17', '1', 'APP-20260402-0015-961', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('62', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0016-989', '18', '1', 'APP-20260402-0016-989', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('63', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0016-989', '18', '1', 'APP-20260402-0016-989', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('64', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0017-249', '19', '1', 'APP-20260402-0017-249', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('65', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0017-249', '19', '1', 'APP-20260402-0017-249', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('66', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0018-309', '20', '1', 'APP-20260402-0018-309', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('67', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0018-309', '20', '1', 'APP-20260402-0018-309', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('68', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0019-737', '21', '1', 'APP-20260402-0019-737', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('69', '1', '4', 'MANAGER', 'Loan Approved', 'Loan approved and activated: APP-20260402-0019-737', '21', '1', 'APP-20260402-0019-737', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('70', '1', '7', 'CREDIT_INVESTIGATOR', 'CI Review', 'Loan marked as CI reviewed: APP-20260402-0020-887', '22', '1', 'APP-20260402-0020-887', '2026-04-02 04:46:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('71', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0001-985', '23', '1', 'APP-CI-20260402-0001-985', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('72', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0002-246', '24', '1', 'APP-CI-20260402-0002-246', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('73', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0003-951', '25', '1', 'APP-CI-20260402-0003-951', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('74', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0004-564', '26', '1', 'APP-CI-20260402-0004-564', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('75', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0005-740', '27', '1', 'APP-CI-20260402-0005-740', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('76', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0006-753', '28', '1', 'APP-CI-20260402-0006-753', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('77', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0007-809', '29', '1', 'APP-CI-20260402-0007-809', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('78', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0008-744', '30', '1', 'APP-CI-20260402-0008-744', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('79', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0009-407', '31', '1', 'APP-CI-20260402-0009-407', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('80', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0010-694', '32', '1', 'APP-CI-20260402-0010-694', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('81', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0011-706', '33', '1', 'APP-CI-20260402-0011-706', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('82', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0012-309', '34', '1', 'APP-CI-20260402-0012-309', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('83', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0013-111', '35', '1', 'APP-CI-20260402-0013-111', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('84', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0014-643', '36', '1', 'APP-CI-20260402-0014-643', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('85', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0015-139', '37', '1', 'APP-CI-20260402-0015-139', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('86', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0016-526', '38', '1', 'APP-CI-20260402-0016-526', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('87', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0017-486', '39', '1', 'APP-CI-20260402-0017-486', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('88', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0018-526', '40', '1', 'APP-CI-20260402-0018-526', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('89', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0019-924', '41', '1', 'APP-CI-20260402-0019-924', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('90', '1', NULL, 'CUSTOMER', 'LOAN_CREATED', 'Pending application submitted for CI review: APP-CI-20260402-0020-114', '42', '1', 'APP-CI-20260402-0020-114', '2026-04-02 04:52:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('91', '2', '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 05:04:19');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('92', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 05:04:24');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('93', '4', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_4 (tenant4). Previous owner: admin owner 3. New owner: Admin owner 1.', NULL, NULL, NULL, '2026-04-02 05:04:48');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('94', '4', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 05:43:09');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('95', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 06:05:02');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('96', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 08:05:41');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('97', '1', '2', 'ADMIN', 'Staff Deactivated', 'Staff cashier 1 deactivated', NULL, NULL, NULL, '2026-04-02 08:42:56');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('98', '1', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 08:43:07');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('99', '1', '4', 'MANAGER', 'USER_LOGIN', 'Staff user logged in: Manager tenant 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 08:47:10');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('100', '1', '4', 'MANAGER', 'USER_LOGOUT', 'Staff user logged out: Manager tenant 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 08:50:58');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('101', '1', '9', 'MANAGER', 'USER_LOGIN', 'Staff user logged in: cashier 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 08:52:12');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('102', '1', '9', 'MANAGER', 'USER_LOGOUT', 'Staff user logged out: cashier 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 08:54:52');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('103', '2', '3', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 2 (ADMIN).', NULL, NULL, NULL, '2026-04-02 08:55:00');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('104', '2', '3', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Admin owner 2 (ADMIN).', NULL, NULL, NULL, '2026-04-02 08:56:32');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('105', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 08:56:45');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('106', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 10:51:36');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('107', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 13:14:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('108', '1', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 13:33:31');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('109', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 13:34:01');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('110', NULL, '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 13:41:49');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('111', '1', '4', 'MANAGER', 'USER_LOGIN', 'Staff user logged in: Manager tenant 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 13:42:50');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('112', '1', '4', 'MANAGER', 'USER_LOGOUT', 'Staff user logged out: Manager tenant 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 13:49:58');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('113', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 13:50:02');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('114', NULL, '1', 'SUPER_ADMIN', 'STAFF_CREATED', 'Admin owner account created: Fritz Harly (ADMIN)', NULL, NULL, NULL, '2026-04-02 14:05:07');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('115', '2', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant_2 (tenant2). Previous owner: Admin owner 2. New owner: Fritz Harly.', NULL, NULL, NULL, '2026-04-02 14:06:44');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('116', NULL, '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 14:06:53');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('117', '2', '10', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Fritz Harly (ADMIN).', NULL, NULL, NULL, '2026-04-02 14:07:18');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('118', '2', '10', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Fritz Harly (ADMIN).', NULL, NULL, NULL, '2026-04-02 14:07:22');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('119', NULL, '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 14:30:44');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('120', '2', '10', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Fritz Harly (ADMIN).', NULL, NULL, NULL, '2026-04-02 14:32:57');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('121', '2', '10', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Fritz Harly (ADMIN).', NULL, NULL, NULL, '2026-04-02 14:37:33');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('122', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 14:54:07');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('123', '1', '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 15:04:41');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('124', '1', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 15:05:01');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('125', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 15:09:50');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('126', '1', '2', 'ADMIN', 'USER_LOGOUT', 'Staff user logged out: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 15:13:26');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('127', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 15:13:31');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('128', NULL, '1', 'SUPER_ADMIN', 'STAFF_CREATED', 'Admin owner account created: admin send email (ADMIN)', NULL, NULL, NULL, '2026-04-02 15:15:35');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('129', '1', '1', 'SUPER_ADMIN', 'TENANT_UPDATED', 'Tenant owner updated for tenant 1 (tenant1). Previous owner: Admin owner 1. New owner: admin send email.', NULL, NULL, NULL, '2026-04-02 15:16:20');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('130', NULL, '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 15:21:25');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('131', '1', '9', 'MANAGER', 'USER_LOGIN', 'Staff user logged in: cashier 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 15:21:52');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('132', '1', '9', 'MANAGER', 'USER_LOGOUT', 'Staff user logged out: cashier 1 (MANAGER).', NULL, NULL, NULL, '2026-04-02 17:54:24');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('133', NULL, '1', 'SUPER_ADMIN', 'USER_LOGOUT', 'Staff user logged out: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 17:54:38');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('134', '4', '2', 'ADMIN', 'USER_LOGIN', 'Staff user logged in: Admin owner 1 (ADMIN).', NULL, NULL, NULL, '2026-04-02 17:54:47');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('135', '4', '2', 'ADMIN', 'STAFF_CREATED', 'Staff account created: Cashier 3 (CASHIER)', NULL, NULL, NULL, '2026-04-02 17:55:40');
INSERT INTO `activity_logs` (`log_id`, `tenant_id`, `user_id`, `user_role`, `action`, `description`, `loan_id`, `customer_id`, `reference_no`, `created_at`) VALUES ('136', NULL, '1', 'SUPER_ADMIN', 'USER_LOGIN', 'Staff user logged in: System Super Admin (SUPER_ADMIN).', NULL, NULL, NULL, '2026-04-02 18:02:49');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for backup_logs
--
INSERT INTO `backup_logs` (`id`, `filename`, `status`, `details`, `file_size`, `requested_by`, `created_at`, `completed_at`) VALUES ('1', 'backup_20260401_214247.sql', 'SUCCESS', 'Backup completed successfully.', '24732', '1', '2026-04-02 03:42:47', '2026-04-02 03:42:48');
INSERT INTO `backup_logs` (`id`, `filename`, `status`, `details`, `file_size`, `requested_by`, `created_at`, `completed_at`) VALUES ('2', 'backup_20260402_122855.sql', 'RUNNING', NULL, NULL, '1', '2026-04-02 18:28:55', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for customers
--
INSERT INTO `customers` (`customer_id`, `tenant_id`, `user_id`, `customer_no`, `first_name`, `last_name`, `contact_no`, `email`, `province`, `city`, `barangay`, `street`, `created_at`, `is_active`) VALUES ('1', '1', '6', 'CUST-2026-0001', 'Fritz', 'Degamo', '09673171503', 'fritz@harly.com', 'Metro manila', 'Taguig City', 'central signal', 'syquio', '2026-04-02 04:30:02', '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for loans
--
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('3', '1', 'APP-20260402-0001-864', '1', '11331.36', '3.00', 'weekly', '6', '11671.30', '11671.30', 'ACTIVE', '2026-03-25 04:46:26', '7', '2026-03-26 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2026-10-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('4', '1', 'APP-20260402-0002-895', '1', '16069.96', '3.50', 'semi_monthly', '9', '16632.41', '16632.41', 'ACTIVE', '2026-03-19 04:46:26', '7', '2026-03-20 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-01-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('5', '1', 'APP-20260402-0003-742', '1', '19673.41', '4.00', 'monthly', '7', '20460.35', '15345.26', 'ACTIVE', '2026-03-31 04:46:26', '7', '2026-04-01 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2029-01-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 18:26:47', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('6', '1', 'APP-20260402-0004-770', '1', '8284.54', '2.75', 'daily', '11', '8512.36', '8512.36', 'ACTIVE', '2026-03-26 04:46:26', '7', '2026-03-27 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-03-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('7', '1', 'APP-20260402-0005-911', '1', '9061.27', '3.00', 'weekly', '11', '9333.11', '9333.11', 'CI_REVIEWED', '2026-03-07 04:46:26', '7', '2026-03-08 04:46:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' CI reviewed.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('8', '1', 'APP-20260402-0006-317', '1', '22235.91', '3.50', 'semi_monthly', '7', '23014.17', '12657.80', 'ACTIVE', '2026-03-09 04:46:26', '7', '2026-03-10 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-11-27', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 18:26:47', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('9', '1', 'APP-20260402-0007-126', '1', '16077.55', '4.00', 'monthly', '3', '16747.45', '16747.45', 'OVERDUE', '2026-03-06 04:46:26', '7', '2026-03-07 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2026-03-28', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 06:06:15', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('10', '1', 'APP-20260402-0008-489', '1', '6067.84', '2.75', 'daily', '8', '6234.71', '0.00', 'CLOSED', '2026-03-15 04:46:26', '7', '2026-03-16 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2026-12-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('11', '1', 'APP-20260402-0009-629', '1', '13215.41', '3.00', 'weekly', '4', '13611.87', '10208.90', 'ACTIVE', '2026-03-16 04:46:26', '7', '2026-03-17 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-01-31', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 18:26:47', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('12', '1', 'APP-20260402-0010-894', '1', '13243.54', '3.50', 'semi_monthly', '12', '13707.06', '13707.06', 'CI_REVIEWED', '2026-03-19 04:46:26', '7', '2026-03-20 04:46:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' CI reviewed.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('13', '1', 'APP-20260402-0011-454', '1', '17884.27', '4.00', 'monthly', '4', '18599.64', '18599.64', 'ACTIVE', '2026-03-21 04:46:26', '7', '2026-03-22 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2026-08-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('14', '1', 'APP-20260402-0012-269', '1', '6123.74', '2.75', 'daily', '9', '6292.14', '3460.67', 'ACTIVE', '2026-03-05 04:46:26', '7', '2026-03-06 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-01-28', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 18:26:47', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('15', '1', 'APP-20260402-0013-729', '1', '6643.06', '3.00', 'weekly', '10', '6842.35', '6842.35', 'ACTIVE', '2026-03-29 04:46:26', '7', '2026-03-30 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-02-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('16', '1', 'APP-20260402-0014-913', '1', '5447.18', '3.50', 'semi_monthly', '12', '5652.36', '5652.36', 'OVERDUE', '2026-03-17 04:46:26', '7', '2026-03-18 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2026-03-28', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 06:06:15', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('17', '1', 'APP-20260402-0015-961', '1', '15334.17', '4.00', 'monthly', '3', '15947.54', '11960.65', 'CI_REVIEWED', '2026-03-13 04:46:26', '7', '2026-03-14 04:46:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' CI reviewed.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('18', '1', 'APP-20260402-0016-989', '1', '14231.79', '2.75', 'daily', '7', '14623.16', '0.00', 'CLOSED', '2026-03-13 04:46:26', '7', '2026-03-14 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2026-11-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('19', '1', 'APP-20260402-0017-249', '1', '10689.42', '3.00', 'weekly', '10', '11010.10', '11010.10', 'ACTIVE', '2026-03-18 04:46:26', '7', '2026-03-19 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-02-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('20', '1', 'APP-20260402-0018-309', '1', '28563.60', '3.50', 'semi_monthly', '7', '29563.33', '16259.83', 'ACTIVE', '2026-03-06 04:46:26', '7', '2026-03-07 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-11-27', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 18:26:47', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('21', '1', 'APP-20260402-0019-737', '1', '28782.21', '4.00', 'monthly', '12', '29933.50', '29933.50', 'ACTIVE', '2026-03-09 04:46:26', '7', '2026-03-10 04:46:26', '4', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '8', '2026-04-02 04:46:26', '2027-04-02', NULL, ' CI reviewed. Manager approved.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('22', '1', 'APP-20260402-0020-887', '1', '7808.57', '2.75', 'daily', '11', '8023.31', '8023.31', 'CI_REVIEWED', '2026-03-08 04:46:26', '7', '2026-03-09 04:46:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' CI reviewed.', '2026-04-02 04:46:26', '2026-04-02 04:46:26', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('23', '1', 'APP-CI-20260402-0001-985', '1', '9112.79', '3.00', 'weekly', '8', '9386.17', '9386.17', 'PENDING', '2026-03-30 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('24', '1', 'APP-CI-20260402-0002-246', '1', '9045.85', '3.50', 'semi_monthly', '10', '9362.45', '9362.45', 'PENDING', '2026-03-27 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('25', '1', 'APP-CI-20260402-0003-951', '1', '21223.44', '4.00', 'monthly', '5', '22072.38', '22072.38', 'PENDING', '2026-03-20 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('26', '1', 'APP-CI-20260402-0004-564', '1', '3436.82', '2.75', 'daily', '8', '3531.33', '3531.33', 'PENDING', '2026-04-02 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('27', '1', 'APP-CI-20260402-0005-740', '1', '3014.56', '3.00', 'weekly', '4', '3105.00', '3105.00', 'PENDING', '2026-04-01 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('28', '1', 'APP-CI-20260402-0006-753', '1', '6334.86', '3.50', 'semi_monthly', '5', '6556.58', '6556.58', 'PENDING', '2026-03-20 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('29', '1', 'APP-CI-20260402-0007-809', '1', '12382.56', '4.00', 'monthly', '11', '12877.86', '12877.86', 'PENDING', '2026-03-26 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('30', '1', 'APP-CI-20260402-0008-744', '1', '7907.40', '2.75', 'daily', '9', '8124.85', '8124.85', 'PENDING', '2026-03-25 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('31', '1', 'APP-CI-20260402-0009-407', '1', '11458.71', '3.00', 'weekly', '3', '11802.47', '11802.47', 'PENDING', '2026-03-27 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('32', '1', 'APP-CI-20260402-0010-694', '1', '23097.59', '3.50', 'semi_monthly', '7', '23906.01', '23906.01', 'PENDING', '2026-03-21 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('33', '1', 'APP-CI-20260402-0011-706', '1', '3481.48', '4.00', 'monthly', '4', '3620.74', '3620.74', 'PENDING', '2026-03-20 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('34', '1', 'APP-CI-20260402-0012-309', '1', '9462.78', '2.75', 'daily', '9', '9723.01', '9723.01', 'PENDING', '2026-03-29 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('35', '1', 'APP-CI-20260402-0013-111', '1', '24604.73', '3.00', 'weekly', '8', '25342.87', '25342.87', 'PENDING', '2026-03-29 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('36', '1', 'APP-CI-20260402-0014-643', '1', '5525.19', '3.50', 'semi_monthly', '6', '5718.57', '5718.57', 'PENDING', '2026-03-21 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('37', '1', 'APP-CI-20260402-0015-139', '1', '17712.02', '4.00', 'monthly', '3', '18420.50', '18420.50', 'PENDING', '2026-03-31 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('38', '1', 'APP-CI-20260402-0016-526', '1', '13613.14', '2.75', 'daily', '12', '13987.50', '13987.50', 'PENDING', '2026-03-27 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('39', '1', 'APP-CI-20260402-0017-486', '1', '4367.80', '3.00', 'weekly', '6', '4498.83', '4498.83', 'PENDING', '2026-03-31 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('40', '1', 'APP-CI-20260402-0018-526', '1', '13511.80', '3.50', 'semi_monthly', '8', '13984.71', '13984.71', 'PENDING', '2026-03-24 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('41', '1', 'APP-CI-20260402-0019-924', '1', '17531.73', '4.00', 'monthly', '4', '18233.00', '18233.00', 'PENDING', '2026-04-02 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');
INSERT INTO `loans` (`loan_id`, `tenant_id`, `reference_no`, `customer_id`, `principal_amount`, `interest_rate`, `payment_term`, `term_months`, `total_payable`, `remaining_balance`, `status`, `submitted_at`, `ci_by`, `ci_at`, `manager_by`, `manager_at`, `approved_at`, `loan_officer_id`, `activated_at`, `due_date`, `denial_reason`, `notes`, `created_at`, `updated_at`, `is_active`) VALUES ('42', '1', 'APP-CI-20260402-0020-114', '1', '6927.84', '2.75', 'daily', '8', '7118.36', '7118.36', 'PENDING', '2026-03-25 04:52:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dummy application seeded for CI review queue.', '2026-04-02 04:52:49', '2026-04-02 04:52:49', '1');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for money_release_vouchers
--
INSERT INTO `money_release_vouchers` (`voucher_id`, `tenant_id`, `loan_id`, `voucher_no`, `release_date`, `check_no`, `check_amount`, `explanation`, `prepared_by`, `approved_by`, `audited_by`, `received_by_name`, `received_by_date`, `voucher_data`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', '21', 'VCH-20260402-0019-737', '2026-04-02', 'MANUAL', '28782.21', NULL, '4', NULL, NULL, 'FRITZ DEGAMO', NULL, '{\"accounts\":{\"CASH IN BANK-SB\":28782.21,\"UNEARNED SERVICE FEE\":1151.2884,\"UNEARNED NOTARIAL ALLOCATION\":150,\"RISK MANAGEMENT ALLOCATION\":287.8221,\"PAF COLLECTED\":0,\"DOCUMENTARY STAMPS ALLOC\":143.91105}}', 'DRAFT', '2026-04-02 08:48:20', '2026-04-02 08:48:20');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for payments
--
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('1', '1', '5', '5115.09', '2026-04-02', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0003-1', '8', '9', 'Dummy payment 1', '2026-04-02 04:46:26', '2026-04-02 04:46:26');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('2', '1', '8', '5753.54', '2026-04-02', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0006-1', '8', '9', 'Dummy payment 1', '2026-04-02 04:46:26', '2026-04-02 04:46:26');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('3', '1', '8', '4602.83', '2026-03-31', 'GCASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0006-2', '8', '9', 'Dummy payment 2', '2026-04-02 04:46:26', '2026-04-02 04:46:26');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('4', '1', '11', '3402.97', '2026-04-02', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0009-1', '8', '9', 'Dummy payment 1', '2026-04-02 04:46:26', '2026-04-02 04:46:26');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('5', '1', '14', '1573.04', '2026-04-02', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0012-1', '8', '9', 'Dummy payment 1', '2026-04-02 04:46:26', '2026-04-02 04:46:26');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('6', '1', '14', '1258.43', '2026-03-31', 'GCASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0012-2', '8', '9', 'Dummy payment 2', '2026-04-02 04:46:26', '2026-04-02 04:46:26');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('7', '1', '17', '3986.89', '2026-04-02', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0015-1', '8', '9', 'Dummy payment 1', '2026-04-02 04:46:26', '2026-04-02 04:46:26');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('8', '1', '20', '7390.83', '2026-04-02', 'CASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0018-1', '8', '9', 'Dummy payment 1', '2026-04-02 04:46:26', '2026-04-02 04:46:26');
INSERT INTO `payments` (`payment_id`, `tenant_id`, `loan_id`, `amount`, `payment_date`, `method`, `cheque_number`, `cheque_date`, `bank_name`, `account_holder`, `bank_reference_no`, `gcash_reference_no`, `or_no`, `loan_officer_id`, `received_by`, `notes`, `created_at`, `updated_at`) VALUES ('9', '1', '20', '5912.67', '2026-03-31', 'GCASH', NULL, NULL, NULL, NULL, NULL, NULL, 'OR-20260402-0018-2', '8', '9', 'Dummy payment 2', '2026-04-02 04:46:26', '2026-04-02 04:46:26');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for system_settings
--
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('1', '1', 'tenant 10', NULL, '#2c3ec5', '2026-04-02 03:25:52', '2026-04-02 10:52:41');
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('2', '2', 'tenant_2', NULL, '#2c3ec5', '2026-04-02 03:26:54', '2026-04-02 03:26:54');
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('3', '3', 'tenant_3', NULL, '#2c3ec5', '2026-04-02 03:54:11', '2026-04-02 03:54:11');
INSERT INTO `system_settings` (`setting_id`, `tenant_id`, `system_name`, `logo_path`, `primary_color`, `created_at`, `updated_at`) VALUES ('4', '4', 'tenant_4', NULL, '#2c3ec5', '2026-04-02 03:56:53', '2026-04-02 03:56:53');

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for tenant_admins
--
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('9', '3', '5', '1', '2026-04-02 03:56:20');
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('12', '4', '2', '1', '2026-04-02 05:04:48');
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('13', '2', '10', '1', '2026-04-02 14:06:44');
INSERT INTO `tenant_admins` (`id`, `tenant_id`, `user_id`, `is_primary_owner`, `created_at`) VALUES ('14', '1', '12', '1', '2026-04-02 15:16:20');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for tenants
--
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'tenant 1', 'tenant1', 'tenant 1', 'ACTIVE', 'BASIC', NULL, '#2c3ec5', '1', '2026-04-02 03:25:52', '2026-04-02 03:27:20');
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'tenant_2', 'tenant2', 'tenant_2', 'ACTIVE', 'BASIC', NULL, '#2c3ec5', '1', '2026-04-02 03:26:54', '2026-04-02 03:27:38');
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'tenant_3', 'tenant3', 'tenant_3', 'ACTIVE', 'BASIC', NULL, '#2c3ec5', '1', '2026-04-02 03:54:11', '2026-04-02 03:54:23');
INSERT INTO `tenants` (`tenant_id`, `tenant_name`, `subdomain`, `display_name`, `tenant_status`, `plan_code`, `logo_path`, `primary_color`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'tenant_4', 'tenant4', 'tenant_4', 'ACTIVE', 'BASIC', NULL, '#2c3ec5', '1', '2026-04-02 03:56:53', '2026-04-02 03:57:06');

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for users
--
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('1', NULL, 'admin', '$2y$10$yMa4cQxzEvHF2jzoR31gsuMRA8fmecaIt9jVi62gaGbYQNLphVH5i', NULL, NULL, 'System Super Admin', 'SUPER_ADMIN', NULL, 'superadmin@localhost', '2026-04-02 03:20:37', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('2', NULL, 'admin1', '$2y$10$v6YuHnoK7NJ10f.GYNJ.qeErHzn.m4n97d1awkTDbYtxKvdDeuBXG', NULL, NULL, 'Admin owner 1', 'ADMIN', NULL, 'admin1@gmail.com', '2026-04-02 03:22:42', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('3', NULL, 'admin2', '$2y$10$qDyfTRbrV1s3WjH4qPhKzeWEBBbvsgFVjFEAVkAtge0uz5pY4bm66', NULL, NULL, 'Admin owner 2', 'ADMIN', NULL, 'admin2@gmail.com', '2026-04-02 03:26:21', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('4', '1', 'manager1', '$2y$10$/71dWOCHb2e3SnwlTU0WG.NyiiRnVDtlUGlTRNhCxSb4tOw67gP8G', NULL, NULL, 'Manager tenant 1', 'MANAGER', NULL, 'manager1@gmail.com', '2026-04-02 03:30:08', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('5', NULL, 'admin3', '$2y$10$HCZdIP3Qe/UXImOeQ3Fuf.JAVlD.nah/.xqSt3l1nlcChGxTSaviC', NULL, NULL, 'admin owner 3', 'ADMIN', NULL, 'admin3@gmail.com', '2026-04-02 03:51:39', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('6', '1', 'customer1', '$2y$10$AhdtZmJ6ud8UY2.pW63RXuxG57Q3KKLP35NC4fUvBit97Q2xl1J/q', NULL, NULL, 'Fritz Degamo', 'CUSTOMER', NULL, NULL, '2026-04-02 04:30:02', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('7', '1', 'ci1', '$2y$10$xKLQGIEvAwMjLv6awS41ieJtlOwkDuRLYrQ50Iu5t1ciOZaOezEYW', NULL, NULL, 'credit investigator 1', 'CREDIT_INVESTIGATOR', NULL, 'ci1@gfmail.com', '2026-04-02 04:31:09', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('8', '1', 'loan1', '$2y$10$HnqMhOyhlIEMniTIC4Pkc.iWwmZu9AgRaw1JtC78Qm60fntaC2b3K', NULL, NULL, 'loan officer 1', 'LOAN_OFFICER', NULL, 'loan1@gmail.com', '2026-04-02 04:32:23', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('9', '1', 'cashier1', '$2y$10$4Ia7WE4XlYUsFo.IW8YVw.TcebaAyRyRZJehVBgs8uPOCU71RCima', NULL, NULL, 'cashier 1', 'MANAGER', NULL, 'cashier1@gmail.com', '2026-04-02 04:33:02', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('10', NULL, 'fritz', '$2y$10$oZiVHT8vm5zmzoo50z.9ZuXFO1KMAbvYNkuQs0vaGswmGIl2DIq/S', NULL, NULL, 'Fritz Harly', 'ADMIN', NULL, 'fritzharlydegamo@gmail.com', '2026-04-02 14:05:07', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('11', '4', 'manager3', '$2y$10$J1NxQV3FZnUEmvBP1mBlsu1MWZ4jyZXtvhk3JMdsoDmwv7vYOSfX6', NULL, NULL, 'manager 3', 'MANAGER', NULL, 'manager3@gmail.com', '2026-04-02 14:55:15', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('12', NULL, 'admin5', '$2y$10$EKFmNHCIHqhH4HRed1HseOMxSOcqcAEINImWAAULi0g1rax9NlN.C', NULL, NULL, 'admin send email', 'ADMIN', NULL, 'fritzharly4@gmail.com', '2026-04-02 15:15:31', '1');
INSERT INTO `users` (`user_id`, `tenant_id`, `username`, `password_hash`, `reset_token`, `reset_token_expiry`, `full_name`, `role`, `contact_no`, `email`, `created_at`, `is_active`) VALUES ('13', '4', 'cashier3', '$2y$10$dJJtdTLBI.ZYS3xzroEfj.gmGQTodvYONNMvn6SN9IGFjaI7a5gJO', NULL, NULL, 'Cashier 3', 'CASHIER', NULL, 'fritzharly4@gmail.com', '2026-04-02 17:55:35', '1');

SET FOREIGN_KEY_CHECKS=1;
