-- ============================================================
-- F1 FORMULA CHAMPIONSHIP MANAGEMENT
-- 01_schema.sql — Tables, Foreign Keys, Constraints
-- Database: f1_championship | Engine: InnoDB | Charset: utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ----------------------------------------------------------
-- 1. User (must be created first — referenced by result)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `username`  VARCHAR(50) NOT NULL UNIQUE,
    `password`  VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role`      ENUM('admin', 'staff') DEFAULT 'staff',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 2. Organization
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `organization` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100) NOT NULL,
    `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 3. Tournament (belongs to Organization)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tournament` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `organization_id` INT NOT NULL,
    `name`            VARCHAR(100) NOT NULL,
    `year`            INT NOT NULL,
    `start_date`      DATE,
    `end_date`        DATE,
    `description`     TEXT,
    FOREIGN KEY (`organization_id`) REFERENCES `organization`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 4. Stage (belongs to Tournament)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stage` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `tournament_id` INT NOT NULL,
    `stage_code`    VARCHAR(20) NOT NULL UNIQUE,
    `name`          VARCHAR(100) NOT NULL,
    `number_laps`   INT NOT NULL,
    `location`      VARCHAR(100) NOT NULL,
    `race_date`     DATETIME NOT NULL,
    `stage_order`   INT NOT NULL,
    `description`   TEXT,
    FOREIGN KEY (`tournament_id`) REFERENCES `tournament`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 5. Team
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `team` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `team_code`   VARCHAR(20) NOT NULL UNIQUE,
    `name`        VARCHAR(100) NOT NULL,
    `brand`       VARCHAR(100),
    `description` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 6. Racer
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `racer` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `driver_code` VARCHAR(20) NOT NULL UNIQUE,
    `name`        VARCHAR(100) NOT NULL,
    `nationality` VARCHAR(50),
    `dob`         DATE,
    `biography`   TEXT,
    `status`      ENUM('Active', 'Injured', 'Suspended') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 7. Contract (connects Racer <-> Team for a period)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contract` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `team_id`    INT NOT NULL,
    `racer_id`   INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date`   DATE NOT NULL,
    FOREIGN KEY (`team_id`) REFERENCES `team`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`racer_id`) REFERENCES `racer`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 8. Registration (which contracts are registered for which stage)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `registration` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `stage_id`      INT NOT NULL,
    `contract_id`   INT NOT NULL,
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`stage_id`) REFERENCES `stage`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`contract_id`) REFERENCES `contract`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY `unique_registration` (`stage_id`, `contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- 9. Result (race outcomes — core table)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `result` (
    `id`             INT AUTO_INCREMENT PRIMARY KEY,
    `stage_id`       INT NOT NULL,
    `contract_id`    INT NOT NULL,
    `finish_time`    VARCHAR(20) DEFAULT NULL,
    `laps_completed` INT NOT NULL DEFAULT 0,
    `finish_rank`    INT DEFAULT NULL,
    `score`          INT DEFAULT 0,
    `status`         ENUM('Finished', 'DNF', 'Accident') NOT NULL DEFAULT 'Finished',
    `updated_by`     INT DEFAULT NULL,
    `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`stage_id`) REFERENCES `stage`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`contract_id`) REFERENCES `contract`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`updated_by`) REFERENCES `user`(`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE KEY `unique_result` (`stage_id`, `contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
