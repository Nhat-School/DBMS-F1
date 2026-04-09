-- ============================================================
-- F1 FORMULA CHAMPIONSHIP MANAGEMENT
-- 04_seed_data.sql — Sample Data for Testing
-- ============================================================

SET NAMES utf8mb4;

-- ----------------------------------------------------------
-- Users (admin + staff)
-- ----------------------------------------------------------
INSERT INTO `user` (`username`, `password`, `full_name`, `role`) VALUES
('admin', MD5('admin123'), 'Quản trị viên', 'admin'),
('staff1', MD5('staff123'), 'Phạm Văn Nhất', 'staff'),
('staff2', MD5('staff456'), 'Đoàn Hoàng Nam', 'staff');

-- ----------------------------------------------------------
-- Organization
-- ----------------------------------------------------------
INSERT INTO `organization` (`name`, `description`) VALUES
('Fédération Internationale de l\'Automobile (FIA)', 'Tổ chức quản lý giải đua xe Công thức 1 thế giới');

-- ----------------------------------------------------------
-- Tournament (Season 2025)
-- ----------------------------------------------------------
INSERT INTO `tournament` (`organization_id`, `name`, `year`, `start_date`, `end_date`, `description`) VALUES
(1, 'FIA Formula One World Championship 2026', 2026, '2026-03-16', '2026-12-07', 'Mùa giải Công thức 1 năm 2026');

-- ----------------------------------------------------------
-- Stages (5 race tracks)
-- ----------------------------------------------------------
INSERT INTO `stage` (`tournament_id`, `stage_code`, `name`, `number_laps`, `location`, `race_date`, `stage_order`, `description`) VALUES
(1, 'BAH2026', 'Bahrain Grand Prix', 57, 'Bahrain International Circuit', '2026-03-16 18:00:00', 1, 'Chặng đua mở màn mùa giải tại Sakhir'),
(1, 'MON2026', 'Monaco Grand Prix', 78, 'Circuit de Monaco', '2026-05-25 15:00:00', 2, 'Chặng đua truyền thống trên đường phố Monte Carlo'),
(1, 'SIL2026', 'British Grand Prix', 52, 'Silverstone Circuit', '2026-07-06 15:00:00', 3, 'Chặng đua tại quê hương của môn đua xe Công thức 1'),
(1, 'MOZ2026', 'Italian Grand Prix', 53, 'Autodromo Nazionale Monza', '2026-09-07 15:00:00', 4, 'Chặng đua tốc độ cao tại Monza - Đền thờ tốc độ'),
(1, 'ABU2026', 'Abu Dhabi Grand Prix', 58, 'Yas Marina Circuit', '2026-12-07 17:00:00', 5, 'Chặng đua khép lại mùa giải tại Abu Dhabi');

-- ----------------------------------------------------------
-- Teams (5 teams)
-- ----------------------------------------------------------
INSERT INTO `team` (`team_code`, `name`, `brand`, `description`) VALUES
('RBR', 'Oracle Red Bull Racing', 'Red Bull', 'Đội đua vô địch thế giới nhiều năm liên tiếp'),
('FER', 'Scuderia Ferrari', 'Ferrari', 'Đội đua lâu đời nhất và thành công nhất trong lịch sử F1'),
('MER', 'Mercedes-AMG Petronas F1 Team', 'Mercedes', 'Đội đua thống trị kỷ nguyên hybrid turbo'),
('MCL', 'McLaren F1 Team', 'McLaren', 'Một trong những đội đua huyền thoại của F1'),
('AMR', 'Aston Martin Aramco F1 Team', 'Aston Martin', 'Đội đua với tham vọng tranh chức vô địch');

