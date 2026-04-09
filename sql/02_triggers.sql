-- ============================================================
-- F1 FORMULA CHAMPIONSHIP MANAGEMENT
-- 02_triggers.sql — Triggers & Stored Procedures
-- ============================================================

DELIMITER //

-- ----------------------------------------------------------
-- TRIGGER: Auto-assign F1 score on INSERT
-- Standard F1 scoring: 25, 18, 15, 12, 10, 8, 6, 4, 2, 1
-- ----------------------------------------------------------
CREATE TRIGGER trg_assign_score_insert
BEFORE INSERT ON `result`
FOR EACH ROW
BEGIN
    IF NEW.status = 'Finished' AND NEW.finish_rank IS NOT NULL THEN
        CASE NEW.finish_rank
            WHEN 1  THEN SET NEW.score = 25;
            WHEN 2  THEN SET NEW.score = 18;
            WHEN 3  THEN SET NEW.score = 15;
            WHEN 4  THEN SET NEW.score = 12;
            WHEN 5  THEN SET NEW.score = 10;
            WHEN 6  THEN SET NEW.score = 8;
            WHEN 7  THEN SET NEW.score = 6;
            WHEN 8  THEN SET NEW.score = 4;
            WHEN 9  THEN SET NEW.score = 2;
            WHEN 10 THEN SET NEW.score = 1;
            ELSE SET NEW.score = 0;
        END CASE;
    ELSE
        SET NEW.score = 0;
    END IF;
END //

-- ----------------------------------------------------------
-- TRIGGER: Auto-assign F1 score on UPDATE
-- ----------------------------------------------------------
CREATE TRIGGER trg_assign_score_update
BEFORE UPDATE ON `result`
FOR EACH ROW
BEGIN
    IF NEW.status = 'Finished' AND NEW.finish_rank IS NOT NULL THEN
        CASE NEW.finish_rank
            WHEN 1  THEN SET NEW.score = 25;
            WHEN 2  THEN SET NEW.score = 18;
            WHEN 3  THEN SET NEW.score = 15;
            WHEN 4  THEN SET NEW.score = 12;
            WHEN 5  THEN SET NEW.score = 10;
            WHEN 6  THEN SET NEW.score = 8;
            WHEN 7  THEN SET NEW.score = 6;
            WHEN 8  THEN SET NEW.score = 4;
            WHEN 9  THEN SET NEW.score = 2;
            WHEN 10 THEN SET NEW.score = 1;
            ELSE SET NEW.score = 0;
        END CASE;
    ELSE
        SET NEW.score = 0;
    END IF;
END //

-- ----------------------------------------------------------
-- STORED PROCEDURE: Validate registration
-- Enforces "max 2 drivers per team per stage" rule
-- ----------------------------------------------------------
CREATE PROCEDURE sp_validate_registration(
    IN p_stage_id INT,
    IN p_contract_id INT,
    OUT p_is_valid BOOLEAN,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_team_id INT;
    DECLARE v_driver_count INT;
    DECLARE v_racer_status VARCHAR(20);
    DECLARE v_already_registered INT;

    -- Get team from contract
    SELECT team_id INTO v_team_id FROM contract WHERE id = p_contract_id;

    -- Check if already registered
    SELECT COUNT(*) INTO v_already_registered
    FROM registration
    WHERE stage_id = p_stage_id AND contract_id = p_contract_id;

    IF v_already_registered > 0 THEN
        SET p_is_valid = FALSE;
        SET p_message = 'Tay đua đã được đăng ký cho chặng đua này';
    ELSE
        -- Check racer status is Active
        SELECT r.status INTO v_racer_status
        FROM racer r
        JOIN contract c ON c.racer_id = r.id
        WHERE c.id = p_contract_id;

        IF v_racer_status != 'Active' THEN
            SET p_is_valid = FALSE;
            SET p_message = CONCAT('Tay đua không ở trạng thái hoạt động (', v_racer_status, ')');
        ELSE
            -- Count how many drivers from this team are already registered
            SELECT COUNT(*) INTO v_driver_count
            FROM registration reg
            JOIN contract c ON reg.contract_id = c.id
            WHERE reg.stage_id = p_stage_id AND c.team_id = v_team_id;

            IF v_driver_count >= 2 THEN
                SET p_is_valid = FALSE;
                SET p_message = 'Đội đua đã đăng ký đủ 2 tay đua cho chặng đua này';
            ELSE
                SET p_is_valid = TRUE;
                SET p_message = 'OK';
            END IF;
        END IF;
    END IF;
END //

DELIMITER ;
