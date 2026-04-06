# Subscription Plan Helper Changes

This document summarizes the reusable backend helpers added for subscription-plan enforcement.

## 1. Files added or modified

Added:
- `includes/subscription_helpers.php`
- `docs/subscription-plan-helper-changes.md`

Modified:
- `includes/auth.php`
- `includes/loan_helpers.php`

## 2. New helper functions

Core plan helpers:
- `get_tenant_plan($tenant_id)`
- `get_tenant_subscription_status($tenant_id)`
- `tenant_subscription_is_active($tenant_id)`
- `tenant_has_feature($tenant_id, $feature_key)`

Seat and role limit helpers:
- `get_tenant_user_counts($tenant_id)`
- `get_tenant_total_user_count($tenant_id)`
- `can_add_more_users($tenant_id)`
- `can_add_user_to_role($tenant_id, $role)`

UI-facing helper:
- `get_tenant_plan_human_info($tenant_id)`

Supporting helpers:
- `subscription_plan_definitions()`
- `subscription_feature_map()`
- `subscription_feature_labels()`
- `subscription_role_limit_map()`
- `subscription_status_labels()`
- `normalize_subscription_feature_key($feature_key)`
- `normalize_subscription_role($role)`
- `subscription_schema_ready()`

## 3. Example usage snippets

Check a feature before loading a premium report:

```php
$tenant_id = require_current_tenant_id();
if (!tenant_has_feature($tenant_id, 'advanced_reports')) {
  http_response_code(403);
  exit('Advanced reports are not enabled for this plan.');
}
```

Check total seat capacity before creating staff:

```php
$tenant_id = require_current_tenant_id();
$seat_check = can_add_more_users($tenant_id);
if (!$seat_check['allowed']) {
  $err = $seat_check['message'];
}
```

Check role-specific capacity before assigning a cashier:

```php
$tenant_id = require_current_tenant_id();
$role_check = can_add_user_to_role($tenant_id, 'CASHIER');
if (!$role_check['allowed']) {
  $err = $role_check['message'];
}
```

Show human-readable plan details in a future UI:

```php
$tenant_id = require_current_tenant_id();
$plan_info = get_tenant_plan_human_info($tenant_id);
echo $plan_info['summary'];
echo $plan_info['usage_summary'];
```

## 4. Assumptions

- Plan enforcement is tenant-scoped, matching the current audit and schema changes.
- `ADMIN` counts are tenant-scoped and include owner admins linked through `tenant_admins`.
- `TENANT` is treated as a legacy compatibility role only and is excluded from seat counting.
- Total user counts include only active billable roles plus active owner admins for that tenant.
- Feature access returns `false` when the subscription is not active or trial.
- The helpers will use database plan rows when available and fall back to the seeded plan definitions if the schema or seed data is missing.
