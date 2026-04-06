SET @tenant_id = 1;
SET @loan_count = 20;

DROP PROCEDURE IF EXISTS seed_dummy_loans;
DELIMITER $$

CREATE PROCEDURE seed_dummy_loans(IN p_tenant_id INT, IN p_loan_count INT)
BEGIN
    DECLARE v_i INT DEFAULT 1;
    DECLARE v_customer_id INT;
    DECLARE v_ci_user_id INT;
    DECLARE v_manager_user_id INT;
    DECLARE v_cashier_user_id INT;
    DECLARE v_loan_officer_id INT;

    DECLARE v_loan_id INT;
    DECLARE v_reference_no VARCHAR(40);
    DECLARE v_or_no VARCHAR(40);

    DECLARE v_principal DECIMAL(12,2);
    DECLARE v_interest_rate DECIMAL(5,2);
    DECLARE v_term_months INT;
    DECLARE v_total_payable DECIMAL(12,2);
    DECLARE v_remaining_balance DECIMAL(12,2);
    DECLARE v_payment_amount DECIMAL(12,2);
    DECLARE v_due_date DATE;
    DECLARE v_payment_term VARCHAR(20);

    DECLARE v_customer_count INT DEFAULT 0;

    SELECT COUNT(*) INTO v_customer_count
    FROM customers
    WHERE tenant_id = p_tenant_id;

    IF v_customer_count = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No customers found for this tenant. Create customers first.';
    END IF;

    SELECT user_id INTO v_ci_user_id
    FROM users
    WHERE tenant_id = p_tenant_id
      AND role = 'CREDIT_INVESTIGATOR'
      AND is_active = 1
    LIMIT 1;

    SELECT user_id INTO v_manager_user_id
    FROM users
    WHERE tenant_id = p_tenant_id
      AND role IN ('MANAGER', 'ADMIN')
      AND is_active = 1
    LIMIT 1;

    SELECT user_id INTO v_loan_officer_id
    FROM users
    WHERE tenant_id = p_tenant_id
      AND role = 'LOAN_OFFICER'
      AND is_active = 1
    LIMIT 1;

    SELECT user_id INTO v_cashier_user_id
    FROM users
    WHERE tenant_id = p_tenant_id
      AND role = 'CASHIER'
      AND is_active = 1
    LIMIT 1;

    IF v_ci_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No CREDIT_INVESTIGATOR found for this tenant.';
    END IF;

    IF v_manager_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No MANAGER or ADMIN found for this tenant.';
    END IF;

    IF v_loan_officer_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No LOAN_OFFICER found for this tenant.';
    END IF;

    IF v_cashier_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No CASHIER found for this tenant.';
    END IF;

    START TRANSACTION;

    WHILE v_i <= p_loan_count DO

        SELECT customer_id
        INTO v_customer_id
        FROM customers
        WHERE tenant_id = p_tenant_id
        ORDER BY RAND()
        LIMIT 1;

        SET v_principal = ROUND(5000 + (RAND() * 25000), 2);
        SET v_term_months = 3 + FLOOR(RAND() * 10);

        CASE MOD(v_i, 4)
            WHEN 0 THEN
                SET v_payment_term = 'daily';
                SET v_interest_rate = 2.75;
            WHEN 1 THEN
                SET v_payment_term = 'weekly';
                SET v_interest_rate = 3.00;
            WHEN 2 THEN
                SET v_payment_term = 'semi_monthly';
                SET v_interest_rate = 3.50;
            ELSE
                SET v_payment_term = 'monthly';
                SET v_interest_rate = 4.00;
        END CASE;

        SET v_total_payable = ROUND(v_principal + (v_principal * (v_interest_rate / 100)), 2);
        SET v_remaining_balance = v_total_payable;

        SET v_reference_no = CONCAT(
            'APP-',
            DATE_FORMAT(NOW(), '%Y%m%d'),
            '-',
            LPAD(v_i, 4, '0'),
            '-',
            FLOOR(100 + RAND() * 900)
        );

        INSERT INTO loans (
            tenant_id,
            reference_no,
            customer_id,
            principal_amount,
            interest_rate,
            payment_term,
            term_months,
            total_payable,
            remaining_balance,
            status,
            submitted_at,
            is_active
        ) VALUES (
            p_tenant_id,
            v_reference_no,
            v_customer_id,
            v_principal,
            v_interest_rate,
            v_payment_term,
            v_term_months,
            v_total_payable,
            v_remaining_balance,
            'PENDING',
            DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY),
            1
        );

        SET v_loan_id = LAST_INSERT_ID();

        UPDATE loans
        SET
            status = 'CI_REVIEWED',
            ci_by = v_ci_user_id,
            ci_at = DATE_ADD(submitted_at, INTERVAL 1 DAY),
            notes = CONCAT(IFNULL(notes, ''), ' CI reviewed.')
        WHERE loan_id = v_loan_id
          AND tenant_id = p_tenant_id;

        IF MOD(v_i, 5) <> 0 THEN
            SET v_due_date = DATE_ADD(CURDATE(), INTERVAL v_term_months MONTH);

            UPDATE loans
            SET
                status = 'ACTIVE',
                manager_by = v_manager_user_id,
                manager_at = NOW(),
                approved_at = NOW(),
                activated_at = NOW(),
                loan_officer_id = v_loan_officer_id,
                due_date = v_due_date,
                notes = CONCAT(IFNULL(notes, ''), ' Manager approved.')
            WHERE loan_id = v_loan_id
              AND tenant_id = p_tenant_id;
        END IF;

        IF MOD(v_i, 3) = 0 THEN
            SET v_payment_amount = ROUND(v_total_payable * 0.25, 2);
            SET v_or_no = CONCAT(
                'OR-',
                DATE_FORMAT(NOW(), '%Y%m%d'),
                '-',
                LPAD(v_i, 4, '0'),
                '-1'
            );

            INSERT INTO payments (
                tenant_id,
                loan_id,
                amount,
                payment_date,
                method,
                or_no,
                loan_officer_id,
                received_by,
                notes
            ) VALUES (
                p_tenant_id,
                v_loan_id,
                v_payment_amount,
                CURDATE(),
                'CASH',
                v_or_no,
                v_loan_officer_id,
                v_cashier_user_id,
                'Dummy payment 1'
            );

            UPDATE loans
            SET remaining_balance = remaining_balance - v_payment_amount
            WHERE loan_id = v_loan_id
              AND tenant_id = p_tenant_id;
        END IF;

        IF MOD(v_i, 6) = 0 THEN
            SET v_payment_amount = ROUND(v_total_payable * 0.20, 2);
            SET v_or_no = CONCAT(
                'OR-',
                DATE_FORMAT(NOW(), '%Y%m%d'),
                '-',
                LPAD(v_i, 4, '0'),
                '-2'
            );

            INSERT INTO payments (
                tenant_id,
                loan_id,
                amount,
                payment_date,
                method,
                or_no,
                loan_officer_id,
                received_by,
                notes
            ) VALUES (
                p_tenant_id,
                v_loan_id,
                v_payment_amount,
                DATE_SUB(CURDATE(), INTERVAL 2 DAY),
                'GCASH',
                v_or_no,
                v_loan_officer_id,
                v_cashier_user_id,
                'Dummy payment 2'
            );

            UPDATE loans
            SET remaining_balance = remaining_balance - v_payment_amount
            WHERE loan_id = v_loan_id
              AND tenant_id = p_tenant_id;
        END IF;

        IF MOD(v_i, 7) = 0 THEN
            UPDATE loans
            SET
                due_date = DATE_SUB(CURDATE(), INTERVAL 5 DAY),
                status = 'OVERDUE'
            WHERE loan_id = v_loan_id
              AND tenant_id = p_tenant_id
              AND status = 'ACTIVE';
        END IF;

        IF MOD(v_i, 8) = 0 THEN
            UPDATE loans
            SET
                remaining_balance = 0,
                status = 'CLOSED'
            WHERE loan_id = v_loan_id
              AND tenant_id = p_tenant_id
              AND status IN ('ACTIVE', 'OVERDUE');
        END IF;

        INSERT INTO activity_logs (
            tenant_id,
            user_id,
            user_role,
            action,
            description,
            loan_id,
            customer_id,
            reference_no,
            created_at
        ) VALUES
        (
            p_tenant_id,
            v_ci_user_id,
            'CREDIT_INVESTIGATOR',
            'CI Review',
            CONCAT('Loan marked as CI reviewed: ', v_reference_no),
            v_loan_id,
            v_customer_id,
            v_reference_no,
            NOW()
        );

        IF MOD(v_i, 5) <> 0 THEN
            INSERT INTO activity_logs (
                tenant_id,
                user_id,
                user_role,
                action,
                description,
                loan_id,
                customer_id,
                reference_no,
                created_at
            ) VALUES
            (
                p_tenant_id,
                v_manager_user_id,
                'MANAGER',
                'Loan Approved',
                CONCAT('Loan approved and activated: ', v_reference_no),
                v_loan_id,
                v_customer_id,
                v_reference_no,
                NOW()
            );
        END IF;

        SET v_i = v_i + 1;
    END WHILE;

    COMMIT;
END$$

DELIMITER ;

CALL seed_dummy_loans(@tenant_id, @loan_count);

DROP PROCEDURE IF EXISTS seed_dummy_loans;
