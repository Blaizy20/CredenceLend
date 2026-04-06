# Subscription/Plan Audit

No code was implemented. This document maps where subscription logic will affect the current multi-tenant PHP loan management system.

## 1. Current system areas affected by subscription

### Tenant creation and tenant lifecycle
- Tenant creation currently happens in `staff/registration.php:373-451`.
  - It writes `tenants.plan_code` at creation time.
  - It creates a matching `system_settings` row.
  - It can assign one owner in `tenant_admins`.
- Tenant approval/activation/suspension currently happens in `staff/tenant_management.php:41-122`.
  - This uses `tenant_status` plus `is_active`.
  - Subscription state is not checked anywhere here.
- Tenant ownership reassignment happens in:
  - `staff/registration.php:453-520`
  - `staff/tenant_management.php:124-170`

### Admin login flow and tenant selection
- Login is handled in `staff/login.php:41-149`.
  - Tenant-scoped staff must pick a tenant.
  - `ADMIN` owners are allowed to log in without picking a tenant first, then are routed to tenant selection if they own more than one tenant.
  - No subscription validation is performed before session creation.
- Authentication queries are in `includes/AuthService.php:26-138`.
  - They only check `is_active` and role.
  - They do not check `plan_code`, billing status, expiry, grace period, or feature access.
- Session/tenant enforcement is in `includes/auth.php:186-206`, `includes/auth.php:357-387`, and `includes/auth.php:414-483`.
  - `require_login()`, `set_current_active_tenant_id()`, and `ensure_active_tenant_session()` only validate active tenant/session state.
  - This is the best central place to block expired, suspended, or past-due subscriptions.

### Staff creation, activation, and role management
- Staff creation/edit/delete is in `staff/registration.php:198-359`.
  - This is the primary enforcement point for staff seat limits.
- Staff role changes and activate/deactivate are in `staff/staff_settings.php:17-75`.
  - This is the second primary enforcement point because re-activating or changing role can exceed limits even if creation is blocked.
- Staff listing is in `staff/staff.php:6-39`.
  - Listing itself is not the enforcement point, but it is the main usage display.
- Static role permissions are defined in `includes/auth.php:46-80`.
  - Current access is role-based only, not plan-based.

### Reports, analytics, exports, and history
- Main reports page is `staff/reports.php`.
  - Report access is checked at `staff/reports.php:4-12`.
  - Advanced report types are gated only by role at `staff/reports.php:12-14` and `staff/reports.php:56-62`.
  - Loan term updates are allowed from the reports page at `staff/reports.php:20-46`.
  - Report exports are client-side JS after data is already rendered:
    - Export modal around `staff/reports.php:1260-1266`
    - CSV export around `staff/reports.php:1473-1499`
    - PDF export around `staff/reports.php:1505-1600`
- Sales report is `staff/sales_report.php:4-89`.
  - This is already restricted to `SUPER_ADMIN` and `ADMIN`, but not by plan.
- Dashboard is `staff/dashboard.php:4-108`.
  - It shows tenant and activity metrics.
  - Chart data comes from `api/v1/analytics.php:1-220`.
  - Backend feature checks will be needed in both the page and the API.
- History/audit log page is `staff/history.php:4-118`.
  - It reads from `activity_logs`.
  - It is a natural premium feature gate for audit visibility.

### Branding/settings
- Tenant branding/settings are updated in `staff/manager_settings.php:15-97`.
  - Current tenant-level settings are `system_name`, `logo_path`, and `primary_color`.
- Settings are loaded and cached in `includes/auth.php:689-817`.
- Login branding is tenant-aware in `staff/login.php:10-25`.
- Sidebar/topbar branding and menu rendering are in `staff/_layout_top.php:4-32` and `staff/_layout_top.php:120-304`.

### Vouchers and receipts
- Money release vouchers:
  - `staff/release_voucher.php:4-105`
  - `staff/release_queue.php`
- Receipt pages:
  - `staff/payment_receipt.php:4-22`
  - `staff/receipt_summary.php:4-23`
- These pages already have role checks, but no plan checks.

### Loan configuration settings
- Per-loan term/rate editing exists in:
  - `staff/loan_view.php:111-136`
  - `staff/loans.php:27-48`
  - `staff/reports.php:20-46`
- Loan creation accepts rate/term directly in:
  - `api/v1/loans.php:147-204`
  - `includes/LoanService.php:116-157`
- Global/default term-rate behavior is hardcoded in:
  - `includes/loan_helpers.php:36-55`
  - `staff/release_voucher.php:30-47`
