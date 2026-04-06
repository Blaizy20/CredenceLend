<?php
require_once __DIR__ . '/db.php';

function subscription_plan_definitions() {
  static $plans = null;
  if ($plans !== null) {
    return $plans;
  }

  $plans = [
    'BASIC' => [
      'code' => 'BASIC',
      'name' => 'Basic',
      'monthly_price' => 999.00,
      'description' => 'Starter plan for small lending teams with core operations only.',
      'max_total_users' => 6,
      'max_total_users_display' => '6',
      'max_admins' => 1,
      'max_managers' => 1,
      'max_credit_investigators' => 1,
      'max_loan_officers' => 2,
      'max_cashiers' => 1,
      'feature_reports' => 0,
      'feature_exports' => 0,
      'feature_audit_logs' => 0,
      'feature_logo_upload' => 0,
      'feature_theme_customization' => 0,
      'feature_editable_receipts' => 0,
      'feature_editable_vouchers' => 0,
      'feature_custom_loan_config' => 0,
      'feature_advanced_reports' => 0,
      'feature_priority_support' => 0,
      'feature_system_name_customization' => 1,
      'is_active' => 1,
    ],
    'PROFESSIONAL' => [
      'code' => 'PROFESSIONAL',
      'name' => 'Professional',
      'monthly_price' => 1499.00,
      'description' => 'Growth plan with branding, exports, and editable receipt and voucher support.',
      'max_total_users' => 15,
      'max_total_users_display' => '15',
      'max_admins' => 1,
      'max_managers' => 2,
      'max_credit_investigators' => 2,
      'max_loan_officers' => 7,
      'max_cashiers' => 3,
      'feature_reports' => 1,
      'feature_exports' => 1,
      'feature_audit_logs' => 0,
      'feature_logo_upload' => 1,
      'feature_theme_customization' => 1,
      'feature_editable_receipts' => 1,
      'feature_editable_vouchers' => 1,
      'feature_custom_loan_config' => 0,
      'feature_advanced_reports' => 0,
      'feature_priority_support' => 0,
      'feature_system_name_customization' => 1,
      'is_active' => 1,
    ],
    'ENTERPRISE' => [
      'code' => 'ENTERPRISE',
      'name' => 'Enterprise',
      'monthly_price' => 1999.00,
      'description' => 'Full feature plan with audit visibility and advanced loan configuration.',
      'max_total_users' => 30,
      'max_total_users_display' => '30',
      'max_admins' => 1,
      'max_managers' => 4,
      'max_credit_investigators' => 4,
      'max_loan_officers' => 15,
      'max_cashiers' => 6,
      'feature_reports' => 1,
      'feature_exports' => 1,
      'feature_audit_logs' => 1,
      'feature_logo_upload' => 1,
      'feature_theme_customization' => 1,
      'feature_editable_receipts' => 1,
      'feature_editable_vouchers' => 1,
      'feature_custom_loan_config' => 1,
      'feature_advanced_reports' => 1,
      'feature_priority_support' => 1,
      'feature_system_name_customization' => 1,
      'is_active' => 1,
    ],
  ];

  return $plans;
}

function subscription_billable_roles() {
  return ['ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER'];
}

function subscription_non_billable_roles() {
  return ['SUPER_ADMIN', 'TENANT', 'CUSTOMER'];
}

function subscription_billable_role_labels() {
  return [
    'ADMIN' => 'Admin (Owner)',
    'MANAGER' => 'Manager',
    'CREDIT_INVESTIGATOR' => 'Credit Investigator',
    'LOAN_OFFICER' => 'Loan Officer',
    'CASHIER' => 'Cashier',
  ];
}

function subscription_billable_role_limit_labels() {
  return [
    'ADMIN' => 'Admins',
    'MANAGER' => 'Managers',
    'CREDIT_INVESTIGATOR' => 'Credit Investigators',
    'LOAN_OFFICER' => 'Loan Officers',
    'CASHIER' => 'Cashiers',
  ];
}

function subscription_feature_map() {
  return [
    'reports' => 'feature_reports',
    'exports' => 'feature_exports',
    'audit_logs' => 'feature_audit_logs',
    'logo_upload' => 'feature_logo_upload',
    'theme_customization' => 'feature_theme_customization',
    'editable_receipts' => 'feature_editable_receipts',
    'editable_vouchers' => 'feature_editable_vouchers',
    'custom_loan_config' => 'feature_custom_loan_config',
    'advanced_reports' => 'feature_advanced_reports',
    'priority_support' => 'feature_priority_support',
    'system_name_customization' => 'feature_system_name_customization',
  ];
}

function subscription_feature_labels() {
  return [
    'feature_reports' => 'Reports',
    'feature_exports' => 'Exports',
    'feature_audit_logs' => 'Audit Logs',
    'feature_logo_upload' => 'Logo Upload',
    'feature_theme_customization' => 'Theme Customization',
    'feature_editable_receipts' => 'Editable Receipts',
    'feature_editable_vouchers' => 'Editable Vouchers',
    'feature_custom_loan_config' => 'Custom Loan Configuration',
    'feature_advanced_reports' => 'Advanced Reports',
    'feature_priority_support' => 'Priority Support',
    'feature_system_name_customization' => 'System Name Customization',
  ];
}

