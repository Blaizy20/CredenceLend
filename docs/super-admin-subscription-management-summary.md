# Super Admin Subscription Management Summary

## Existing subscription changes reused

This feature was built on top of the subscription work already present in the repo:

- `includes/subscription_helpers.php`
- `staff/subscription.php`
- `schema.sql`
- `setup/20260403_subscription_plan_schema.sql`
- `setup/20260403_subscription_plan_seed.sql`
- `docs/subscription-plan-schema-changes.md`
- `docs/subscription-plan-helper-changes.md`
- `docs/subscription-plan-limit-enforcement.md`

Those earlier changes already introduced:

- tenant plan schema support through `tenants.plan_id`, `tenants.plan_code`
- tenant subscription lifecycle columns
- plan definitions and plan/status helper functions
- billable user counting and feature/seat enforcement helpers

## Files added/changed

Added:

- `staff/subscriptions.php`
- `docs/super-admin-subscription-management-summary.md`

Changed:

- `includes/auth.php`
- `includes/subscription_helpers.php`
- `staff/_layout_top.php`
- `staff/role_permissions.php`
- `staff/history.php`

## New page structure

New page:

- `staff/subscriptions.php`

Structure:

1. Super-admin hero section with portfolio-level metrics:
   - total tenants
   - active/trial subscriptions
   - tenants needing attention
   - total billable users
2. Schema readiness warning when subscription migration is not yet applied in the database
3. Tenant subscription table with:
   - tenant name
   - current plan
   - effective subscription status
   - subscription start date
   - subscription expiry date
   - current user count
   - inline update form
4. Inline row action for every tenant:
   - plan selector
   - stored subscription status selector
   - save button

## Permissions used

New permission:

- `manage_subscriptions`

Permission mapping:

- `manage_subscriptions` => `SUPER_ADMIN` only

Backend page protection:

- `require_login()`
- `require_permission('manage_subscriptions')`

## Backend update handling

New helper/backend path in `includes/subscription_helpers.php`:

- `subscription_manageable_status_options()`
- `normalize_subscription_plan_code($plan_code, $default = 'BASIC')`
- `subscription_available_plan_options($active_only = true)`
- `update_tenant_subscription_settings($tenant_id, $plan_code, $subscription_status)`

Update flow:

1. Validate tenant id
2. Validate plan code against known plans
3. Validate status against allowed stored subscription statuses
4. Require migrated subscription schema before update
5. Lock the tenant row with `subscription_lock_tenant_scope($tenant_id)`
6. Update:
   - `tenants.plan_code`
   - `tenants.plan_id`
   - `tenants.subscription_status`
   - `tenants.subscription_started_at` when needed
7. Write audit log entry:
   - `TENANT_SUBSCRIPTION_UPDATED`

## Sidebar integration

Sidebar changes in `staff/_layout_top.php`:

- added `can_access('manage_subscriptions')`
- included new menu item:
  - `Subscription Management`
  - path: `staff/subscriptions.php`
- menu item is shown only for `SUPER_ADMIN`

## Audit/history integration

Updated `staff/history.php` so subscription changes are visible and filterable:

- added `TENANT_SUBSCRIPTION_UPDATED` to the tenant activity category
- added a direct filter option for `TENANT_SUBSCRIPTION_UPDATED`

## Verification

PHP lint passed for:

- `includes/subscription_helpers.php`
- `includes/auth.php`
- `staff/subscriptions.php`
- `staff/_layout_top.php`
- `staff/role_permissions.php`
- `staff/history.php`
