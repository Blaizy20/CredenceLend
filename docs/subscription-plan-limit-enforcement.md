# Subscription Plan Limit Enforcement

## Files Changed

- `staff/registration.php`
- `staff/staff_settings.php`
- `staff/tenant_management.php`
- `includes/subscription_helpers.php`

## Limit Checks Added

- Staff creation now enforces plan limits in the transaction-backed backend path, not just as a pre-check.
- Before creating tenant-scoped staff, the backend now checks:
  - subscription is active
  - total billable seat cap (`max_total_users`)
  - per-role cap for `MANAGER`, `CREDIT_INVESTIGATOR`, `LOAN_OFFICER`, `CASHIER`
- Staff edit role now re-checks limits inside a transaction before saving a role change.
- Staff re-activation now re-checks limits before activating an inactive account.
- Owner `ADMIN` reassignment now still respects the tenant `ADMIN` seat limit, but valid owner swaps no longer fail incorrectly.
- Tenant-scoped limit checks are serialized with a tenant row lock to prevent concurrent requests from overshooting the same plan limit.

## Validation/Error Messages Added

- Total-seat limit messages now read like:
  - `This tenant has reached the maximum number of billable users allowed by the Basic plan.`
- Per-role limit messages now read like:
  - `This tenant has reached the maximum number of Cashiers allowed by the Basic plan.`
- Inactive subscription messages now read like:
  - `This tenant cannot assign billable staff because its subscription is Suspended.`
- These messages come from the shared helper so they apply consistently across create, edit, reactivate, and owner-assignment paths.

## Edge Cases Handled

- `SUPER_ADMIN` remains outside tenant seat counting.
- Global `ADMIN` owner account creation remains outside tenant limits until that admin is actually assigned to a tenant.
- Re-activating an `ADMIN` owner now validates every tenant the owner is attached to, since those seats live in `tenant_admins`, not `users.tenant_id`.
- Replacing one tenant owner `ADMIN` with another no longer gets blocked by the `ADMIN max = 1` rule.
- An existing bug in `staff/registration.php` was fixed where non-`ADMIN` staff creation could pass validation but never reach the insert path.

## Verification

- `C:\xampp\php\php.exe -l includes\subscription_helpers.php`
- `C:\xampp\php\php.exe -l staff\registration.php`
- `C:\xampp\php\php.exe -l staff\staff_settings.php`
- `C:\xampp\php\php.exe -l staff\tenant_management.php`

All four files passed PHP syntax validation.