function subscription_feature_label($feature_key) {
  $feature_key = normalize_subscription_feature_key($feature_key);
  $feature_map = subscription_feature_map();
  $feature_labels = subscription_feature_labels();
  $column = $feature_map[$feature_key] ?? null;
  if ($column === null) {
    return ucwords(str_replace('_', ' ', $feature_key));
  }

  return $feature_labels[$column] ?? ucwords(str_replace('_', ' ', $feature_key));
}

function subscription_role_limit_map() {
  return [
    'ADMIN' => 'max_admins',
    'MANAGER' => 'max_managers',
    'CREDIT_INVESTIGATOR' => 'max_credit_investigators',
    'LOAN_OFFICER' => 'max_loan_officers',
    'CASHIER' => 'max_cashiers',
  ];
}

function subscription_status_labels() {
  return [
    'TRIAL' => 'Trial',
    'ACTIVE' => 'Active',
    'PENDING' => 'Pending',
    'PAST_DUE' => 'Past Due',
    'SUSPENDED' => 'Suspended',
    'CANCELLED' => 'Cancelled',
    'EXPIRED' => 'Expired',
    'MISSING' => 'Unavailable',
  ];
}

function subscription_manageable_status_options() {
  $labels = subscription_status_labels();

  return [
    'TRIAL' => $labels['TRIAL'],
    'ACTIVE' => $labels['ACTIVE'],
    'PAST_DUE' => $labels['PAST_DUE'],
    'SUSPENDED' => $labels['SUSPENDED'],
    'CANCELLED' => $labels['CANCELLED'],
    'EXPIRED' => $labels['EXPIRED'],
  ];
}

function normalize_subscription_plan_code($plan_code, $default = 'BASIC') {
  $plan_code = strtoupper(trim((string) $plan_code));
  $plans = subscription_plan_definitions();

  if ($plan_code === '' || !isset($plans[$plan_code])) {
    return $default;
  }

  return $plan_code;
}

function normalize_subscription_feature_key($feature_key) {
  $feature_key = strtolower(trim((string) $feature_key));
  if ($feature_key === '') {
    return '';
  }

  $feature_key = str_replace(['-', ' '], '_', $feature_key);
  if (strpos($feature_key, 'feature_') === 0) {
    $feature_key = substr($feature_key, 8);
  }

  return $feature_key;
}

function normalize_subscription_role($role) {
  $role = strtoupper(trim((string) $role));
  if ($role === '') {
    return '';
  }

  return str_replace([' ', '-'], '_', $role);
}

function subscription_role_is_billable($role) {
  return in_array(normalize_subscription_role($role), subscription_billable_roles(), true);
}

function subscription_limit_is_unlimited($limit_value) {
  return intval($limit_value ?? 0) <= 0;
}

function subscription_plan_limit_label($plan, $limit_key, $unlimited_label = 'Unlimited') {
  $display_key = $limit_key . '_display';
  $display_value = trim((string) ($plan[$display_key] ?? ''));
  if ($display_value !== '') {
    return $display_value;
  }

  $limit_value = intval($plan[$limit_key] ?? 0);
  if ($limit_value <= 0) {
    return $unlimited_label;
  }

  return number_format($limit_value);
}

function subscription_plan_name($plan) {
  $plan_name = trim((string) ($plan['name'] ?? $plan['code'] ?? $plan['tenant_plan_code'] ?? ''));
  return $plan_name !== '' ? $plan_name : 'current';
}

function subscription_lock_tenant_scope($tenant_id) {
  $tenant_id = intval($tenant_id ?? 0);
  if ($tenant_id <= 0) {
    throw new RuntimeException('Invalid tenant.');
  }

  $tenant = fetch_one(q(
    "SELECT tenant_id
     FROM tenants
     WHERE tenant_id=?
     LIMIT 1
     FOR UPDATE",
    "i",
    [$tenant_id]
  ));

  if (!$tenant) {
    throw new RuntimeException('Tenant not found.');
  }

  return $tenant;
}

function subscription_schema_ready() {
  static $ready = null;
  if ($ready !== null) {
    return $ready;
  }

  try {
    $plans_table = fetch_one(q(
      "SELECT 1 AS ok
       FROM information_schema.TABLES
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'plans'
       LIMIT 1"
    ));

    $plan_id_column = fetch_one(q(
      "SELECT 1 AS ok
       FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'tenants'
         AND COLUMN_NAME = 'plan_id'
       LIMIT 1"
    ));

    $subscription_status_column = fetch_one(q(
      "SELECT 1 AS ok
       FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'tenants'
         AND COLUMN_NAME = 'subscription_status'
       LIMIT 1"
    ));

    $subscription_expires_column = fetch_one(q(
      "SELECT 1 AS ok
       FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME = 'tenants'
         AND COLUMN_NAME = 'subscription_expires_at'
       LIMIT 1"
    ));

    $ready = !empty($plans_table) && !empty($plan_id_column) && !empty($subscription_status_column) && !empty($subscription_expires_column);
  } catch (Exception $e) {
    error_log("Subscription schema readiness error: " . $e->getMessage());
    $ready = false;
  }

  return $ready;
}