- There is no tenant-level loan configuration table today.

## 2. Existing features that can be plan-gated

### Already implemented and easy to gate
- Staff seats and staff management:
  - `staff/registration.php`
  - `staff/staff_settings.php`
  - `staff/staff.php`
- Tenant workspace creation/activation:
  - `staff/registration.php`
  - `staff/tenant_management.php`
- Reports:
  - Base loan report in `staff/reports.php`
  - Advanced report types already separated by code path in `staff/reports.php`
  - Sales report in `staff/sales_report.php`
- Dashboard analytics:
  - `staff/dashboard.php`
  - `api/v1/analytics.php`
- Audit logs/history:
  - `staff/history.php`
  - Dashboard recent activity widgets
- Branding/settings:
  - `staff/settings.php`
  - `staff/manager_settings.php`
- Receipts and receipt PDFs:
  - `staff/payment_receipt.php`
  - `staff/receipt_summary.php`
  - Receipt launch flow embedded in `staff/reports.php`
- Vouchers:
  - `staff/release_queue.php`
  - `staff/release_voucher.php`
- Loan term/rate editing:
  - `staff/loan_view.php`
  - `staff/loans.php`
  - `staff/reports.php`
- Backups / SQL export:
  - `staff/backup_settings.php`
  - `staff/backup_download.php`
- Subscription UI itself already exists:
  - `staff/subscription.php:1-72`
  - It is UI-only and not connected to real subscription data.

### Menu-level surfaces already present
- The sidebar already separates features in `staff/_layout_top.php:231-304`.
- That means the UI can be hidden by plan quickly, but backend checks still must be authoritative.

## 3. Missing data model pieces

- `tenants.plan_code` exists, but it is only a label today.
  - There is no billing lifecycle state beyond `tenant_status` and `is_active`.
- There is no explicit subscription status such as:
  - `TRIAL`
  - `ACTIVE`
  - `PAST_DUE`
  - `CANCELLED`
  - `EXPIRED`
- There are no subscription dates:
  - trial end
  - current period start
  - current period end
  - grace end
- There is no plan feature matrix in the database.
  - Current role permissions are static in PHP.
  - Current plan labels are static in PHP.
- There is no place to store plan overrides for special tenants.
- There is no dedicated plan-change history table.
  - Day 1 can reuse `activity_logs`, but the structure is not subscription-specific.
- There is no tenant-level loan configuration model.
  - Loan term/rate defaults are hardcoded or passed in at loan creation time.
- Important design ambiguity:
  - The schema models plans per tenant (`tenants.plan_code`).
  - The sample subscription UI describes plans like "1 active tenant workspace" and "3 active tenant workspaces", which sounds owner-level, not tenant-level.
  - If the intended billing model is one owner account covering multiple tenant workspaces, the current data model is misaligned.

## 4. Recommended schema changes

### Minimum-change path if plans are tenant-scoped
- Keep `tenants.plan_code`.
- Add the following columns to `tenants`:
  - `subscription_status ENUM('TRIAL','ACTIVE','PAST_DUE','SUSPENDED','CANCELLED','EXPIRED') NOT NULL DEFAULT 'TRIAL'`
  - `subscription_started_at DATETIME NULL`
  - `subscription_ends_at DATETIME NULL`
  - `trial_ends_at DATETIME NULL`
  - `grace_ends_at DATETIME NULL`
  - `subscription_updated_at DATETIME NULL`
- Add an index such as:
  - `KEY idx_tenants_subscription (subscription_status, subscription_ends_at)`
- Keep the initial plan matrix in PHP config/service code, not in the database.
  - This is the smallest launchable change.
  - `plan_code` can drive features like max staff, reports, branding, vouchers, exports, history, and backups.
- Reuse `activity_logs` for subscription change audit in phase 1.
  - No extra table is strictly required to launch.

### Optional but recommended if you need custom tenant deals
- Add `feature_overrides_json JSON NULL` on `tenants`.
  - Use only if you expect enterprise exceptions outside the standard plan matrix.

### Not required for phase 1, but needed if billing is owner-scoped
- Add a separate billing entity such as `billing_accounts` or `subscription_accounts`.
  - Put `plan_code` and subscription dates there instead of on `tenants`.
  - Link owned tenants to that billing account.
- Without that, you cannot correctly enforce "number of active tenant workspaces per owner/admin" using the current schema.

