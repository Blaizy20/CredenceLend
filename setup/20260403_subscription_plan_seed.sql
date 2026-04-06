SET NAMES utf8mb4;

INSERT INTO plans (
  code,
  name,
  monthly_price,
  description,
  max_total_users,
  max_admins,
  max_managers,
  max_credit_investigators,
  max_loan_officers,
  max_cashiers,
  feature_reports,
  feature_exports,
  feature_audit_logs,
  feature_logo_upload,
  feature_theme_customization,
  feature_editable_receipts,
  feature_editable_vouchers,
  feature_custom_loan_config,
  feature_advanced_reports,
  feature_priority_support,
  feature_system_name_customization,
  is_active
) VALUES
(
  'BASIC',
  'Basic',
  999.00,
  'Starter plan for small lending teams with core operations only.',
  6,
  1,
  1,
  1,
  2,
  1,
  0,
  0,
  0,
  0,
  0,
  0,
  0,
  0,
  0,
  0,
  0,
  1,
  1
),
(
  'PROFESSIONAL',
  'Professional',
  1499.00,
  'Growth plan with branding, exports, and editable receipt and voucher support.',
  15,
  1,
  2,
  2,
  7,
  3,
  1,
  1,
  0,
  0,
  1,
  1,
  1,
  1,
  0,
  0,
  0,
  1,
  1
),
(
  'ENTERPRISE',
  'Enterprise',
  1999.00,
  'Full feature plan with audit visibility and advanced loan configuration.',
  30,
  1,
  4,
  4,
  15,
  6,
  1,
  1,
  1,
  1,
  1,
  1,
  1,
  1,
  1,
  1,
  1,
  1,
  1
)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  monthly_price = VALUES(monthly_price),
  description = VALUES(description),
  max_total_users = VALUES(max_total_users),
  max_admins = VALUES(max_admins),
  max_managers = VALUES(max_managers),
  max_credit_investigators = VALUES(max_credit_investigators),
  max_loan_officers = VALUES(max_loan_officers),
  max_cashiers = VALUES(max_cashiers),
  feature_reports = VALUES(feature_reports),
  feature_exports = VALUES(feature_exports),
  feature_audit_logs = VALUES(feature_audit_logs),
  feature_logo_upload = VALUES(feature_logo_upload),
  feature_theme_customization = VALUES(feature_theme_customization),
  feature_editable_receipts = VALUES(feature_editable_receipts),
  feature_editable_vouchers = VALUES(feature_editable_vouchers),
  feature_custom_loan_config = VALUES(feature_custom_loan_config),
  feature_advanced_reports = VALUES(feature_advanced_reports),
  feature_priority_support = VALUES(feature_priority_support),
  feature_system_name_customization = VALUES(feature_system_name_customization),
  is_active = VALUES(is_active),
  updated_at = CURRENT_TIMESTAMP;

UPDATE tenants t
JOIN plans p ON p.code = t.plan_code
SET t.plan_id = p.plan_id
WHERE t.plan_id IS NULL;

UPDATE tenants
SET subscription_status = 'ACTIVE'
WHERE subscription_status IS NULL OR subscription_status = '';

UPDATE tenants
SET subscription_started_at = COALESCE(subscription_started_at, created_at)
WHERE plan_id IS NOT NULL;