function subscription_plan_from_definition($plan_code) {
  $plans = subscription_plan_definitions();
  $plan_code = normalize_subscription_plan_code($plan_code);

  $plan = $plans[$plan_code] ?? $plans['BASIC'];
  $plan['plan_id'] = null;
  $plan['plan_source'] = 'fallback';
  return $plan;
}

function get_tenant_plan($tenant_id) {
  static $cache = [];

  $tenant_id = intval($tenant_id ?? 0);
  if ($tenant_id <= 0) {
    return null;
  }

  if (isset($cache[$tenant_id])) {
    return $cache[$tenant_id];
  }

  try {
    if (subscription_schema_ready()) {
      $tenant = fetch_one(q(
        "SELECT
           t.tenant_id,
           t.tenant_name,
           t.display_name,
           t.tenant_status,
           t.plan_id AS tenant_plan_id,
           t.plan_code AS tenant_plan_code,
           t.subscription_status,
           t.subscription_started_at,
           t.subscription_expires_at,
           p.plan_id,
           p.code,
           p.name,
           p.monthly_price,
           p.description,
           p.max_total_users,
           p.max_admins,
           p.max_managers,
           p.max_credit_investigators,
           p.max_loan_officers,
           p.max_cashiers,
           p.feature_reports,
           p.feature_exports,
           p.feature_audit_logs,
           p.feature_logo_upload,
           p.feature_theme_customization,
           p.feature_editable_receipts,
           p.feature_editable_vouchers,
           p.feature_custom_loan_config,
           p.feature_advanced_reports,
           p.feature_priority_support,
           p.feature_system_name_customization,
           p.is_active AS plan_is_active
         FROM tenants t
         LEFT JOIN plans p ON p.plan_id = t.plan_id
         WHERE t.tenant_id = ?
         LIMIT 1",
        "i",
        [$tenant_id]
      ));
    } else {
      $tenant = fetch_one(q(
        "SELECT
           tenant_id,
           tenant_name,
           display_name,
           tenant_status,
           plan_code AS tenant_plan_code
         FROM tenants
         WHERE tenant_id = ?
         LIMIT 1",
        "i",
        [$tenant_id]
      ));
    }
  } catch (Exception $e) {
    error_log("Get tenant plan error: " . $e->getMessage());
    return null;
  }

  if (!$tenant) {
    return null;
  }

  $resolved_plan = null;
  $plan_code = strtoupper(trim((string) ($tenant['code'] ?? $tenant['tenant_plan_code'] ?? 'BASIC')));

  if (!empty($tenant['code'])) {
    $resolved_plan = [
      'plan_id' => intval($tenant['plan_id'] ?? 0) ?: null,
      'code' => $plan_code,
      'name' => trim((string) ($tenant['name'] ?? $plan_code)),
      'monthly_price' => floatval($tenant['monthly_price'] ?? 0),
      'description' => $tenant['description'] ?? null,
      'max_total_users' => intval($tenant['max_total_users'] ?? 0),
      'max_admins' => intval($tenant['max_admins'] ?? 0),
      'max_managers' => intval($tenant['max_managers'] ?? 0),
      'max_credit_investigators' => intval($tenant['max_credit_investigators'] ?? 0),
      'max_loan_officers' => intval($tenant['max_loan_officers'] ?? 0),
      'max_cashiers' => intval($tenant['max_cashiers'] ?? 0),
      'feature_reports' => intval($tenant['feature_reports'] ?? 0),
      'feature_exports' => intval($tenant['feature_exports'] ?? 0),
      'feature_audit_logs' => intval($tenant['feature_audit_logs'] ?? 0),
      'feature_logo_upload' => intval($tenant['feature_logo_upload'] ?? 0),
      'feature_theme_customization' => intval($tenant['feature_theme_customization'] ?? 0),
      'feature_editable_receipts' => intval($tenant['feature_editable_receipts'] ?? 0),
      'feature_editable_vouchers' => intval($tenant['feature_editable_vouchers'] ?? 0),
      'feature_custom_loan_config' => intval($tenant['feature_custom_loan_config'] ?? 0),
      'feature_advanced_reports' => intval($tenant['feature_advanced_reports'] ?? 0),
      'feature_priority_support' => intval($tenant['feature_priority_support'] ?? 0),
      'feature_system_name_customization' => intval($tenant['feature_system_name_customization'] ?? 0),
      'is_active' => intval($tenant['plan_is_active'] ?? 0),
      'plan_source' => 'database',
    ];
  } else {
    $resolved_plan = subscription_plan_from_definition($plan_code);
  }

  $fallback_plan = subscription_plan_from_definition($plan_code);
  if (!isset($resolved_plan['max_total_users_display']) && isset($fallback_plan['max_total_users_display'])) {
    $resolved_plan['max_total_users_display'] = $fallback_plan['max_total_users_display'];
  }

  $resolved_plan['tenant_id'] = intval($tenant['tenant_id']);
  $resolved_plan['tenant_name'] = trim((string) ($tenant['tenant_name'] ?? ''));
  $resolved_plan['display_name'] = trim((string) ($tenant['display_name'] ?? ''));
  $resolved_plan['tenant_status'] = strtoupper(trim((string) ($tenant['tenant_status'] ?? 'PENDING')));
  $resolved_plan['tenant_plan_id'] = intval($tenant['tenant_plan_id'] ?? $tenant['plan_id'] ?? 0) ?: null;
  $resolved_plan['tenant_plan_code'] = strtoupper(trim((string) ($tenant['tenant_plan_code'] ?? $plan_code)));
  $resolved_plan['subscription_status'] = strtoupper(trim((string) ($tenant['subscription_status'] ?? 'ACTIVE')));
  $resolved_plan['subscription_started_at'] = $tenant['subscription_started_at'] ?? null;
  $resolved_plan['subscription_expires_at'] = $tenant['subscription_expires_at'] ?? null;

  $cache[$tenant_id] = $resolved_plan;
  return $cache[$tenant_id];
}