-- ----------------------------------------------------------
-- Racers (10 drivers — 2 per team)
-- ----------------------------------------------------------
INSERT INTO `racer` (`driver_code`, `name`, `nationality`, `dob`, `biography`, `status`) VALUES
('VER', 'Max Verstappen', 'Hà Lan', '1997-09-30', 'Nhà vô địch thế giới 4 lần liên tiếp', 'Active'),
('PER', 'Sergio Pérez', 'Mexico', '1990-01-26', 'Tay đua giàu kinh nghiệm với nhiều chiến thắng', 'Active'),
('LEC', 'Charles Leclerc', 'Monaco', '1997-10-16', 'Tay đua tài năng trẻ của Scuderia Ferrari', 'Active'),
('SAI', 'Carlos Sainz Jr.', 'Tây Ban Nha', '1994-09-01', 'Tay đua đa năng và ổn định', 'Active'),
('HAM', 'Lewis Hamilton', 'Anh', '1985-01-07', 'Tay đua vĩ đại nhất mọi thời đại với 7 chức VĐTG', 'Active'),
('RUS', 'George Russell', 'Anh', '1998-02-15', 'Tay đua trẻ đầy triển vọng', 'Active'),
('NOR', 'Lando Norris', 'Anh', '1999-11-13', 'Tay đua tài năng thế hệ mới', 'Active'),
('PIA', 'Oscar Piastri', 'Úc', '2001-04-06', 'Tân binh xuất sắc từ Formula 2', 'Active'),
('ALO', 'Fernando Alonso', 'Tây Ban Nha', '1981-07-29', 'Huyền thoại 2 lần vô địch thế giới', 'Active'),
('STR', 'Lance Stroll', 'Canada', '1998-10-29', 'Tay đua có tốc độ ấn tượng trên đường ướt', 'Active');

-- ----------------------------------------------------------
-- Contracts (2026 season — each driver signed with their team)
-- ----------------------------------------------------------
INSERT INTO `contract` (`team_id`, `racer_id`, `start_date`, `end_date`) VALUES
(1, 1, '2026-01-01', '2026-12-31'),  -- Verstappen - Red Bull
(1, 2, '2026-01-01', '2026-12-31'),  -- Pérez - Red Bull
(2, 3, '2026-01-01', '2026-12-31'),  -- Leclerc - Ferrari
(2, 4, '2026-01-01', '2026-12-31'),  -- Sainz - Ferrari
(3, 5, '2026-01-01', '2026-12-31'),  -- Hamilton - Mercedes
(3, 6, '2026-01-01', '2026-12-31'),  -- Russell - Mercedes
(4, 7, '2026-01-01', '2026-12-31'),  -- Norris - McLaren
(4, 8, '2026-01-01', '2026-12-31'),  -- Piastri - McLaren
(5, 9, '2026-01-01', '2026-12-31'),  -- Alonso - Aston Martin
(5, 10, '2026-01-01', '2026-12-31'); -- Stroll - Aston Martin

-- ----------------------------------------------------------
-- Registrations for Bahrain GP (all 10 drivers)
-- ----------------------------------------------------------
INSERT INTO `registration` (`stage_id`, `contract_id`) VALUES
(1, 1), (1, 2),   -- Red Bull: Verstappen, Pérez
(1, 3), (1, 4),   -- Ferrari: Leclerc, Sainz
(1, 5), (1, 6),   -- Mercedes: Hamilton, Russell
(1, 7), (1, 8),   -- McLaren: Norris, Piastri
(1, 9), (1, 10);  -- Aston Martin: Alonso, Stroll

-- ----------------------------------------------------------
-- Results for Bahrain GP (to show triggers in action)
-- Note: score is auto-calculated by trigger!
-- ----------------------------------------------------------
INSERT INTO `result` (`stage_id`, `contract_id`, `finish_time`, `laps_completed`, `finish_rank`, `status`, `updated_by`) VALUES
(1, 1, '01:32:44.891', 57, 1, 'Finished', 2),   -- Verstappen P1 → trigger sets 25pts
(1, 3, '01:32:56.143', 57, 2, 'Finished', 2),   -- Leclerc P2 → trigger sets 18pts
(1, 7, '01:33:02.556', 57, 3, 'Finished', 2),   -- Norris P3 → trigger sets 15pts
(1, 4, '01:33:10.220', 57, 4, 'Finished', 2),   -- Sainz P4 → trigger sets 12pts
(1, 8, '01:33:15.887', 57, 5, 'Finished', 2),   -- Piastri P5 → trigger sets 10pts
(1, 6, '01:33:21.334', 57, 6, 'Finished', 2),   -- Russell P6 → trigger sets 8pts
(1, 5, '01:33:28.771', 57, 7, 'Finished', 2),   -- Hamilton P7 → trigger sets 6pts
(1, 9, '01:33:35.445', 57, 8, 'Finished', 2),   -- Alonso P8 → trigger sets 4pts
(1, 2, '01:33:42.112', 57, 9, 'Finished', 2),   -- Pérez P9 → trigger sets 2pts
(1, 10, '00:45:12.000', 28, NULL, 'DNF', 2);    -- Stroll DNF → trigger sets 0pts
