-- ============================================================
-- F1 FORMULA CHAMPIONSHIP MANAGEMENT
-- 03_views.sql — Database Views for Statistics
-- ============================================================

-- ----------------------------------------------------------
-- VIEW: Racer standings (cumulative points and time per stage)
-- Used by Module 3: Xem bảng xếp hạng tay đua
-- ----------------------------------------------------------
CREATE OR REPLACE VIEW vw_racer_standings AS
SELECT
    r.id AS racer_id,
    r.driver_code,
    r.name AS racer_name,
    r.nationality,
    t.id AS team_id,
    t.name AS team_name,
    s.id AS stage_id,
    s.name AS stage_name,
    s.stage_order,
    s.tournament_id,
    COUNT(DISTINCT res.stage_id) AS total_races,
    COALESCE(SUM(res.score), 0) AS total_points,
    COALESCE(SUM(
        CASE WHEN res.finish_time IS NOT NULL AND res.finish_time != ''
        THEN TIME_TO_SEC(res.finish_time)
        ELSE 0 END
    ), 0) AS total_time_seconds
FROM racer r
JOIN contract c ON c.racer_id = r.id
JOIN registration reg ON reg.contract_id = c.id
JOIN stage s ON reg.stage_id = s.id
JOIN team t ON c.team_id = t.id
LEFT JOIN result res ON res.contract_id = c.id
    AND res.stage_id <= s.id
    AND EXISTS (SELECT 1 FROM stage s2 WHERE s2.id = res.stage_id AND s2.tournament_id = s.tournament_id)
GROUP BY r.id, r.driver_code, r.name, r.nationality, t.id, t.name, s.id, s.name, s.stage_order, s.tournament_id;

-- ----------------------------------------------------------
-- VIEW: Team standings (sum of all drivers' points)
-- Used by Module 4: Xem bảng xếp hạng đội đua
-- ----------------------------------------------------------
CREATE OR REPLACE VIEW vw_team_standings AS
SELECT
    t.id AS team_id,
    t.team_code,
    t.name AS team_name,
    t.brand,
    s.id AS stage_id,
    s.name AS stage_name,
    s.stage_order,
    s.tournament_id,
    COALESCE(SUM(res.score), 0) AS total_points,
    COALESCE(SUM(
        CASE WHEN res.finish_time IS NOT NULL AND res.finish_time != ''
        THEN TIME_TO_SEC(res.finish_time)
        ELSE 0 END
    ), 0) AS total_time_seconds
FROM team t
JOIN contract c ON c.team_id = t.id
JOIN registration reg ON reg.contract_id = c.id
JOIN stage s ON reg.stage_id = s.id
LEFT JOIN result res ON res.contract_id = c.id
    AND res.stage_id <= s.id
    AND EXISTS (SELECT 1 FROM stage s2 WHERE s2.id = res.stage_id AND s2.tournament_id = s.tournament_id)
GROUP BY t.id, t.team_code, t.name, t.brand, s.id, s.name, s.stage_order, s.tournament_id;