function get_tenant_subscription_status($tenant_id) {
  $plan = get_tenant_plan($tenant_id);
  $labels = subscription_status_labels();

  if (!$plan) {
    return [
      'tenant_id' => intval($tenant_id ?? 0),
      'status' => 'MISSING',
      'label' => $labels['MISSING'],
      'message' => 'Tenant plan or subscription information is unavailable.',
      'is_active' => false,
      'is_trial' => false,
      'is_pending' => false,
      'is_expired' => true,
      'is_suspended' => false,
      'expires_at' => null,
    ];
  }

  $tenant_status = strtoupper(trim((string) ($plan['tenant_status'] ?? 'PENDING')));
  $status = strtoupper(trim((string) ($plan['subscription_status'] ?? 'ACTIVE')));
  $expires_at = $plan['subscription_expires_at'] ?? null;
  $expires_ts = $expires_at ? strtotime($expires_at) : false;

  if ($tenant_status === 'PENDING') {
    $status = 'PENDING';
  } elseif ($tenant_status === 'SUSPENDED') {
    $status = 'SUSPENDED';
  } elseif ($expires_ts && $expires_ts < time() && in_array($status, ['ACTIVE', 'TRIAL', 'PAST_DUE'], true)) {
    $status = 'EXPIRED';
  }

  if (!isset($labels[$status])) {
    $status = 'MISSING';
  }

  $is_trial = ($status === 'TRIAL');
  $is_pending = ($status === 'PENDING');
  $is_suspended = ($status === 'SUSPENDED');
  $is_expired = in_array($status, ['EXPIRED', 'CANCELLED'], true);
  $is_active = in_array($status, ['ACTIVE', 'TRIAL'], true);

  $message_map = [
    'TRIAL' => 'Subscription trial is active.',
    'ACTIVE' => 'Subscription is active.',
    'PENDING' => 'Tenant setup or subscription approval is still pending.',
    'PAST_DUE' => 'Subscription is past due.',
    'SUSPENDED' => 'Subscription is suspended.',
    'CANCELLED' => 'Subscription has been cancelled.',
    'EXPIRED' => 'Subscription has expired.',
    'MISSING' => 'Subscription information is unavailable.',
  ];

  return [
    'tenant_id' => intval($plan['tenant_id'] ?? 0),
    'status' => $status,
    'label' => $labels[$status],
    'message' => $message_map[$status] ?? $message_map['MISSING'],
    'is_active' => $is_active,
    'is_trial' => $is_trial,
    'is_pending' => $is_pending,
    'is_expired' => $is_expired,
    'is_suspended' => $is_suspended,
    'expires_at' => $expires_at,
  ];
}

function tenant_subscription_is_active($tenant_id) {
  $status = get_tenant_subscription_status($tenant_id);
  return !empty($status['is_active']);
}

function tenant_has_feature($tenant_id, $feature_key) {
  $access = get_tenant_feature_access($tenant_id, $feature_key);
  return !empty($access['allowed']);
}

