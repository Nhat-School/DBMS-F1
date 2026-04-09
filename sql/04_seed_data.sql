-- ============================================================
-- F1 FORMULA CHAMPIONSHIP MANAGEMENT
-- 04_seed_data.sql — Diversified Team Sizes for Testing
-- ============================================================

SET NAMES utf8mb4;

-- ----------------------------------------------------------
-- 1. Cleanup
-- ----------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `result`;
TRUNCATE TABLE `registration`;
TRUNCATE TABLE `contract`;
TRUNCATE TABLE `racer`;
TRUNCATE TABLE `team`;
TRUNCATE TABLE `stage`;
TRUNCATE TABLE `tournament`;
TRUNCATE TABLE `organization`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------
-- 2. Users
-- ----------------------------------------------------------
INSERT IGNORE INTO `user` (`id`, `username`, `password`, `full_name`, `role`) VALUES
(1, 'admin', MD5('admin123'), 'Quản trị viên', 'admin'),
(2, 'staff1', MD5('staff123'), 'Phạm Văn Nhất', 'staff'),
(3, 'staff2', MD5('staff456'), 'Đoàn Hoàng Nam', 'staff');

-- ----------------------------------------------------------
-- 3. Organization & Tournament
-- ----------------------------------------------------------
INSERT INTO `organization` (`id`, `name`, `description`) VALUES
(1, 'FIA', 'Fédération Internationale de l\'Automobile'),
(2, 'VMA', 'Vietnam Motorsports Association');

INSERT INTO `tournament` (`id`, `organization_id`, `name`, `year`, `start_date`, `end_date`, `description`) VALUES
(1, 1, 'Formula 1 World Championship 2026', 2026, '2026-03-01', '2026-12-31', 'Mùa giải F1 thế giới 2026');

-- ----------------------------------------------------------
-- 4. Stages (Vietnamese Locations)
-- ----------------------------------------------------------
INSERT INTO `stage` (`id`, `tournament_id`, `stage_code`, `name`, `number_laps`, `location`, `race_date`, `stage_order`, `description`) VALUES
(1, 1, 'HAN2026', 'Vietnam Grand Prix (Hà Nội)', 55, 'Hanoi Street Circuit', '2026-04-12 15:00:00', 1, 'Chặng đua phố kịch tính tại thủ đô Hà Nội'),
(2, 1, 'DAN2026', 'Da Nang Grand Prix (Đèo Hải Vân)', 45, 'Hai Van Pass Circuit', '2026-05-10 14:00:00', 2, 'Cung đường đua ven biển và vượt đèo đẹp nhất thế giới'),
(3, 1, 'HCM2026', 'Saigon Grand Prix (TP.HCM)', 60, 'District 1 Street Circuit', '2026-06-14 19:00:00', 3, 'Chặng đua đêm rực rỡ tại trung tâm kinh tế Việt Nam'),
(4, 1, 'HSA2026', 'Hoang Sa Grand Prix', 50, 'Paracel Islands Scenic Track', '2026-07-12 10:00:00', 4, 'Chặng đua mang ý nghĩa lịch sử và chủ quyền thiêng liêng'),
(5, 1, 'HPH2026', 'Hai Phòng Grand Prix', 52, 'Do Son Coastal Circuit', '2026-08-16 15:00:00', 5, 'Chặng đua tại thành phố Hoa phượng đỏ'),
(6, 1, 'CTO2026', 'Cần Thơ Grand Prix', 48, 'Mekong Delta Sprint', '2026-09-20 16:00:00', 6, 'Đường đua vùng sông nước miền Tây hữu tình');