### Not required for phase 1 subscription, but relevant to loan-configuration plans
- If subscription tiers will unlock configurable loan products or custom default rates/terms, add a tenant-level loan settings table later.
  - Example: `tenant_loan_settings` or a JSON column on a tenant settings table.

## 5. Files that will need modification

### Schema and migrations
- `setup/loan_management_complete.sql`
- `schema.sql`
- `setup/20260325_priority3_tenant_workflow_migration.sql`
- likely a new forward migration under `setup/`

### Core auth/session/tenant enforcement
- `includes/auth.php`
- `includes/AuthService.php`
- `includes/loan_helpers.php`
- `includes/TenantService.php`
- `includes/TenantMiddleware.php`

### Tenant creation and lifecycle
- `staff/registration.php`
- `staff/tenant_management.php`
- `staff/login.php`
- `staff/select_tenant.php`
- `staff/subscription.php`

### Staff limits and role limits
- `staff/registration.php`
- `staff/staff_settings.php`
- `staff/staff.php`

### Reports, exports, analytics, history
- `staff/reports.php`
- `staff/sales_report.php`
- `staff/history.php`
- `staff/dashboard.php`
- `api/v1/analytics.php`
- any future server-side export endpoint if CSV/PDF moves out of the browser

### Branding/settings
- `staff/settings.php`
- `staff/manager_settings.php`
- `staff/_layout_top.php`

### Receipts, vouchers, download/export handlers
- `staff/payment_receipt.php`
- `staff/receipt_summary.php`
- `staff/release_queue.php`
- `staff/release_voucher.php`
- `staff/backup_settings.php`
- `staff/backup_download.php`
- `staff/download_requirement.php` only if file-download/export access also becomes plan-gated

### Loan configuration and product enforcement
- `staff/loan_view.php`
- `staff/loans.php`
- `api/v1/loans.php`
- `includes/LoanService.php`

## 6. Risks / conflicts with current design

- The biggest design risk is billing scope ambiguity.
  - Current schema is tenant-scoped.
  - Current subscription UI copy looks owner-scoped across multiple tenant workspaces.
  - You should decide this before implementation.

- `tenant_status` and billing status are not the same thing.
  - `tenant_status` currently represents approval/operational workflow.
  - Reusing it for billing suspension will mix onboarding workflow with subscription state.
  - A separate `subscription_status` is safer.

- Role checks are static and scattered.
  - `includes/auth.php:46-80` controls role permissions.
  - Many pages rely only on `require_permission(...)`.
  - Hiding the menu in `staff/_layout_top.php` will not be enough.

- Staff limits are currently bypassable through non-create flows unless you enforce them in more than one place.
  - `staff/registration.php` covers create.
  - `staff/staff_settings.php` also needs checks for activate/promote.

- Owner admins are stored differently from tenant staff.
  - `ADMIN` accounts are linked through `tenant_admins`, not `users.tenant_id`.
  - Any "staff per tenant" count must decide whether owner admins count toward plan seats.
  - Current tenant statistics in `staff/tenant_management.php` and similar queries use `users.tenant_id`, so owner admins are not counted there.

- Some multi-tenant helper classes are stale relative to the owner refactor.
  - `includes/TenantService.php:95-100` creates tenants without `tenant_status` workflow handling.
  - `includes/TenantMiddleware.php:25-49` still depends on `users.tenant_id` for tenant context resolution.
  - These may conflict with subscription enforcement if reused later.

- Exports are mostly client-side today.
  - `staff/reports.php` CSV/PDF export happens in browser JS after the page is already rendered.
  - If a user can open the page, they can export the rendered data.
  - The real backend gate must happen before report data is loaded.

- Branding is tenant-scoped, but loan configuration is not.
  - `system_settings` already supports tenant branding.
  - There is no matching tenant-level table for loan defaults or product configuration.
  - If plans include configurable loan products, that is a separate data-model gap.

- The `interest_rate_history` table exists in schema, but the current codebase does not appear to write to it.
  - If you expect premium audit depth around loan-config changes, current implementation relies on generic `activity_logs`, not structured history.

## Recommended implementation order later

1. Decide whether subscriptions are tenant-scoped or owner-scoped.
2. Add subscription lifecycle columns/schema.
3. Add one central backend helper for:
   - tenant subscription state
   - feature checks
   - staff/tenant limit checks
4. Enforce centrally in `require_login()` / tenant session selection.
5. Enforce at write paths:
   - tenant create/activate
   - staff create/activate/promote
   - loan config changes
6. Enforce at read/export paths:
   - reports
   - dashboard analytics API
   - history
   - receipts/vouchers
   - backup/export downloads