function get_tenant_feature_access($tenant_id, $feature_key) {
  $tenant_id = intval($tenant_id ?? 0);
  $feature_key = normalize_subscription_feature_key($feature_key);
  $feature_label = subscription_feature_label($feature_key);
  $feature_map = subscription_feature_map();
  $column = $feature_map[$feature_key] ?? null;

  if ($tenant_id <= 0) {
    return [
      'allowed' => false,
      'tenant_id' => 0,
      'feature_key' => $feature_key,
      'feature_label' => $feature_label,
      'plan_code' => '',
      'plan_name' => '',
      'message' => 'Tenant context is required to access ' . $feature_label . '.',
    ];
  }

  if ($column === null) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'feature_key' => $feature_key,
      'feature_label' => $feature_label,
      'plan_code' => '',
      'plan_name' => '',
      'message' => $feature_label . ' is not recognized by subscription enforcement.',
    ];
  }

  $plan = get_tenant_plan($tenant_id);
  $subscription = get_tenant_subscription_status($tenant_id);
  if (!$plan) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'feature_key' => $feature_key,
      'feature_label' => $feature_label,
      'plan_code' => '',
      'plan_name' => '',
      'message' => 'Tenant plan information is unavailable.',
    ];
  }

  $plan_name = subscription_plan_name($plan);
  $plan_code = strtoupper(trim((string) ($plan['code'] ?? $plan['tenant_plan_code'] ?? '')));

  if (!$subscription['is_active']) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'feature_key' => $feature_key,
      'feature_label' => $feature_label,
      'plan_code' => $plan_code,
      'plan_name' => $plan_name,
      'message' => $feature_label . ' is unavailable because this tenant subscription is ' . $subscription['label'] . '.',
    ];
  }

  $allowed = !empty($plan[$column]);

  return [
    'allowed' => $allowed,
    'tenant_id' => $tenant_id,
    'feature_key' => $feature_key,
    'feature_label' => $feature_label,
    'plan_code' => $plan_code,
    'plan_name' => $plan_name,
    'message' => $allowed
      ? $feature_label . ' is available on the ' . $plan_name . ' plan.'
      : $feature_label . ' is not available on the ' . $plan_name . ' plan for this tenant.',
  ];
}

function get_tenant_user_counts($tenant_id) {
  static $cache = [];

  $tenant_id = intval($tenant_id ?? 0);
  if ($tenant_id <= 0) {
    return [
      'tenant_id' => 0,
      'total_users' => 0,
      'counted_roles' => subscription_billable_roles(),
      'excluded_roles' => subscription_non_billable_roles(),
      'by_role' => [
        'ADMIN' => 0,
        'MANAGER' => 0,
        'CREDIT_INVESTIGATOR' => 0,
        'LOAN_OFFICER' => 0,
        'CASHIER' => 0,
      ],
    ];
  }

  if (isset($cache[$tenant_id])) {
    return $cache[$tenant_id];
  }

  $counts = [
    'tenant_id' => $tenant_id,
    'total_users' => 0,
    'counted_roles' => subscription_billable_roles(),
    'excluded_roles' => subscription_non_billable_roles(),
    'by_role' => [
      'ADMIN' => 0,
      'MANAGER' => 0,
      'CREDIT_INVESTIGATOR' => 0,
      'LOAN_OFFICER' => 0,
      'CASHIER' => 0,
    ],
  ];

  try {
    $role_rows = fetch_all(q(
      "SELECT scoped_users.normalized_role AS role, COUNT(*) AS role_count
       FROM (
         SELECT u.user_id, 'ADMIN' AS normalized_role
         FROM tenant_admins ta
         JOIN users u ON u.user_id = ta.user_id
         WHERE ta.tenant_id = ?
           AND u.is_active = 1
           AND u.role = 'ADMIN'

         UNION

         SELECT u.user_id, u.role AS normalized_role
         FROM users u
         WHERE u.tenant_id = ?
           AND u.is_active = 1
           AND u.role IN ('ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER')
       ) AS scoped_users
       GROUP BY scoped_users.normalized_role",
      "ii",
      [$tenant_id, $tenant_id]
    ));

    foreach ($role_rows as $row) {
      $role = normalize_subscription_role($row['role'] ?? '');
      if (isset($counts['by_role'][$role])) {
        $counts['by_role'][$role] = intval($row['role_count'] ?? 0);
      }
    }

    $total_row = fetch_one(q(
      "SELECT COUNT(DISTINCT scoped_users.user_id) AS total_users
       FROM (
         SELECT u.user_id
         FROM tenant_admins ta
         JOIN users u ON u.user_id = ta.user_id
         WHERE ta.tenant_id = ?
           AND u.is_active = 1
           AND u.role = 'ADMIN'

         UNION

         SELECT u.user_id
         FROM users u
         WHERE u.tenant_id = ?
           AND u.is_active = 1
           AND u.role IN ('ADMIN', 'MANAGER', 'CREDIT_INVESTIGATOR', 'LOAN_OFFICER', 'CASHIER')
       ) AS scoped_users",
      "ii",
      [$tenant_id, $tenant_id]
    ));

    $counts['total_users'] = intval($total_row['total_users'] ?? 0);
  } catch (Exception $e) {
    error_log("Tenant user counts error: " . $e->getMessage());
  }

  $cache[$tenant_id] = $counts;
  return $cache[$tenant_id];
}