-- ----------------------------------------------------------
-- 5. Teams (20 Teams)
-- ----------------------------------------------------------
INSERT INTO `team` (`id`, `team_code`, `name`, `brand`, `description`) VALUES
(1, 'RBR', 'Red Bull Racing', 'Red Bull', 'Đương kim vô địch (Có 3 tay đua ký HĐ)'),
(2, 'FER', 'Scuderia Ferrari', 'Ferrari', 'Huyền thoại nước Ý'),
(3, 'MER', 'Mercedes-AMG', 'Mercedes', 'Mũi tên bạc'),
(4, 'MCL', 'McLaren F1', 'McLaren', 'Tài năng trẻ bứt phá'),
(5, 'AMR', 'Aston Martin', 'Aston Martin', 'Đội đua hạng sang'),
(6, 'VNF', 'VinFast Racing', 'VinFast', 'Niềm tự hào Việt Nam (Có 3 tay đua ký HĐ)'),
(7, 'VMT', 'Viettel Motorsport', 'Viettel', 'Sức mạnh công nghệ Việt'),
(8, 'FPS', 'FPT Speed Team', 'FPT', 'Tốc độ trí tuệ Việt'),
(9, 'VNA', 'Vietnam Airlines Racing', 'VNA', 'Sải cánh cùng tốc độ'),
(10, 'THP', 'Tan Hiep Phat Racing', 'Number 1', 'Năng lượng bứt phá'),
(11, 'ALP', 'Alpine F1', 'Renault', 'Đại diện từ Pháp'),
(12, 'WIL', 'Williams Racing', 'Williams', 'Kỷ nguyên phục hưng'),
(13, 'HAA', 'Haas F1 Team', 'Haas', 'Đại diện Bắc Mỹ'),
(14, 'SAU', 'Kick Sauber', 'Audi', 'Sức mạnh từ Thụy Sĩ'),
(15, 'RBA', 'Visa Cash App RB', 'Honda', 'Đội đua tài năng trẻ'),
(16, 'AND', 'Andretti Global', 'Cadillac', 'Tân binh tham vọng'),
(17, 'LOT', 'Lotus Racing', 'Lotus', 'Huyền thoại trở lại'),
(18, 'TOY', 'Toyota Gazoo', 'Toyota', 'Gã khổng lồ Nhật Bản'),
(19, 'BMW', 'BMW Motorsport', 'BMW', 'Đẳng cấp cơ khí Đức'),
(20, 'BRS', 'Brawn GP', 'Brawn', 'Đội đua chỉ có 1 tay đua');

-- ----------------------------------------------------------
-- 6. Racers (44 Racers - Diversified)
-- ----------------------------------------------------------
INSERT INTO `racer` (`id`, `driver_code`, `name`, `nationality`, `dob`, `biography`, `status`) VALUES
-- Team 1: Red Bull (3 Racers)
(1, 'VER', 'Max Verstappen', 'Hà Lan', '1997-09-30', 'Siêu sao F1', 'Active'),
(2, 'PER', 'Sergio Pérez', 'Mexico', '1990-01-26', 'Vua phố', 'Active'),
(3, 'LAW', 'Liam Lawson', 'New Zealand', '2002-02-11', 'Tay đua dự bị Red Bull', 'Active'),

-- Team 2: Ferrari (2 Racers)
(4, 'LEC', 'Charles Leclerc', 'Monaco', '1997-10-16', 'Hoàng tử', 'Active'),
(5, 'SAI', 'Carlos Sainz', 'Tây Ban Nha', '1994-09-01', 'Lỳ lợm', 'Active'),

-- Team 3: Mercedes (2 Racers)
(6, 'HAM', 'Lewis Hamilton', 'Anh', '1985-01-07', 'Huyền thoại', 'Active'),
(7, 'RUS', 'George Russell', 'Anh', '1998-02-15', 'Tương lai', 'Active'),

-- Team 4-5
(8, 'NOR', 'Lando Norris', 'Anh', '1999-11-13', 'Ngôi sao trẻ', 'Active'),
(9, 'PIA', 'Oscar Piastri', 'Úc', '2001-04-06', 'Tân binh', 'Active'),
(10, 'ALO', 'Fernando Alonso', 'Tây Ban Nha', '1981-07-29', 'Lão tướng', 'Active'),
(11, 'STR', 'Lance Stroll', 'Canada', '1998-10-29', 'Thực lực', 'Active'),

-- Team 6: VinFast (3 Racers)
(12, 'HOA', 'Nguyễn Thái Hòa', 'Việt Nam', '2000-01-01', 'Chủ lực VinFast', 'Active'),
(13, 'LIN', 'Lê Hữu Đăng Lâm', 'Việt Nam', '1999-05-12', 'Vua phố Hà Nội', 'Active'),
(14, 'CUO', 'Nguyễn Tấn Cường', 'Việt Nam', '2001-02-20', 'Tay đua trẻ VinFast', 'Active'),

