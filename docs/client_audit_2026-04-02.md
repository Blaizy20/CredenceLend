# Loan Management System Audit And Fix Report

## 1. Executive Summary

This audit found that the system was only partially enforcing multi-tenant isolation. The most important problems were tenant-aware authentication gaps, broken API token verification, hidden-but-not-global role visibility, missing backup tooling, inconsistent username uniqueness rules, and activity/history leakage through weak tenant filters. The dashboard was visually improved already in parts of the codebase, but tenant users still lacked the same activity/trend clarity available to the system view.

The codebase has been updated to harden backend tenant enforcement, make customer API auth and loan submission tenant-aware, add a system-level Roles & Permissions page, add a system-level Backup Settings module with manual trigger and history, improve tenant dashboard charts/activity visibility, and align install schema files with the code now in use.

## 2. Issues Found

### Requirement Audit

| Requirement | Status Before | Notes |
| --- | --- | --- |
| Dashboard UI improvement | Partially implemented | Super admin view had charts; tenant view was still mostly metric cards and tables with limited activity visibility. |
| Show roles & permissions globally | Missing / hidden | A role-permission modal existed inside tenant settings, but `settings.php` required tenant context and the feature gate referenced a non-existent `DEVELOPER` role. |
| Strict tenant isolation | Partially implemented | Many queries used tenant helpers correctly, but history allowed `tenant_id IS NULL/0` leakage and several POST flows lacked backend CSRF validation. |
| Tenant switching restriction | Partially implemented | Session tenant enforcement existed, but tenant-auth fallback paths were still too permissive and needed tightening. |
| Login validation per tenant | Broken / partial | Staff login could still fall back to a global auth path after a tenant-scoped attempt. Customer API auth was not properly tenant-aware. |
| Backup settings feature | Missing | No backup UI, no backup log table, no manual trigger, no backup file listing. |
| Username as main identifier | Partially implemented | Username was used functionally, but uniqueness rules were inconsistent across staff/customer/API flows. |
| Application submission issue | Broken in API path | API token verification was non-functional and customer/loan services were not instantiated with a tenant context, which broke tenant-aware customer/loan operations. |
| Tenant creation auto-increment | Implemented | `tenant_id` already uses `AUTO_INCREMENT`; no manual assignment bug was found in tenant creation flow. |

### Specific Technical Problems

- `api/v1/config.php` stored auth tokens at login time but `verify_auth_token()` always returned `null`, effectively breaking authenticated API usage.
- `api/v1/auth.php` customer register/login/forgot-password flows were not reliably tenant-aware.
- `api/v1/loans.php` did not pass authenticated tenant context into `CustomerService` and `LoanService`.
- `staff/history.php` allowed tenant users to see logs where `tenant_id` was `NULL` or `0`.
- `staff/login.php` could still fall back from tenant-scoped login attempts into a broader auth path.
- POST actions on core staff pages were missing backend CSRF enforcement.
- `schema.sql` was behind the actual app: missing `tenant_status`, `plan_code`, and new support tables used or needed by the application direction.

## 3. Fixes Implemented

- Added `view_role_permissions` and `manage_backups` backend permissions in [`includes/auth.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/auth.php).
- Added backend CSRF enforcement helper in [`includes/auth.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/auth.php) and applied it to the main POST-based staff workflows audited in this pass.
- Added owner-only auth method in [`includes/AuthService.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/AuthService.php) and tightened login routing in [`staff/login.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/login.php).
- Implemented database-backed API token verification and revocation in [`api/v1/config.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/api/v1/config.php).
- Made customer API register/login/forgot-password tenant-aware in [`api/v1/auth.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/api/v1/auth.php).
- Passed authenticated tenant context into loan/customer services in [`api/v1/loans.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/api/v1/loans.php), fixing the tenant-aware application path.
- Tightened history filtering in [`staff/history.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/history.php) to stop tenant users from seeing non-tenant logs.
- Standardized new username checks to global uniqueness in [`staff/registration.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/registration.php), [`staff/customers.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/customers.php), and [`api/v1/auth.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/api/v1/auth.php).
- Added a system-level Roles & Permissions page in [`staff/role_permissions.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/role_permissions.php).
- Added a system-level backup module with manual SQL snapshot generation, backup history, and download support in [`includes/backup_helpers.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/backup_helpers.php), [`staff/backup_settings.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/backup_settings.php), and [`staff/backup_download.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/backup_download.php).
- Improved dashboard clarity and tenant-side trend visibility in [`staff/dashboard.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/dashboard.php).
- Added navigation links for the new system-level modules in [`staff/_layout_top.php`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/staff/_layout_top.php).
- Reconciled schema files in [`schema.sql`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/schema.sql) and [`setup/loan_management_complete.sql`](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/setup/loan_management_complete.sql).

## 4. Files Modified

- `api/v1/auth.php`
- `api/v1/config.php`
- `api/v1/loans.php`
- `includes/AuthService.php`
- `includes/auth.php`
- `includes/backup_helpers.php`
- `schema.sql`
- `setup/loan_management_complete.sql`
- `staff/_layout_top.php`
- `staff/backup_download.php`
- `staff/backup_settings.php`
- `staff/customers.php`
- `staff/dashboard.php`
- `staff/history.php`
- `staff/loan_view.php`
- `staff/loans.php`
- `staff/login.php`
- `staff/manager_settings.php`
- `staff/payment_add.php`
- `staff/payment_edit.php`
- `staff/registration.php`
- `staff/reports.php`
- `staff/role_permissions.php`
- `staff/select_tenant.php`
- `staff/tenant_management.php`

## 5. Code Changes

- Tenant-aware staff login now distinguishes tenant-scoped users from owner/system accounts.
- API auth tokens are now actually verifiable instead of being written and then ignored.
- Customer API actions now require a valid tenant and use that tenant throughout the customer/loan services.
- Tenant users no longer see `NULL` / `0` tenant history entries.
- Main admin/staff POST flows now require a valid CSRF token.
- Username collision checks now block duplicate usernames globally in the updated registration/edit flows.

## 6. New Features Added

- System-level Roles & Permissions page for `SUPER_ADMIN`.
- System-level Backup Settings page for `SUPER_ADMIN`.
- Manual backup trigger with SQL file output.
- Backup history logging table and backup file listing/download.
- Tenant-side dashboard charts and recent activity feed.

## 7. Security Improvements

- Backend CSRF validation added to core POST actions covered in this audit.
- Tenant login validation tightened to prevent cross-tenant staff access by fallback auth behavior.
- Authenticated API access now validates stored tokens and active users.
- History/activity visibility now respects strict tenant filtering.
- New system-level modules are permission-protected in the backend, not just hidden in UI.

## 8. Remaining Risks

- The database server was not running in this environment during the audit, so the fixes were syntax-checked but not exercised against a live database.
- Existing deployed databases may already contain duplicate usernames. Fresh schema now defines global username uniqueness, but production data may need cleanup before adding the new DB-level unique index safely.
- The new backup feature is a basic application-level SQL snapshot, not a full infrastructure backup strategy.
- Some older pages outside the audited/fixed paths may still deserve a second-pass CSRF review.

## 9. Final Confirmation

### Does the system now meet the client requirements?

Mostly yes at code level, with one operational caveat: the updated code now covers the requested tenant-auth hardening, system-level roles/permissions visibility, backup module, dashboard improvements, stricter tenant history visibility, and username handling. Final production confirmation still requires running the updated system against a live database, applying the schema updates, and verifying existing data does not conflict with the new global username rule.
