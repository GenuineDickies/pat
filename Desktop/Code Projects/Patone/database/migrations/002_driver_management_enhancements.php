<?php
/**
 * Roadside Assistance Admin Platform - Driver Management Enhancements
 * Migration for adding driver certifications and availability scheduling
 */

require_once '../../config.php';

class DriverManagementEnhancementsMigration {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function up() {
        try {
            echo "Starting driver management enhancements migration...\n";

            // Create driver_certifications table
            echo "Creating driver_certifications table...\n";
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `driver_certifications` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `driver_id` INT UNSIGNED NOT NULL,
                    `certification_type` VARCHAR(100) NOT NULL COMMENT 'e.g., CDL, First Aid, Towing License',
                    `certification_number` VARCHAR(100) NULL,
                    `issuing_authority` VARCHAR(255) NULL,
                    `issue_date` DATE NULL,
                    `expiry_date` DATE NULL,
                    `status` ENUM('active', 'expired', 'pending', 'suspended') NOT NULL DEFAULT 'active',
                    `document_path` VARCHAR(255) NULL COMMENT 'Path to uploaded certificate document',
                    `notes` TEXT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE,
                    INDEX `idx_driver` (`driver_id`),
                    INDEX `idx_status` (`status`),
                    INDEX `idx_expiry` (`expiry_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Create driver_availability_schedule table
            echo "Creating driver_availability_schedule table...\n";
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `driver_availability_schedule` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `driver_id` INT UNSIGNED NOT NULL,
                    `day_of_week` TINYINT NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
                    `start_time` TIME NOT NULL,
                    `end_time` TIME NOT NULL,
                    `is_available` BOOLEAN DEFAULT TRUE,
                    `notes` VARCHAR(255) NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE,
                    INDEX `idx_driver` (`driver_id`),
                    INDEX `idx_day` (`day_of_week`),
                    INDEX `idx_available` (`is_available`),
                    UNIQUE KEY `unique_schedule` (`driver_id`, `day_of_week`, `start_time`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Create driver_documents table for general documents
            echo "Creating driver_documents table...\n";
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `driver_documents` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `driver_id` INT UNSIGNED NOT NULL,
                    `document_type` VARCHAR(100) NOT NULL COMMENT 'e.g., Insurance, Vehicle Registration, Background Check',
                    `document_name` VARCHAR(255) NOT NULL,
                    `file_path` VARCHAR(255) NOT NULL,
                    `file_size` INT NULL COMMENT 'Size in bytes',
                    `mime_type` VARCHAR(100) NULL,
                    `expiry_date` DATE NULL,
                    `status` ENUM('active', 'expired', 'pending_review') NOT NULL DEFAULT 'pending_review',
                    `uploaded_by` INT UNSIGNED NULL,
                    `notes` TEXT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
                    INDEX `idx_driver` (`driver_id`),
                    INDEX `idx_type` (`document_type`),
                    INDEX `idx_status` (`status`),
                    INDEX `idx_expiry` (`expiry_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            // Add workload tracking columns to drivers table if they don't exist
            echo "Adding workload tracking fields to drivers table...\n";
            
            // Check if columns exist before adding
            $columns = $this->db->getRows("SHOW COLUMNS FROM drivers LIKE 'current_workload'");
            if (empty($columns)) {
                $this->db->query("ALTER TABLE drivers ADD COLUMN `current_workload` INT DEFAULT 0 COMMENT 'Number of active requests' AFTER `completed_jobs`");
            }
            
            $columns = $this->db->getRows("SHOW COLUMNS FROM drivers LIKE 'max_workload'");
            if (empty($columns)) {
                $this->db->query("ALTER TABLE drivers ADD COLUMN `max_workload` INT DEFAULT 3 COMMENT 'Maximum concurrent requests' AFTER `current_workload`");
            }
            
            $columns = $this->db->getRows("SHOW COLUMNS FROM drivers LIKE 'availability_notes'");
            if (empty($columns)) {
                $this->db->query("ALTER TABLE drivers ADD COLUMN `availability_notes` TEXT NULL COMMENT 'Notes about driver availability' AFTER `notes`");
            }

            // Record migration
            $this->recordMigration('002_driver_management_enhancements');

            echo "Driver management enhancements migration completed successfully!\n";
            return true;

        } catch (Exception $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function down() {
        try {
            echo "Rolling back driver management enhancements...\n";

            // Drop tables
            echo "Dropping driver_documents table...\n";
            $this->db->query("DROP TABLE IF EXISTS driver_documents");

            echo "Dropping driver_availability_schedule table...\n";
            $this->db->query("DROP TABLE IF EXISTS driver_availability_schedule");

            echo "Dropping driver_certifications table...\n";
            $this->db->query("DROP TABLE IF EXISTS driver_certifications");

            // Remove added columns from drivers table
            echo "Removing workload tracking fields from drivers table...\n";
            $this->db->query("ALTER TABLE drivers DROP COLUMN IF EXISTS availability_notes");
            $this->db->query("ALTER TABLE drivers DROP COLUMN IF EXISTS max_workload");
            $this->db->query("ALTER TABLE drivers DROP COLUMN IF EXISTS current_workload");

            // Remove migration record
            $this->removeMigration('002_driver_management_enhancements');

            echo "Rollback completed successfully!\n";
            return true;

        } catch (Exception $e) {
            echo "Rollback failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function recordMigration($version) {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS migrations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                version VARCHAR(50) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_version (version)
            )"
        );

        $this->db->query(
            "INSERT INTO migrations (version) VALUES (?)",
            [$version]
        );
    }

    private function removeMigration($version) {
        $this->db->query("DELETE FROM migrations WHERE version = ?", [$version]);
    }
}

// Handle command line execution
if (php_sapi_name() === 'cli') {
    $migration = new DriverManagementEnhancementsMigration();

    if ($argc > 1 && $argv[1] === 'down') {
        $migration->down();
    } else {
        $migration->up();
    }
}
?>
