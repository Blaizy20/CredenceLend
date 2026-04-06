SET NAMES utf8mb4;

START TRANSACTION;

SET @tenant_id := 1;

-- Seed payments for tenant_id = 1 only.
-- This file depends on the loan references from:
--   setup/20260405_tenant1_customer_loan_seed.sql
-- It is rerunnable because each OR number is inserted only once per tenant.

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 2800.00, '2026-02-11', 'CASH', 'OR-2026-1001', 'First monthly payment.', '2026-02-11 10:05:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0001'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1001');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 2800.00, '2026-03-11', 'GCASH', 'OR-2026-1002', 'Second monthly payment.', '2026-03-11 09:20:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0001'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1002');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 5500.00, '2026-02-14', 'BANK', 'OR-2026-1003', 'Initial payment for closed account.', '2026-02-14 13:15:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0002'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1003');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 5500.00, '2026-03-14', 'BANK', 'OR-2026-1004', 'Second payment for closed account.', '2026-03-14 14:05:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0002'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1004');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 5500.00, '2026-04-14', 'CHEQUE', 'OR-2026-1005', 'Third payment for closed account.', '2026-04-14 11:10:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0002'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1005');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, cheque_number, cheque_date, bank_name, account_holder, notes, created_at
)
SELECT @tenant_id, l.loan_id, 5500.00, '2026-05-14', 'CHEQUE', 'OR-2026-1006', 'CHK-22014', '2026-05-14', 'MetroBank', 'John Reyes', 'Fourth payment for closed account.', '2026-05-14 15:30:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0002'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1006');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 5500.00, '2026-06-14', 'DIGITAL', 'OR-2026-1007', 'Final payment for closed account.', '2026-06-14 10:40:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0002'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1007');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 3800.00, '2026-02-28', 'CASH', 'OR-2026-1008', 'First payment for active loan.', '2026-02-28 09:55:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0006'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1008');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 3800.00, '2026-03-29', 'GCASH', 'OR-2026-1009', 'Second payment for active loan.', '2026-03-29 16:05:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0006'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1009');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 6240.00, '2026-03-03', 'CASH', 'OR-2026-1010', 'First installment for overdue account.', '2026-03-03 11:25:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0007'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1010');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 7187.50, '2026-03-11', 'BANK', 'OR-2026-1011', 'Initial payment for enterprise inventory loan.', '2026-03-11 10:15:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0009'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1011');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, bank_reference_no, account_holder, notes, created_at
)
SELECT @tenant_id, l.loan_id, 7187.50, '2026-04-11', 'BANK', 'OR-2026-1012', 'BNK-981223', 'Ella Ramos', 'Second payment for enterprise inventory loan.', '2026-04-11 09:45:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0009'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1012');

INSERT INTO payments (
  tenant_id, loan_id, amount, payment_date, method, or_no, notes, created_at
)
SELECT @tenant_id, l.loan_id, 6810.00, '2026-03-19', 'OTHER', 'OR-2026-1013', 'Partial payment received before follow-up.', '2026-03-19 14:50:00'
FROM loans l
WHERE l.tenant_id = @tenant_id
  AND l.reference_no = 'LN-2026-0011'
  AND NOT EXISTS (SELECT 1 FROM payments WHERE tenant_id = @tenant_id AND or_no = 'OR-2026-1013');

COMMIT;
