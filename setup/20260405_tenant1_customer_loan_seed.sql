SET NAMES utf8mb4;

START TRANSACTION;

SET @tenant_id := 1;

-- Seed customers for tenant_id = 1 only.
-- This file is rerunnable: each customer_no and reference_no is inserted only once.
-- It does not create staff accounts or customer user accounts.

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'maria.santos', 'CUST-1001', 'Maria', 'Santos', '09170000001', 'maria.santos@example.com',
       'Metro Manila', 'Quezon City', 'Bagumbayan', '12 Sampaguita St', '2026-01-08 09:15:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1001');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'john.reyes', 'CUST-1002', 'John', 'Reyes', '09170000002', 'john.reyes@example.com',
       'Bulacan', 'Malolos', 'Santo Rosario', '45 Rizal Ave', '2026-01-10 10:00:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1002');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'ana.cruz', 'CUST-1003', 'Ana', 'Cruz', '09170000003', 'ana.cruz@example.com',
       'Laguna', 'Santa Rosa', 'Balibago', '89 Mabini St', '2026-01-12 08:30:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1003');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'carlo.garcia', 'CUST-1004', 'Carlo', 'Garcia', '09170000004', 'carlo.garcia@example.com',
       'Cavite', 'Bacoor', 'Talaba', '17 Luna St', '2026-01-14 13:10:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1004');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'liza.mendoza', 'CUST-1005', 'Liza', 'Mendoza', '09170000005', 'liza.mendoza@example.com',
       'Pampanga', 'San Fernando', 'Del Pilar', '102 Orchid Rd', '2026-01-18 14:20:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1005');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'paolo.dizon', 'CUST-1006', 'Paolo', 'Dizon', '09170000006', 'paolo.dizon@example.com',
       'Pangasinan', 'Dagupan', 'Poblacion Oeste', '7 Bonifacio Blvd', '2026-01-22 09:45:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1006');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'jenny.flores', 'CUST-1007', 'Jenny', 'Flores', '09170000007', 'jenny.flores@example.com',
       'Batangas', 'Lipa', 'Sabang', '33 Acacia St', '2026-01-26 11:35:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1007');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'mark.valdez', 'CUST-1008', 'Mark', 'Valdez', '09170000008', 'mark.valdez@example.com',
       'Nueva Ecija', 'Cabanatuan', 'Sangitan', '51 Maharlika Hwy', '2026-02-02 15:05:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1008');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'ella.ramos', 'CUST-1009', 'Ella', 'Ramos', '09170000009', 'ella.ramos@example.com',
       'Tarlac', 'Tarlac City', 'San Vicente', '66 J Luna St', '2026-02-08 10:50:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1009');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'nico.castillo', 'CUST-1010', 'Nico', 'Castillo', '09170000010', 'nico.castillo@example.com',
       'Metro Manila', 'Pasig', 'Santolan', '18 Emerald Ave', '2026-02-11 16:25:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1010');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'rose.navarro', 'CUST-1011', 'Rose', 'Navarro', '09170000011', 'rose.navarro@example.com',
       'Bataan', 'Balanga', 'Cupang Proper', '120 Mabuhay Rd', '2026-02-16 09:10:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1011');

INSERT INTO customers (
  tenant_id, user_id, username, customer_no, first_name, last_name, contact_no, email,
  province, city, barangay, street, created_at, is_active
)
SELECT @tenant_id, NULL, 'leo.manalo', 'CUST-1012', 'Leo', 'Manalo', '09170000012', 'leo.manalo@example.com',
       'Zambales', 'Olongapo', 'East Bajac-Bajac', '27 Harbor Point', '2026-02-20 13:40:00', 1
WHERE EXISTS (SELECT 1 FROM tenants WHERE tenant_id = @tenant_id)
  AND NOT EXISTS (SELECT 1 FROM customers WHERE tenant_id = @tenant_id AND customer_no = 'CUST-1012');