function get_tenant_total_user_count($tenant_id) {
  $counts = get_tenant_user_counts($tenant_id);
  return intval($counts['total_users'] ?? 0);
}

function get_tenant_plan_human_info($tenant_id) {
  $plan = get_tenant_plan($tenant_id);
  $subscription = get_tenant_subscription_status($tenant_id);
  $counts = get_tenant_user_counts($tenant_id);
  if (!$plan) {
    return [
      'tenant_id' => intval($tenant_id ?? 0),
      'plan_code' => '',
      'plan_name' => 'Unknown Plan',
      'plan_label' => 'Unknown Plan',
      'price_label' => 'Plan unavailable',
      'subscription_status' => $subscription['status'],
      'subscription_status_label' => $subscription['label'],
      'summary' => 'Plan information is unavailable.',
      'usage_summary' => 'No billable seat information is available.',
      'counted_roles' => subscription_billable_role_labels(),
      'role_counts' => [],
      'enabled_features' => [],
    ];
  }

  $price_label = 'PHP ' . number_format(floatval($plan['monthly_price'] ?? 0), 2) . '/month';
  $total_users = intval($counts['total_users'] ?? 0);
  $max_total_users = intval($plan['max_total_users'] ?? 0);
  $max_total_users_label = subscription_plan_limit_label($plan, 'max_total_users', '20+');
  $feature_labels = subscription_feature_labels();
  $enabled_features = [];

  foreach ($feature_labels as $column => $label) {
    if (!empty($plan[$column])) {
      $enabled_features[] = $label;
    }
  }

  return [
    'tenant_id' => intval($plan['tenant_id'] ?? 0),
    'plan_code' => strtoupper(trim((string) ($plan['code'] ?? $plan['tenant_plan_code'] ?? ''))),
    'plan_name' => trim((string) ($plan['name'] ?? 'Unknown')),
    'plan_label' => trim((string) ($plan['name'] ?? 'Unknown')) . ' Plan',
    'price_label' => $price_label,
    'subscription_status' => $subscription['status'],
    'subscription_status_label' => $subscription['label'],
    'summary' => trim((string) ($plan['name'] ?? 'Unknown')) . ' Plan (' . $price_label . ')',
    'usage_summary' => subscription_limit_is_unlimited($max_total_users)
      ? $total_users . ' billable seats in use (plan total: ' . $max_total_users_label . ')'
      : $total_users . ' of ' . $max_total_users_label . ' billable seats in use',
    'counted_roles' => subscription_billable_role_labels(),
    'role_counts' => $counts['by_role'],
    'enabled_features' => $enabled_features,
  ];
}

function can_add_more_users($tenant_id) {
  $tenant_id = intval($tenant_id ?? 0);
  $plan = get_tenant_plan($tenant_id);
  $subscription = get_tenant_subscription_status($tenant_id);
  $counts = get_tenant_user_counts($tenant_id);

  if (!$plan) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'current_count' => 0,
      'max_total_users' => 0,
      'remaining_slots' => 0,
      'message' => 'Tenant plan information is unavailable.',
    ];
  }

  $current_count = intval($counts['total_users'] ?? 0);
  $max_total_users = intval($plan['max_total_users'] ?? 0);
  $remaining_slots = subscription_limit_is_unlimited($max_total_users)
    ? null
    : max(0, $max_total_users - $current_count);

  if (!$subscription['is_active']) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'current_count' => $current_count,
      'max_total_users' => $max_total_users,
      'remaining_slots' => $remaining_slots,
      'message' => 'This tenant cannot add billable staff because its subscription is ' . $subscription['label'] . '.',
    ];
  }

  $plan_name = subscription_plan_name($plan);
  $allowed = subscription_limit_is_unlimited($max_total_users) ? true : ($current_count < $max_total_users);

  return [
    'allowed' => $allowed,
    'tenant_id' => $tenant_id,
    'current_count' => $current_count,
    'max_total_users' => $max_total_users,
    'remaining_slots' => $remaining_slots,
    'max_total_users_label' => subscription_plan_limit_label($plan, 'max_total_users', '20+'),
    'message' => $allowed
      ? 'A billable seat is available under the ' . $plan_name . ' plan.'
      : 'This tenant has reached the maximum number of billable users allowed by the ' . $plan_name . ' plan.',
  ];
}

