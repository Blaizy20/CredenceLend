# Subscription Seat Counting Refactor

This document summarizes the seat-counting refactor based on the client billing rule that `TENANT` is not a billable user role.

## Counted seat roles

- `ADMIN`
- `MANAGER`
- `CREDIT_INVESTIGATOR`
- `LOAN_OFFICER`
- `CASHIER`

## Excluded from seat counting

- `SUPER_ADMIN`
- `TENANT`
- `CUSTOMER`
- any other legacy or non-billable system role outside the five counted roles

## What changed

- `TENANT` was removed from subscription role-limit mapping.
- `TENANT` was removed from total billable user counting.
- `TENANT` is no longer normalized into `ADMIN` for plan enforcement.
- Admin owner assignment now respects the admin seat rule and total billable seat rule.
- Staff creation, staff role changes, and staff re-activation now use billable-role plan checks.
- The subscription page now describes billable seat limits instead of tenant workspace counts.

## Legacy `TENANT` role handling

`TENANT` still exists in other parts of the codebase for legacy compatibility and access behavior, but it is no longer part of subscription billing logic.

Remaining non-billing references include:
- `includes/AuthService.php`
- `staff/login.php`
- `staff/dashboard.php`
- `staff/download_requirement.php`
- `staff/history.php`
- `staff/manager_settings.php`
- role-audit and validation documents under `docs/`

These references were not converted into billable-seat logic. They remain separate from subscription enforcement.

## Enterprise total-user note

The client-provided `20+` total-user rule for `ENTERPRISE` is represented as an open-ended total seat cap in enforcement logic.

- In helper logic, a total limit of `0` is treated as open-ended.
- In UI messaging, that plan is displayed as `20+` for total billable seats.

This keeps enforcement maintainable without treating `TENANT` as a billable role.
