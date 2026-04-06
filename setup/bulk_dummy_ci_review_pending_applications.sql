SET @tenant_id = 1;
SET @application_count = 20;

DROP PROCEDURE IF EXISTS seed_ci_pending_applications;
DELIMITER $$

CREATE PROCEDURE seed_ci_pending_applications(IN p_tenant_id INT, IN p_application_count INT)
BEGIN
    DECLARE v_i INT DEFAULT 1;
    DECLARE v_customer_id INT;
    DECLARE v_customer_count INT DEFAULT 0;

    DECLARE v_loan_id INT;
    DECLARE v_reference_no VARCHAR(40);
    DECLARE v_principal DECIMAL(12,2);
    DECLARE v_interest_rate DECIMAL(5,2);
    DECLARE v_term_months INT;
    DECLARE v_total_payable DECIMAL(12,2);
    DECLARE v_payment_term VARCHAR(20);

    SELECT COUNT(*) INTO v_customer_count
    FROM customers
    WHERE tenant_id = p_tenant_id
      AND is_active = 1;

    IF v_customer_count = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No active customers found for this tenant. Create customers first.';
    END IF;

    START TRANSACTION;

    WHILE v_i <= p_application_count DO

        SELECT customer_id
        INTO v_customer_id
        FROM customers
        WHERE tenant_id = p_tenant_id
          AND is_active = 1
        ORDER BY RAND()
        LIMIT 1;

        SET v_principal = ROUND(3000 + (RAND() * 22000), 2);
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

        SET v_reference_no = CONCAT(
            'APP-CI-',
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
            notes,
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
            v_total_payable,
            'PENDING',
            DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 15) DAY),
            'Dummy application seeded for CI review queue.',
            1
        );

        SET v_loan_id = LAST_INSERT_ID();

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
        ) VALUES (
            p_tenant_id,
            NULL,
            'CUSTOMER',
            'LOAN_CREATED',
            CONCAT('Pending application submitted for CI review: ', v_reference_no),
            v_loan_id,
            v_customer_id,
            v_reference_no,
            NOW()
        );

        SET v_i = v_i + 1;
    END WHILE;

    COMMIT;
END$$

DELIMITER ;

CALL seed_ci_pending_applications(@tenant_id, @application_count);

DROP PROCEDURE IF EXISTS seed_ci_pending_applications;