function can_assign_user_to_role($tenant_id, $new_role, $current_role = null, $current_is_active = false) {
  $tenant_id = intval($tenant_id ?? 0);
  $new_role = normalize_subscription_role($new_role);
  $current_role = normalize_subscription_role($current_role);
  $current_is_active = !empty($current_is_active);

  if (!subscription_role_is_billable($new_role)) {
    return [
      'allowed' => true,
      'tenant_id' => $tenant_id,
      'role' => $new_role,
      'current_count' => 0,
      'max_allowed' => null,
      'remaining_slots' => null,
      'message' => 'Role is not billable for subscription enforcement.',
    ];
  }

  $role_limit_map = subscription_role_limit_map();
  $limit_column = $role_limit_map[$new_role] ?? null;

  if ($limit_column === null) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'role' => $new_role,
      'current_count' => 0,
      'max_allowed' => 0,
      'remaining_slots' => 0,
      'message' => 'Unsupported role for subscription limits.',
    ];
  }

  $subscription = get_tenant_subscription_status($tenant_id);
  if (!$subscription['is_active']) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'role' => $new_role,
      'current_count' => 0,
      'max_allowed' => 0,
      'remaining_slots' => 0,
      'message' => 'This tenant cannot assign billable staff because its subscription is ' . $subscription['label'] . '.',
    ];
  }

  $plan = get_tenant_plan($tenant_id);
  $counts = get_tenant_user_counts($tenant_id);
  if (!$plan) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'role' => $new_role,
      'current_count' => 0,
      'max_allowed' => 0,
      'remaining_slots' => 0,
      'message' => 'Tenant plan information is unavailable.',
    ];
  }

  $plan_name = subscription_plan_name($plan);
  $current_billable = $current_is_active && subscription_role_is_billable($current_role);
  $current_total_count = intval($counts['total_users'] ?? 0);
  $adjusted_total_count = $current_total_count - ($current_billable ? 1 : 0);
  $total_limit = intval($plan['max_total_users'] ?? 0);

  if (!subscription_limit_is_unlimited($total_limit) && $adjusted_total_count >= $total_limit) {
    return [
      'allowed' => false,
      'tenant_id' => $tenant_id,
      'role' => $new_role,
      'current_count' => intval($counts['by_role'][$new_role] ?? 0),
      'max_allowed' => intval($plan[$limit_column] ?? 0),
      'remaining_slots' => 0,
      'message' => 'This tenant has reached the maximum number of billable users allowed by the ' . $plan_name . ' plan.',
    ];
  }

  $current_count = intval($counts['by_role'][$new_role] ?? 0);
  if ($current_billable && $current_role === $new_role) {
    $current_count = max(0, $current_count - 1);
  }

  $max_allowed = intval($plan[$limit_column] ?? 0);
  $remaining_slots = subscription_limit_is_unlimited($max_allowed)
    ? null
    : max(0, $max_allowed - $current_count);
  $allowed = subscription_limit_is_unlimited($max_allowed) ? true : ($current_count < $max_allowed);
  $role_labels = subscription_billable_role_labels();
  $role_label = $role_labels[$new_role] ?? str_replace('_', ' ', $new_role);
  $role_limit_labels = subscription_billable_role_limit_labels();
  $role_limit_label = $role_limit_labels[$new_role] ?? $role_label;

  return [
    'allowed' => $allowed,
    'tenant_id' => $tenant_id,
    'role' => $new_role,
    'current_count' => $current_count,
    'max_allowed' => $max_allowed,
    'remaining_slots' => $remaining_slots,
    'message' => $allowed
      ? 'A ' . $role_label . ' seat is available under the ' . $plan_name . ' plan.'
      : 'This tenant has reached the maximum number of ' . $role_limit_label . ' allowed by the ' . $plan_name . ' plan.',
  ];
}

function can_add_user_to_role($tenant_id, $role) {
  return can_assign_user_to_role($tenant_id, $role, null, false);
}

function subscription_available_plan_options($active_only = true) {
  static $cache = [];

  $cache_key = $active_only ? 'active_only' : 'all';
  if (isset($cache[$cache_key])) {
    return $cache[$cache_key];
  }

  $plans = [];

  if (subscription_schema_ready()) {
    try {
      $sql = "SELECT plan_id, UPPER(code) AS code, name, is_active
              FROM plans";
      if ($active_only) {
        $sql .= " WHERE is_active = 1";
      }
      $sql .= " ORDER BY FIELD(UPPER(code), 'BASIC', 'PROFESSIONAL', 'ENTERPRISE'), name ASC";

      $rows = fetch_all(q($sql));
      foreach ($rows as $row) {
        $code = strtoupper(trim((string) ($row['code'] ?? '')));
        if ($code === '') {
          continue;
        }

        $fallback = subscription_plan_from_definition($code);
        $plans[$code] = array_merge($fallback, [
          'plan_id' => intval($row['plan_id'] ?? 0) ?: null,
          'code' => $code,
          'name' => trim((string) ($row['name'] ?? ($fallback['name'] ?? $code))),
          'is_active' => intval($row['is_active'] ?? 0),
          'plan_source' => 'database',
        ]);
      }
    } catch (Exception $e) {
      error_log("Subscription plan options error: " . $e->getMessage());
    }
  }

  if (empty($plans)) {
    foreach (subscription_plan_definitions() as $code => $definition) {
      if ($active_only && empty($definition['is_active'])) {
        continue;
      }

      $plans[$code] = $definition;
    }
  }

  $cache[$cache_key] = $plans;
  return $cache[$cache_key];
}

