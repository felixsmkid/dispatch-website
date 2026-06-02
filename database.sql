-- Satu Mimpi Central Dispatch (SMCD) Database Schema
-- MySQL 5.7+ / MariaDB 10.3+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS smcd_dispatch CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smcd_dispatch;

DROP TABLE IF EXISTS call_assignments;
DROP TABLE IF EXISTS pursuit_units;
DROP TABLE IF EXISTS panic_alerts;
DROP TABLE IF EXISTS calls;
DROP TABLE IF EXISTS pursuits;
DROP TABLE IF EXISTS bolos;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('developer', 'dispatch', 'lspd', 'bcso') NOT NULL,
    display_name VARCHAR(100) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE units (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    character_name VARCHAR(100) NOT NULL,
    callsign VARCHAR(20) NOT NULL,
    rank_title VARCHAR(50) NOT NULL,
    department ENUM('LSPD', 'BCSO') NOT NULL,
    status_code VARCHAR(10) NOT NULL DEFAULT '10-8',
    status_label VARCHAR(50) NOT NULL DEFAULT 'Available',
    is_online TINYINT(1) NOT NULL DEFAULT 1,
    last_update DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_unit (user_id),
    CONSTRAINT fk_units_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE calls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    call_number VARCHAR(20) NOT NULL UNIQUE,
    caller_name VARCHAR(100) DEFAULT NULL,
    phone_number VARCHAR(30) DEFAULT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'emergency') NOT NULL DEFAULT 'medium',
    status ENUM('pending', 'active', 'closed') NOT NULL DEFAULT 'pending',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_calls_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE call_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    call_id INT UNSIGNED NOT NULL,
    unit_id INT UNSIGNED NOT NULL,
    assignment_type ENUM('primary', 'secondary', 'assigned') NOT NULL DEFAULT 'assigned',
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_call_unit (call_id, unit_id),
    CONSTRAINT fk_ca_call FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE CASCADE,
    CONSTRAINT fk_ca_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pursuits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pursuit_code VARCHAR(20) NOT NULL UNIQUE,
    vehicle_description VARCHAR(255) NOT NULL,
    plate VARCHAR(20) DEFAULT NULL,
    occupants VARCHAR(255) DEFAULT NULL,
    charges TEXT DEFAULT NULL,
    current_location VARCHAR(255) DEFAULT NULL,
    primary_unit_id INT UNSIGNED DEFAULT NULL,
    secondary_unit_id INT UNSIGNED DEFAULT NULL,
    pit_authorized TINYINT(1) NOT NULL DEFAULT 0,
    spike_authorized TINYINT(1) NOT NULL DEFAULT 0,
    air_unit_requested TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active', 'ended') NOT NULL DEFAULT 'active',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ended_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_pursuit_primary FOREIGN KEY (primary_unit_id) REFERENCES units(id) ON DELETE SET NULL,
    CONSTRAINT fk_pursuit_secondary FOREIGN KEY (secondary_unit_id) REFERENCES units(id) ON DELETE SET NULL,
    CONSTRAINT fk_pursuit_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pursuit_units (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pursuit_id INT UNSIGNED NOT NULL,
    unit_id INT UNSIGNED NOT NULL,
    added_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pursuit_unit (pursuit_id, unit_id),
    CONSTRAINT fk_pu_pursuit FOREIGN KEY (pursuit_id) REFERENCES pursuits(id) ON DELETE CASCADE,
    CONSTRAINT fk_pu_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bolos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle VARCHAR(100) NOT NULL,
    plate VARCHAR(20) DEFAULT NULL,
    description TEXT NOT NULL,
    reason VARCHAR(255) NOT NULL,
    bolo_date DATE NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_by INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bolo_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE panic_alerts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    unit_id INT UNSIGNED NOT NULL,
    officer_name VARCHAR(100) NOT NULL,
    callsign VARCHAR(20) NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    department ENUM('LSPD', 'BCSO') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME DEFAULT NULL,
    resolved_by INT UNSIGNED DEFAULT NULL,
    CONSTRAINT fk_panic_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    CONSTRAINT fk_panic_resolved_by FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default accounts (passwords: developer123 / dispatch123)
INSERT IGNORE INTO users (username, password, role, display_name) VALUES
('developer', '$2b$12$EPl1QFkJgvP08kn7GKHxvuQLqGa5knOCHFOKKNamZz.QdboI8mgUO', 'developer', 'System Developer'),
('dispatch', '$2b$12$.tujtSIdQ/JapwXpQwKOvevaoPea3RW.gpqz4kAf6HiSAq9zdJoNq', 'dispatch', 'Central Dispatcher');

SET FOREIGN_KEY_CHECKS = 1;

-- Sample officer accounts (optional, for testing)
-- LSPD: username lspd1 / password lspd123
-- BCSO: username bcso1 / password bcso123
INSERT IGNORE INTO users (username, password, role, display_name) VALUES
('lspd1', '$2b$12$wlqlp3LCUEFfUTe39K8HDOTvAAn9dyQ0QbT1gjviLx8DuHl4zhNGi', 'lspd', 'LSPD Officer Demo'),
('bcso1', '$2b$12$XSm4PupkD/DdE0kaZwxSUOA2G0fHw6oWbqhvpb.RJQitbXVx6LLuu', 'bcso', 'BCSO Deputy Demo');
