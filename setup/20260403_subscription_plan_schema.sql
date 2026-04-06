SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS plans (
  plan_id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(30) NOT NULL,
  name VARCHAR(120) NOT NULL,
  monthly_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  description TEXT NULL,
  max_total_users INT NOT NULL DEFAULT 0,
  max_admins INT NOT NULL DEFAULT 0,
  max_managers INT NOT NULL DEFAULT 0,
  max_credit_investigators INT NOT NULL DEFAULT 0,
  max_loan_officers INT NOT NULL DEFAULT 0,
  max_cashiers INT NOT NULL DEFAULT 0,
  feature_reports TINYINT(1) NOT NULL DEFAULT 0,
  feature_exports TINYINT(1) NOT NULL DEFAULT 0,
  feature_audit_logs TINYINT(1) NOT NULL DEFAULT 0,
  feature_logo_upload TINYINT(1) NOT NULL DEFAULT 0,
  feature_theme_customization TINYINT(1) NOT NULL DEFAULT 0,
  feature_editable_receipts TINYINT(1) NOT NULL DEFAULT 0,
  feature_editable_vouchers TINYINT(1) NOT NULL DEFAULT 0,
  feature_custom_loan_config TINYINT(1) NOT NULL DEFAULT 0,
  feature_advanced_reports TINYINT(1) NOT NULL DEFAULT 0,
  feature_priority_support TINYINT(1) NOT NULL DEFAULT 0,
  feature_system_name_customization TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_plan_code (code),
  KEY idx_plans_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @tenant_plan_id_exists = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'tenants'
    AND column_name = 'plan_id'
);

SET @tenant_plan_id_sql = IF(
  @tenant_plan_id_exists = 0,
  'ALTER TABLE tenants ADD COLUMN plan_id INT NULL AFTER plan_code',
  'SELECT 1'
);

PREPARE stmt FROM @tenant_plan_id_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tenant_subscription_status_exists = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'tenants'
    AND column_name = 'subscription_status'
);

SET @tenant_subscription_status_sql = IF(
  @tenant_subscription_status_exists = 0,
  'ALTER TABLE tenants ADD COLUMN subscription_status ENUM(''TRIAL'',''ACTIVE'',''PAST_DUE'',''SUSPENDED'',''CANCELLED'',''EXPIRED'') NOT NULL DEFAULT ''ACTIVE'' AFTER plan_id',
  'SELECT 1'
);

PREPARE stmt FROM @tenant_subscription_status_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tenant_subscription_started_exists = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'tenants'
    AND column_name = 'subscription_started_at'
);

SET @tenant_subscription_started_sql = IF(
  @tenant_subscription_started_exists = 0,
  'ALTER TABLE tenants ADD COLUMN subscription_started_at DATETIME NULL AFTER subscription_status',
  'SELECT 1'
);

PREPARE stmt FROM @tenant_subscription_started_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tenant_subscription_expires_exists = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'tenants'
    AND column_name = 'subscription_expires_at'
);

SET @tenant_subscription_expires_sql = IF(
  @tenant_subscription_expires_exists = 0,
  'ALTER TABLE tenants ADD COLUMN subscription_expires_at DATETIME NULL AFTER subscription_started_at',
  'SELECT 1'
);

PREPARE stmt FROM @tenant_subscription_expires_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tenant_plan_id_index_exists = (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'tenants'
    AND index_name = 'idx_tenants_plan_id'
);

SET @tenant_plan_id_index_sql = IF(
  @tenant_plan_id_index_exists = 0,
  'ALTER TABLE tenants ADD INDEX idx_tenants_plan_id (plan_id)',
  'SELECT 1'
);

PREPARE stmt FROM @tenant_plan_id_index_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tenant_subscription_index_exists = (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'tenants'
    AND index_name = 'idx_tenants_subscription'
);

SET @tenant_subscription_index_sql = IF(
  @tenant_subscription_index_exists = 0,
  'ALTER TABLE tenants ADD INDEX idx_tenants_subscription (subscription_status, subscription_expires_at)',
  'SELECT 1'
);

PREPARE stmt FROM @tenant_subscription_index_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @tenant_plan_fk_exists = (
  SELECT COUNT(*)
  FROM information_schema.table_constraints
  WHERE table_schema = DATABASE()
    AND table_name = 'tenants'
    AND constraint_name = 'fk_tenants_plan'
    AND constraint_type = 'FOREIGN KEY'
);

SET @tenant_plan_fk_sql = IF(
  @tenant_plan_fk_exists = 0,
  'ALTER TABLE tenants ADD CONSTRAINT fk_tenants_plan FOREIGN KEY (plan_id) REFERENCES plans(plan_id) ON DELETE SET NULL',
  'SELECT 1'
);

PREPARE stmt FROM @tenant_plan_fk_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE tenants
SET subscription_status = 'ACTIVE'
WHERE subscription_status IS NULL OR subscription_status = '';

SET FOREIGN_KEY_CHECKS=1;