-- Team 7-9 (2 Racers each)
(15, 'NAM', 'Trần Hoàng Nam', 'Việt Nam', '1998-12-25', 'Viettel', 'Active'),
(16, 'MIN', 'Phạm Quang Minh', 'Việt Nam', '2001-07-08', 'Viettel', 'Active'),
(17, 'DUC', 'Lý Trung Đức', 'Việt Nam', '2002-03-15', 'FPT', 'Active'),
(18, 'HAI', 'Võ Hoàng Hải', 'Việt Nam', '1997-11-20', 'FPT', 'Active'),
(19, 'ANH', 'Đoàn Nhật Anh', 'Việt Nam', '1996-09-30', 'VNA', 'Active'),
(20, 'QUY', 'Ngô Phú Quý', 'Việt Nam', '2000-04-18', 'VNA', 'Active'),

-- Team 10-19 (Mix)
(21, 'SON', 'Trương Tấn Sơn', 'Việt Nam', '1995-10-10', 'Tan Hiep Phat', 'Active'),
(22, 'KHO', 'Đặng Minh Khoa', 'Việt Nam', '2003-01-20', 'Tan Hiep Phat', 'Active'),
(23, 'GAS', 'Pierre Gasly', 'Pháp', '1996-02-07', 'Alpine', 'Active'),
(24, 'OCO', 'Esteban Ocon', 'Pháp', '1996-09-17', 'Alpine', 'Active'),
(25, 'ALB', 'Alexander Albon', 'Thái Lan', '1996-03-23', 'Williams', 'Active'),
(26, 'DANG', 'Nguyễn Hải Đăng', 'Việt Nam', '1996-03-03', 'Williams', 'Active'),
(27, 'HUL', 'Nico Hülkenberg', 'Đức', '1987-08-19', 'Haas', 'Active'),
(28, 'MAG', 'Kevin Magnussen', 'Đan Mạch', '1992-10-05', 'Haas', 'Active'),
(29, 'RIC', 'Daniel Ricciardo', 'Úc', '1989-07-01', 'Sauber', 'Active'),
(30, 'BEA', 'Oliver Bearman', 'Anh', '2005-05-08', 'Sauber', 'Active'),
(31, 'TSU', 'Yuki Tsunoda', 'Nhật Bản', '2000-05-11', 'RB', 'Active'),
(32, 'HAD', 'Isack Hadjar', 'Pháp', '2004-09-28', 'RB', 'Active'),
(33, 'SAR', 'Logan Sargeant', 'Mỹ', '2000-12-31', 'Andretti', 'Active'),
(34, 'TIEN', 'Lương Minh Tiến', 'Việt Nam', '2000-12-12', 'Andretti', 'Active'),
(35, 'THI', 'Vương Đắc Thiện', 'Việt Nam', '1999-02-14', 'Lotus', 'Active'),
(36, 'KHAN', 'Lê Phan Minh Khang', 'Việt Nam', '2004-02-29', 'Lotus', 'Active'),
(37, 'PHI', 'Mai Vũ Phi', 'Việt Nam', '1997-05-05', 'Toyota', 'Active'),
(38, 'THAN', 'Chu Văn Thắng', 'Việt Nam', '1994-06-12', 'Toyota', 'Active'),
(39, 'TUN', 'Bùi Thanh Tùng', 'Việt Nam', '1998-08-08', 'BMW', 'Active'),
(40, 'NHAT', 'Phạm Văn Nhất', 'Việt Nam', '1997-01-28', 'BMW', 'Active'),

-- Team 20: Brawn GP (Only 1 Racer)
(41, 'BOB', 'Bùi Xuân Huấn', 'Việt Nam', '1985-07-01', 'Huyền thoại Brawn GP', 'Active'),

-- Additional Racers for testing
(42, 'LAM', 'Trịnh Thế Lâm', 'Việt Nam', '2001-11-11', 'Tay đua tự do', 'Active'),
(43, 'VIE', 'Hoàng Việt', 'Việt Nam', '2000-05-05', 'Tay đua thử nghiệm', 'Active'),
(44, 'SPO', 'Lê Văn Speed', 'Việt Nam', '1996-06-06', 'Phóng viên đua xe', 'Active');

