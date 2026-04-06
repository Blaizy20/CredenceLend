# Subscription Plan Benefits

This document summarizes the current subscription plan benefits implemented in the codebase.

Source of truth:

- [includes/subscription_helpers.php](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/includes/subscription_helpers.php)
- [setup/20260403_subscription_plan_seed.sql](/C:/xampp/htdocs/LOAN_MANAGEMENT_APP/setup/20260403_subscription_plan_seed.sql)

## Basic

Plan summary:

- Designed for small lending teams with core operations only
- Monthly price: `PHP 999.00`

Seat limits:

- Total billable users: `35`
- Admin owners: `1`
- Managers: `1`
- Credit investigators: `1`
- Loan officers: `12`
- Cashiers: `12`

Included benefits:

- Core tenant plan assignment
- System name customization

Not included:

- Reports
- Exports
- Audit logs
- Automation
- Logo upload
- Theme customization
- Editable receipts
- Editable vouchers
- Custom loan configuration
- Advanced reports
- Priority support

## Professional

Plan summary:

- Designed for growing operations that need branding, exports, and editable receipt/voucher support
- Monthly price: `PHP 1499.00`

Seat limits:

- Total billable users: `812`
- Admin owners: `1`
- Managers: `12`
- Credit investigators: `12`
- Loan officers: `35`
- Cashiers: `23`

Included benefits:

- Reports
- Exports
- Logo upload
- Theme customization
- Editable receipts
- Editable vouchers
- System name customization

Not included:

- Audit logs
- Automation
- Custom loan configuration
- Advanced reports
- Priority support

## Enterprise

Plan summary:

- Designed for full feature access, audit visibility, and advanced loan configuration
- Monthly price: `PHP 1999.00`

Seat limits:

- Total billable users: `Unlimited` in logic
- Display label in UI: `20+`
- Admin owners: `1`
- Managers: `24`
- Credit investigators: `24`
- Loan officers: `510`
- Cashiers: `35`

Included benefits:

- Reports
- Exports
- Audit logs
- Automation
- Logo upload
- Theme customization
- Editable receipts
- Editable vouchers
- Custom loan configuration
- Advanced reports
- Priority support
- System name customization

## Notes

- Billable roles are: `ADMIN`, `MANAGER`, `CREDIT_INVESTIGATOR`, `LOAN_OFFICER`, and `CASHIER`
- Non-billable roles are: `SUPER_ADMIN`, `TENANT`, and `CUSTOMER`
- Subscription status must still be active for plan benefits to be usable
- `ACTIVE` and `TRIAL` allow access
- `PAST_DUE`, `SUSPENDED`, `CANCELLED`, and `EXPIRED` block access to tenant core modules

## Important Implementation Note

If your live database plan values differ from the repo seed, the application will prefer database `plans` rows when the subscription schema is active.

That means this document reflects the current repo implementation, not necessarily a customized local database.