-- Seed loans linked to the seeded customers above.

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0001', c.customer_id, 15000.00, 12.00, 'MONTHLY', 6,
  16800.00, 11200.00, 'ACTIVE', '2026-01-09 08:00:00', '2026-01-09 13:00:00', '2026-01-10 09:00:00', '2026-01-10 11:30:00',
  '2026-01-11 09:00:00', '2026-07-11', NULL, 'Regular appliance loan.', '2026-01-09 08:00:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1001'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0001');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0002', c.customer_id, 25000.00, 10.00, 'MONTHLY', 10,
  27500.00, 0.00, 'CLOSED', '2026-01-12 10:15:00', '2026-01-12 15:00:00', '2026-01-13 10:00:00', '2026-01-13 14:30:00',
  '2026-01-14 10:00:00', '2026-11-14', NULL, 'Closed small business loan.', '2026-01-12 10:15:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1002'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0002');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0003', c.customer_id, 18000.00, 11.50, 'MONTHLY', 8,
  19680.00, 19680.00, 'APPROVED', '2026-01-15 11:00:00', '2026-01-15 16:30:00', '2026-01-16 09:40:00', '2026-01-16 12:15:00',
  NULL, NULL, NULL, 'Approved and pending release.', '2026-01-15 11:00:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1003'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0003');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0004', c.customer_id, 32000.00, 13.00, 'MONTHLY', 12,
  36992.00, 36992.00, 'CI_REVIEWED', '2026-01-19 09:10:00', '2026-01-19 15:45:00', NULL, NULL,
  NULL, NULL, NULL, 'Awaiting manager approval after CI review.', '2026-01-19 09:10:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1004'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0004');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0005', c.customer_id, 22000.00, 9.50, 'MONTHLY', 6,
  23254.00, 23254.00, 'PENDING', '2026-01-24 14:30:00', NULL, NULL, NULL,
  NULL, NULL, NULL, 'Freshly submitted salary loan.', '2026-01-24 14:30:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1005'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0005');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0006', c.customer_id, 40000.00, 14.00, 'MONTHLY', 12,
  45600.00, 28400.00, 'ACTIVE', '2026-01-27 08:45:00', '2026-01-27 13:20:00', '2026-01-28 09:10:00', '2026-01-28 11:45:00',
  '2026-01-29 10:00:00', '2027-01-29', NULL, 'Vehicle repair capital.', '2026-01-27 08:45:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1006'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0006');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0007', c.customer_id, 12000.00, 10.00, 'MONTHLY', 4,
  12480.00, 6240.00, 'OVERDUE', '2026-02-01 09:00:00', '2026-02-01 14:15:00', '2026-02-02 10:05:00', '2026-02-02 13:20:00',
  '2026-02-03 09:10:00', '2026-03-03', NULL, 'Past due one installment.', '2026-02-01 09:00:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1007'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0007');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0008', c.customer_id, 27000.00, 12.50, 'MONTHLY', 9,
  30037.50, 0.00, 'DENIED', '2026-02-05 10:20:00', '2026-02-05 16:05:00', '2026-02-06 09:25:00', NULL,
  NULL, NULL, 'Insufficient repayment capacity based on submitted documents.', 'Denied after review.', '2026-02-05 10:20:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1008'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0008');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0009', c.customer_id, 50000.00, 15.00, 'MONTHLY', 12,
  57500.00, 43125.00, 'ACTIVE', '2026-02-09 08:20:00', '2026-02-09 13:40:00', '2026-02-10 10:15:00', '2026-02-10 14:00:00',
  '2026-02-11 09:00:00', '2027-02-11', NULL, 'Inventory expansion loan.', '2026-02-09 08:20:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1009'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0009');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0010', c.customer_id, 14500.00, 9.00, 'MONTHLY', 5,
  15152.50, 15152.50, 'PENDING', '2026-02-12 15:10:00', NULL, NULL, NULL,
  NULL, NULL, NULL, 'Pending identity verification.', '2026-02-12 15:10:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1010'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0010');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0011', c.customer_id, 36000.00, 13.50, 'MONTHLY', 12,
  40860.00, 34050.00, 'OVERDUE', '2026-02-17 09:35:00', '2026-02-17 14:10:00', '2026-02-18 09:55:00', '2026-02-18 13:05:00',
  '2026-02-19 08:45:00', '2026-03-19', NULL, 'Needs collection follow-up.', '2026-02-17 09:35:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1011'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0011');

INSERT INTO loans (
  tenant_id, reference_no, customer_id, principal_amount, interest_rate, payment_term, term_months,
  total_payable, remaining_balance, status, submitted_at, ci_at, manager_at, approved_at,
  activated_at, due_date, denial_reason, notes, created_at, is_active
)
SELECT
  @tenant_id, 'LN-2026-0012', c.customer_id, 21000.00, 11.00, 'MONTHLY', 7,
  22617.00, 22617.00, 'CI_REVIEWED', '2026-02-22 11:50:00', '2026-02-22 16:20:00', NULL, NULL,
  NULL, NULL, NULL, 'Waiting for manager queue.', '2026-02-22 11:50:00', 1
FROM customers c
WHERE c.tenant_id = @tenant_id AND c.customer_no = 'CUST-1012'
  AND NOT EXISTS (SELECT 1 FROM loans WHERE tenant_id = @tenant_id AND reference_no = 'LN-2026-0012');

COMMIT;
