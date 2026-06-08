-- =====================================================
--  GeoFence Dashboard — Database Schema
--  Run this in your MySQL database to set up tables
-- =====================================================

-- Create the geofences table
CREATE TABLE IF NOT EXISTS `geofences` (
    `id`          INT(11)       NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(255)  NOT NULL,
    `start_date`  DATE          DEFAULT NULL,
    `start_time`  TIME          DEFAULT NULL,
    `end_date`    DATE          DEFAULT NULL,
    `end_time`    TIME          DEFAULT NULL,
    `description` TEXT          DEFAULT NULL,
    `coordinates` LONGTEXT      NOT NULL COMMENT 'JSON array of {lat, lng} objects',
    `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_start_date` (`start_date`),
    KEY `idx_end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
--  Make sure your usernamepass table exists too.
--  If it doesn't yet, here's a starter schema:
-- =====================================================

CREATE TABLE IF NOT EXISTS `usernamepass` (
    `ID`       INT(11)      NOT NULL AUTO_INCREMENT,
    `UserName` VARCHAR(100) NOT NULL,
    `Email`    VARCHAR(150) NOT NULL UNIQUE,
    `Password` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
--  Sample geofence data for testing analytics
-- =====================================================

INSERT INTO `geofences` (`name`, `start_date`, `start_time`, `end_date`, `end_time`, `description`, `coordinates`) VALUES
('Campus Event Zone',     '2026-01-10', '08:00:00', '2026-01-10', '18:00:00', 'Annual campus gathering area.',  '[{"lat":14.651,"lng":121.049},{"lat":14.652,"lng":121.050},{"lat":14.650,"lng":121.051}]'),
('Concert Perimeter',     '2026-02-14', '17:00:00', '2026-02-14', '23:00:00', "Valentine's night concert zone.", '[{"lat":14.653,"lng":121.047},{"lat":14.654,"lng":121.048},{"lat":14.652,"lng":121.049}]'),
('Sports Field Boundary', '2026-03-20', '09:00:00', '2026-03-20', '16:00:00', 'Basketball tournament area.',    '[{"lat":14.649,"lng":121.050},{"lat":14.650,"lng":121.052},{"lat":14.648,"lng":121.051}]'),
('Market Zone Q2',        '2026-04-05', '06:00:00', '2026-04-05', '20:00:00', 'Saturday market perimeter.',     '[{"lat":14.655,"lng":121.046},{"lat":14.656,"lng":121.047},{"lat":14.654,"lng":121.048}]'),
('Upcoming Bazaar',       '2026-06-15', '10:00:00', '2026-06-15', '22:00:00', 'Summer bazaar zone.',            '[{"lat":14.647,"lng":121.053},{"lat":14.648,"lng":121.054},{"lat":14.646,"lng":121.053}]'),
('Future Conference',     '2026-07-20', '08:00:00', '2026-07-22', '18:00:00', 'International conference area.', '[{"lat":14.660,"lng":121.045},{"lat":14.661,"lng":121.046},{"lat":14.659,"lng":121.047}]');
