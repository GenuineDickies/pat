-- Roadside Assistance Admin Platform - Database Schema
-- Version: 1.0
-- Description: Complete database structure for the Patone v1.0 platform

-- Create database (if needed)
-- CREATE DATABASE IF NOT EXISTS roadside_assistance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE roadside_assistance;

-- ============================================
-- Users Table
-- ============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `role` ENUM('admin', 'manager', 'dispatcher', 'driver') NOT NULL DEFAULT 'dispatcher',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Customers Table
-- ============================================
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `emergency_contact` VARCHAR(20) NULL,
    `date_of_birth` DATE NULL,
    `address` VARCHAR(255) NOT NULL,
    `address2` VARCHAR(255) NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(50) NOT NULL,
    `zip` VARCHAR(20) NOT NULL,
    `is_vip` BOOLEAN DEFAULT FALSE,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`last_name`, `first_name`),
    INDEX `idx_email` (`email`),
    INDEX `idx_phone` (`phone`),
    INDEX `idx_status` (`status`),
    INDEX `idx_is_vip` (`is_vip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Customer Vehicles Table
-- ============================================
CREATE TABLE IF NOT EXISTS `customer_vehicles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `make` VARCHAR(50) NOT NULL,
    `model` VARCHAR(50) NOT NULL,
    `year` INT NOT NULL,
    `color` VARCHAR(30) NULL,
    `license_plate` VARCHAR(20) NULL,
    `vin` VARCHAR(17) NULL,
    `is_primary` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_license_plate` (`license_plate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Drivers Table
-- ============================================
CREATE TABLE IF NOT EXISTS `drivers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `license_number` VARCHAR(50) NOT NULL,
    `license_state` VARCHAR(50) NOT NULL,
    `license_expiry` DATE NOT NULL,
    `vehicle_info` VARCHAR(255) NULL,
    `status` ENUM('available', 'busy', 'offline', 'on_break') NOT NULL DEFAULT 'offline',
    `current_latitude` DECIMAL(10, 8) NULL,
    `current_longitude` DECIMAL(11, 8) NULL,
    `last_location_update` DATETIME NULL,
    `rating` DECIMAL(3, 2) DEFAULT 0.00,
    `total_jobs` INT DEFAULT 0,
    `completed_jobs` INT DEFAULT 0,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_name` (`last_name`, `first_name`),
    INDEX `idx_status` (`status`),
    INDEX `idx_email` (`email`),
    INDEX `idx_location` (`current_latitude`, `current_longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Service Types Table
-- ============================================
CREATE TABLE IF NOT EXISTS `service_types` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `base_price` DECIMAL(10, 2) DEFAULT 0.00,
    `estimated_duration` INT DEFAULT 30 COMMENT 'Duration in minutes',
    `is_active` BOOLEAN DEFAULT TRUE,
    `priority` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Service Requests Table
-- ============================================
CREATE TABLE IF NOT EXISTS `service_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `driver_id` INT UNSIGNED NULL,
    `service_type_id` INT UNSIGNED NOT NULL,
    `vehicle_id` INT UNSIGNED NULL,
    `status` ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `priority` ENUM('low', 'normal', 'high', 'emergency') NOT NULL DEFAULT 'normal',
    `location_address` VARCHAR(255) NOT NULL,
    `location_city` VARCHAR(100) NOT NULL,
    `location_state` VARCHAR(50) NOT NULL,
    `location_latitude` DECIMAL(10, 8) NULL,
    `location_longitude` DECIMAL(11, 8) NULL,
    `description` TEXT NULL,
    `estimated_cost` DECIMAL(10, 2) NULL,
    `final_cost` DECIMAL(10, 2) NULL,
    `assigned_at` DATETIME NULL,
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `cancelled_at` DATETIME NULL,
    `cancellation_reason` TEXT NULL,
    `customer_notes` TEXT NULL,
    `driver_notes` TEXT NULL,
    `internal_notes` TEXT NULL,
    `rating` INT NULL COMMENT 'Customer rating 1-5',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`service_type_id`) REFERENCES `service_types`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`vehicle_id`) REFERENCES `customer_vehicles`(`id`) ON DELETE SET NULL,
    INDEX `idx_customer` (`customer_id`),
    INDEX `idx_driver` (`driver_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_priority` (`priority`),
    INDEX `idx_created` (`created_at`),
    INDEX `idx_location` (`location_latitude`, `location_longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Activity Logs Table
-- ============================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) NULL,
    `entity_id` INT UNSIGNED NULL,
    `description` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Settings Table
-- ============================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_type` ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
    `description` TEXT NULL,
    `is_public` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Reports Table
-- ============================================
CREATE TABLE IF NOT EXISTS `reports` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `report_type` VARCHAR(50) NOT NULL,
    `report_name` VARCHAR(255) NOT NULL,
    `generated_by` INT UNSIGNED NULL,
    `parameters` TEXT NULL COMMENT 'JSON encoded parameters',
    `file_path` VARCHAR(255) NULL,
    `status` ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME NULL,
    FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_type` (`report_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Login Attempts Table (Security)
-- ============================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `success` BOOLEAN NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- User Sessions Table
-- ============================================
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `session_token` VARCHAR(255) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_token` (`session_token`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Default Data
-- ============================================

-- Default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`, `status`) VALUES
('admin', 'admin@roadsideassistance.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'active');

-- Default service types
INSERT INTO `service_types` (`name`, `description`, `base_price`, `estimated_duration`, `is_active`, `priority`) VALUES
('Flat Tire Change', 'Change flat tire with spare', 50.00, 30, TRUE, 3),
('Jump Start', 'Battery jump start service', 40.00, 20, TRUE, 4),
('Fuel Delivery', 'Emergency fuel delivery (up to 2 gallons)', 45.00, 25, TRUE, 3),
('Lockout Service', 'Vehicle lockout assistance', 55.00, 30, TRUE, 2),
('Towing', 'Vehicle towing service', 100.00, 60, TRUE, 5),
('Winch Out', 'Pull vehicle out of ditch/mud', 75.00, 45, TRUE, 4),
('Battery Replacement', 'On-site battery replacement', 120.00, 40, TRUE, 3),
('Minor Repair', 'Minor mechanical repairs on-site', 80.00, 60, TRUE, 2);

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `is_public`) VALUES
('site_name', 'Roadside Assistance Admin', 'string', 'Site name', TRUE),
('site_email', 'admin@roadsideassistance.com', 'string', 'Site email', FALSE),
('max_dispatch_distance', '50', 'integer', 'Maximum distance in miles for auto-dispatch', FALSE),
('default_service_radius', '25', 'integer', 'Default service radius in miles', FALSE),
('enable_gps_tracking', 'true', 'boolean', 'Enable GPS tracking for drivers', FALSE),
('enable_notifications', 'true', 'boolean', 'Enable email/SMS notifications', FALSE),
('notification_email', 'notifications@roadsideassistance.com', 'string', 'Notification email address', FALSE),
('business_hours_start', '08:00', 'string', 'Business hours start time', TRUE),
('business_hours_end', '20:00', 'string', 'Business hours end time', TRUE),
('timezone', 'America/New_York', 'string', 'System timezone', FALSE);

-- ============================================
-- End of Schema
-- ============================================