function subscription_log_tenant_activity($tenant_id, $action, $description, $reference_no = null) {
  $tenant_id = intval($tenant_id ?? 0);
  if ($tenant_id <= 0) {
    return;
  }

  if (function_exists('log_tenant_activity')) {
    log_tenant_activity($tenant_id, $action, $description, $reference_no);
    return;
  }

  if (function_exists('log_activity_for_tenant')) {
    log_activity_for_tenant($tenant_id, $action, $description, null, null, $reference_no);
    return;
  }

  $user_id = intval($_SESSION['user_id'] ?? 0);
  $user_role = trim((string) ($_SESSION['role'] ?? 'UNKNOWN'));

  try {
    q(
      "INSERT INTO activity_logs (tenant_id, user_id, user_role, action, description, loan_id, customer_id, reference_no)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
      "iisssiis",
      [$tenant_id, $user_id, $user_role, $action, $description, null, null, $reference_no]
    );
  } catch (Exception $e) {
    error_log("Subscription tenant activity log error: " . $e->getMessage());
  }
}

function update_tenant_subscription_settings($tenant_id, $plan_code, $subscription_status) {
  if (!subscription_schema_ready()) {
    throw new RuntimeException('Subscription schema is not ready. Run the subscription plan migration before using subscription management.');
  }

  $tenant_id = intval($tenant_id ?? 0);
  if ($tenant_id <= 0) {
    throw new RuntimeException('Invalid tenant.');
  }

  $plan_code = normalize_subscription_plan_code($plan_code);
  $subscription_status = strtoupper(trim((string) $subscription_status));
  $status_options = subscription_manageable_status_options();
  if (!isset($status_options[$subscription_status])) {
    throw new RuntimeException('Invalid subscription status.');
  }

  $plan_options = subscription_available_plan_options(false);
  $selected_plan = $plan_options[$plan_code] ?? null;
  if (!$selected_plan) {
    throw new RuntimeException('Invalid subscription plan.');
  }

  $conn = db();
  $started_at = null;

  try {
    $conn->begin_transaction();
    subscription_lock_tenant_scope($tenant_id);

    $tenant = fetch_one(q(
      "SELECT
         tenant_id,
         tenant_name,
         COALESCE(display_name, tenant_name) AS display_name,
         subdomain,
         plan_code,
         subscription_status,
         subscription_started_at
       FROM tenants
       WHERE tenant_id = ?
       LIMIT 1",
      "i",
      [$tenant_id]
    ));

    if (!$tenant) {
      throw new RuntimeException('Tenant not found.');
    }

    $current_plan_code = normalize_subscription_plan_code($tenant['plan_code'] ?? 'BASIC');
    $current_status = strtoupper(trim((string) ($tenant['subscription_status'] ?? 'ACTIVE')));
    $started_at = $tenant['subscription_started_at'] ?? null;

    if ($started_at === null && in_array($subscription_status, ['TRIAL', 'ACTIVE', 'PAST_DUE'], true)) {
      $started_at = date('Y-m-d H:i:s');
    }

    q(
      "UPDATE tenants
       SET plan_code = ?,
           plan_id = ?,
           subscription_status = ?,
           subscription_started_at = ?,
           updated_at = CURRENT_TIMESTAMP
       WHERE tenant_id = ?",
      "sissi",
      [
        $plan_code,
        intval($selected_plan['plan_id'] ?? 0) ?: null,
        $subscription_status,
        $started_at,
        $tenant_id,
      ]
    );

    $conn->commit();

    $status_labels = subscription_status_labels();
    $current_plan_name = subscription_plan_name(subscription_plan_from_definition($current_plan_code));
    $next_plan_name = trim((string) ($selected_plan['name'] ?? $plan_code));
    $changes = [];

    if ($current_plan_code !== $plan_code) {
      $changes[] = 'Plan: ' . $current_plan_name . ' -> ' . $next_plan_name;
    }
    if ($current_status !== $subscription_status) {
      $changes[] = 'Status: ' . ($status_labels[$current_status] ?? $current_status) . ' -> ' . ($status_labels[$subscription_status] ?? $subscription_status);
    }
    if (empty($changes)) {
      $changes[] = 'Plan and status were re-saved with no value change';
    }

    subscription_log_tenant_activity(
      $tenant_id,
      'TENANT_SUBSCRIPTION_UPDATED',
      sprintf(
        'Tenant subscription updated for %s (%s). %s.',
        $tenant['display_name'],
        $tenant['subdomain'],
        implode('; ', $changes)
      )
    );

    return [
      'tenant_id' => $tenant_id,
      'tenant_name' => $tenant['display_name'],
      'plan_code' => $plan_code,
      'plan_name' => $next_plan_name,
      'subscription_status' => $subscription_status,
      'subscription_status_label' => $status_labels[$subscription_status] ?? $subscription_status,
    ];
  } catch (Exception $e) {
    try {
      $conn->rollback();
    } catch (Exception $rollback_exception) {
    }
    throw $e;
  }
}
?>
