-- ================================================================
--  GeoFence System V2 — Complete Database Schema
--  Run this once in `registerdb` (phpMyAdmin > SQL tab)
-- ================================================================

-- 1. Extend usernamepass
ALTER TABLE `usernamepass`
  ADD COLUMN IF NOT EXISTS `role`    ENUM('admin','student') NOT NULL DEFAULT 'student',
  ADD COLUMN IF NOT EXISTS `course`  VARCHAR(10)  DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `year`    TINYINT(1)   DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `section` VARCHAR(10)  DEFAULT NULL;

UPDATE `usernamepass` SET `role` = 'admin' WHERE `role` IS NULL OR `role` = '';

-- 2. Geofences table
CREATE TABLE IF NOT EXISTS `geofences` (
    `id`           INT          NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(255) NOT NULL,
    `start_date`   DATE         DEFAULT NULL,
    `start_time`   TIME         DEFAULT NULL,
    `end_date`     DATE         DEFAULT NULL,
    `end_time`     TIME         DEFAULT NULL,
    `description`  TEXT         DEFAULT NULL,
    `coordinates`  LONGTEXT     NOT NULL,
    `allowed_ips`  TEXT         DEFAULT NULL,
    `area_sqm`     FLOAT        DEFAULT NULL,
    `perimeter_m`  FLOAT        DEFAULT NULL,
    `require_gps`  TINYINT(1)   NOT NULL DEFAULT 1,
    `require_ip`   TINYINT(1)   NOT NULL DEFAULT 1,
    `created_by`   INT          DEFAULT NULL,
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Safe upgrade for existing geofences table
ALTER TABLE `geofences`
  ADD COLUMN IF NOT EXISTS `allowed_ips`  TEXT       DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `area_sqm`     FLOAT      DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `perimeter_m`  FLOAT      DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `require_gps`  TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `require_ip`   TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS `created_by`   INT        DEFAULT NULL;

-- 3. Attendance log
CREATE TABLE IF NOT EXISTS `event_attendance` (
    `id`            INT           NOT NULL AUTO_INCREMENT,
    `geofence_id`   INT           NOT NULL,
    `user_id`       INT           NOT NULL,
    `time_in`       DATETIME      NOT NULL,
    `time_out`      DATETIME      DEFAULT NULL,
    `ip_address`    VARCHAR(60)   DEFAULT NULL,
    `gps_lat`       DECIMAL(11,8) DEFAULT NULL,
    `gps_lng`       DECIMAL(11,8) DEFAULT NULL,
    `access_method` VARCHAR(20)   DEFAULT 'gps+ip',
    `status`        ENUM('inside','outside','manual') NOT NULL DEFAULT 'inside',
    PRIMARY KEY (`id`),
    KEY `idx_gf`   (`geofence_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_tin`  (`time_in`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Courses reference
CREATE TABLE IF NOT EXISTS `courses` (
    `id`   INT          NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(10)  NOT NULL UNIQUE,
    `name` VARCHAR(120) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `courses` (`code`,`name`) VALUES
  ('BSIT','BS Information Technology'),
  ('BSCS','BS Computer Science'),
  ('BSBA','BS Business Administration');

-- ================================================================
--  Sample students  (use add_student.php to set proper hashed passwords)
-- ================================================================
INSERT IGNORE INTO `usernamepass` (`UserName`,`Email`,`Password`,`role`,`course`,`year`,`section`) VALUES
  ('Juan Dela Cruz',  'juan.dc@school.edu',  MD5('pass123'),'student','BSIT',1,'1A'),
  ('Maria Santos',    'maria.s@school.edu',  MD5('pass123'),'student','BSIT',1,'1A'),
  ('Pedro Reyes',     'pedro.r@school.edu',  MD5('pass123'),'student','BSIT',1,'1B'),
  ('Ana Lim',         'ana.l@school.edu',    MD5('pass123'),'student','BSIT',2,'2A'),
  ('Carlo Mendoza',   'carlo.m@school.edu',  MD5('pass123'),'student','BSIT',2,'2B'),
  ('Lea Torres',      'lea.t@school.edu',    MD5('pass123'),'student','BSIT',3,'3A'),
  ('Mark Villanueva', 'mark.v@school.edu',   MD5('pass123'),'student','BSIT',4,'4A'),
  ('Joyce Aquino',    'joyce.a@school.edu',  MD5('pass123'),'student','BSIT',4,'4A'),
  ('Ben Santos',      'ben.s@school.edu',    MD5('pass123'),'student','BSCS',1,'1A'),
  ('Claire Dizon',    'claire.d@school.edu', MD5('pass123'),'student','BSCS',2,'2A'),
  ('Dave Cruz',       'dave.c@school.edu',   MD5('pass123'),'student','BSCS',3,'3A'),
  ('Ella Ramos',      'ella.r@school.edu',   MD5('pass123'),'student','BSBA',1,'1A'),
  ('Felix Navarro',   'felix.n@school.edu',  MD5('pass123'),'student','BSBA',2,'2A'),
  ('Grace Ocampo',    'grace.o@school.edu',  MD5('pass123'),'student','BSBA',3,'3A');

-- Sample geofences
INSERT IGNORE INTO `geofences`
  (`name`,`start_date`,`start_time`,`end_date`,`end_time`,`description`,`coordinates`,`allowed_ips`,`area_sqm`,`perimeter_m`,`require_gps`,`require_ip`)
VALUES
  ('Campus Main Gate','2026-06-01','07:00:00','2026-12-31','18:00:00','Primary campus access zone',
   '[{"lat":14.6508,"lng":121.0486},{"lat":14.6515,"lng":121.0493},{"lat":14.6507,"lng":121.0500},{"lat":14.6500,"lng":121.0492}]',
   '["192.168.1.0/24","10.0.0.0/8"]',3200.0,240.0,1,1),
  ('Auditorium Zone','2026-07-15','08:00:00','2026-07-15','20:00:00','Graduation ceremony area',
   '[{"lat":14.6518,"lng":121.0483},{"lat":14.6524,"lng":121.0489},{"lat":14.6519,"lng":121.0495},{"lat":14.6513,"lng":121.0489}]',
   '["192.168.2.0/24"]',2100.0,190.0,1,1),
  ('Library Zone','2026-06-01','06:00:00','2026-12-31','22:00:00','Study and reading area',
   '[{"lat":14.6503,"lng":121.0495},{"lat":14.6508,"lng":121.0501},{"lat":14.6503,"lng":121.0506},{"lat":14.6498,"lng":121.0500}]',
   '["192.168.3.0/24"]',1800.0,172.0,1,0);