-- ----------------------------------------------------------
-- 7. Contracts (Handling 1, 2, 3 racers per team)
-- ----------------------------------------------------------
INSERT INTO `contract` (`id`, `team_id`, `racer_id`, `start_date`, `end_date`) VALUES
-- Team 1 RBR (3 drivers)
(1, 1, 1, '2026-01-01', '2026-12-31'), (2, 1, 2, '2026-01-01', '2026-12-31'), (3, 1, 3, '2026-01-01', '2026-12-31'),
-- Team 2 FER (2 drivers)
(4, 2, 4, '2026-01-01', '2026-12-31'), (5, 2, 5, '2026-01-01', '2026-12-31'),
-- Team 3 MER (2 drivers)
(6, 3, 6, '2026-01-01', '2026-12-31'), (7, 3, 7, '2026-01-01', '2026-12-31'),
-- Team 4 MCL (2 drivers)
(8, 4, 8, '2026-01-01', '2026-12-31'), (9, 4, 9, '2026-01-01', '2026-12-31'),
-- Team 5 AMR (2 drivers)
(10, 5, 10, '2026-01-01', '2026-12-31'), (11, 5, 11, '2026-01-01', '2026-12-31'),
-- Team 6 VNF (3 drivers)
(12, 6, 12, '2026-01-01', '2026-12-31'), (13, 6, 13, '2026-01-01', '2026-12-31'), (14, 6, 14, '2026-01-01', '2026-12-31'),
-- Team 7-19 (2 drivers each) - Using IDs 15-40
(15, 7, 15, '2026-01-01', '2026-12-31'), (16, 7, 16, '2026-01-01', '2026-12-31'),
(17, 8, 17, '2026-01-01', '2026-12-31'), (18, 8, 18, '2026-01-01', '2026-12-31'),
(19, 9, 19, '2026-01-01', '2026-12-31'), (20, 9, 20, '2026-01-01', '2026-12-31'),
(21, 10, 21, '2026-01-01', '2026-12-31'), (22, 10, 22, '2026-01-01', '2026-12-31'),
(23, 11, 23, '2026-01-01', '2026-12-31'), (24, 11, 24, '2026-01-01', '2026-12-31'),
(25, 12, 25, '2026-01-01', '2026-12-31'), (26, 12, 26, '2026-01-01', '2026-12-31'),
(27, 13, 27, '2026-01-01', '2026-12-31'), (28, 13, 28, '2026-01-01', '2026-12-31'),
(29, 14, 29, '2026-01-01', '2026-12-31'), (30, 14, 30, '2026-01-01', '2026-12-31'),
(31, 15, 31, '2026-01-01', '2026-12-31'), (32, 15, 32, '2026-01-01', '2026-12-31'),
(33, 16, 33, '2026-01-01', '2026-12-31'), (34, 16, 34, '2026-01-01', '2026-12-31'),
(35, 17, 35, '2026-01-01', '2026-12-31'), (36, 17, 36, '2026-01-01', '2026-12-31'),
(37, 18, 37, '2026-01-01', '2026-12-31'), (38, 18, 38, '2026-01-01', '2026-12-31'),
(39, 19, 39, '2026-01-01', '2026-12-31'), (40, 19, 40, '2026-01-01', '2026-12-31'),
-- Team 20 BRS (1 driver)
(41, 20, 41, '2026-01-01', '2026-12-31');

-- ----------------------------------------------------------
-- 8. Registrations (Initial set: 2 drivers per team for Hanoi)
-- ----------------------------------------------------------
INSERT INTO `registration` (`stage_id`, `contract_id`) 
VALUES 
(1, 1), (1, 2), -- RBR (Đã 2, còn Lawson ID 3 chưa đk)
(1, 4), (1, 5), -- FER
(1, 6), (1, 7), -- MER
(1, 12), (1, 13), -- VNF (Đã 2, còn Tấn Cường ID 14 chưa đk)
(1, 15), (1, 16), (1, 17), (1, 18), (1, 19), (1, 20), (1, 21), (1, 22), 
(1, 23), (1, 24), (1, 25), (1, 26), (1, 27), (1, 28), (1, 29), (1, 30),
(1, 41); -- BRS (Chỉ có 1 người)

-- ----------------------------------------------------------
-- 9. Some Results
-- ----------------------------------------------------------
INSERT INTO `result` (`stage_id`, `contract_id`, `finish_time`, `laps_completed`, `finish_rank`, `status`, `updated_by`) VALUES
(1, 12, '1:28:44.891', 55, 1, 'Finished', 2), -- Thái Hòa P1
(1, 1, '1:29:12.445', 55, 2, 'Finished', 2);  -- Max Verstappen P2
