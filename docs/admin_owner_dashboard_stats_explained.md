# Admin Owner Dashboard Stats Explained

This file explains the dashboard cards you see when you are logged in as an `ADMIN` owner.

These numbers are based on the **currently selected tenant only**.

## Simple Meaning Of Each Card

### Total Staff
- This shows how many staff accounts belong to your tenant.
- It does **not** count customer accounts.
- It includes roles like:
  - `ADMIN`
  - `TENANT`
  - `MANAGER`
  - `CREDIT_INVESTIGATOR`
  - `LOAN_OFFICER`
  - `CASHIER`

Where to see it:
- `Staff` page
- File/page: `staff/staff.php`

### Pending
- This shows how many loan applications are still waiting and have status `PENDING`.
- In simple words: these are applications not yet finished in the review process.

Where to see it:
- `Loans` page, filter by `PENDING`
- `CI Review` page
- Files/pages:
  - `staff/loans.php`
  - `staff/ci_queue.php`

### Denied
- This shows how many loan applications were rejected.
- Their status is `DENIED`.

Where to see it:
- `Loans` page, filter by `DENIED`
- File/page: `staff/loans.php`

### CI Review Queue
- This is meant to show loans waiting for credit investigation review.
- In the current code, this card is counting **the same `PENDING` loans**.
- So right now, `CI Review Queue` and `Pending` will usually show the **same number**.

Where to see it:
- `CI Review` page
- `Loans` page, filter by `PENDING`
- Files/pages:
  - `staff/ci_queue.php`
  - `staff/loans.php`

### Manager Approval
- This shows loans that already passed CI review and are waiting for manager decision.
- Their status is `CI_REVIEWED`.

Where to see it:
- `Manager Approval` page
- `Loans` page, filter by `CI_REVIEWED`
- Files/pages:
  - `staff/manager_queue.php`
  - `staff/loans.php`

### Approved
- This shows loans with status `ACTIVE`.
- In simple words: these are loans already approved and already running.
- In the current code, `Approved` is counting the same status used for active loans.

Where to see it:
- `Loans` page, filter by `ACTIVE`
- File/page: `staff/loans.php`

### Overdue
- This shows active loans that are already late for payment.
- Their status is `OVERDUE`.

Where to see it:
- `Loans` page, filter by `OVERDUE`
- `Payments` page, filter by loan status `OVERDUE`
- Files/pages:
  - `staff/loans.php`
  - `staff/payments.php`

### Closed
- This shows loans that are already finished or fully settled.
- Their status is `CLOSED`.

Where to see it:
- `Loans` page, filter by `CLOSED`
- File/page: `staff/loans.php`

## Very Short Summary

- `Total Staff` = all staff users in your tenant, not customers
- `Pending` = applications waiting for review
- `Denied` = rejected applications
- `CI Review Queue` = currently the same count as `Pending`
- `Manager Approval` = applications already reviewed by CI and waiting for manager
- `Approved` = active approved loans
- `Overdue` = late loans
- `Closed` = finished loans

## Important Note About Current Behavior

Based on the current code in `staff/dashboard.php`:

- `CI Review Queue` uses `status='PENDING'`
- `Approved` uses `status='ACTIVE'`

So if you expected different numbers for those, the dashboard is not separating them further right now.
