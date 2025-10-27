<?php
/**
 * Roadside Assistance Admin Platform - Initial Database Setup
 * Migration file for setting up the database structure
 */

require_once '../../config.php';

class InitialSetupMigration {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function up() {
        try {
            echo "Starting initial database setup...\n";

            // Read and execute schema file
            $schemaFile = __DIR__ . '/../schema.sql';
            if (!file_exists($schemaFile)) {
                throw new Exception("Schema file not found: $schemaFile");
            }

            $schema = file_get_contents($schemaFile);
            $statements = $this->splitSQLStatements($schema);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    echo "Executing: " . substr($statement, 0, 50) . "...\n";
                    $this->db->query($statement);
                }
            }

            // Record migration
            $this->recordMigration('001_initial_setup');

            echo "Initial setup completed successfully!\n";
            return true;

        } catch (Exception $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function down() {
        try {
            echo "Rolling back initial setup...\n";

            // Drop tables in reverse order (respecting foreign keys)
            $tables = [
                'reports',
                'user_sessions',
                'login_attempts',
                'activity_logs',
                'service_requests',
                'service_types',
                'drivers',
                'customer_vehicles',
                'customers',
                'settings',
                'users'
            ];

            foreach ($tables as $table) {
                echo "Dropping table: $table\n";
                $this->db->query("DROP TABLE IF EXISTS $table");
            }

            // Remove migration record
            $this->removeMigration('001_initial_setup');

            echo "Rollback completed successfully!\n";
            return true;

        } catch (Exception $e) {
            echo "Rollback failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function splitSQLStatements($sql) {
        // Split SQL file into individual statements
        $statements = [];
        $lines = explode("\n", $sql);
        $currentStatement = '';

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (empty($line) || preg_match('/^--/', $line)) {
                continue;
            }

            $currentStatement .= $line . "\n";

            // Check if statement ends with semicolon
            if (substr($line, -1) === ';') {
                $statements[] = $currentStatement;
                $currentStatement = '';
            }
        }

        return $statements;
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
    $migration = new InitialSetupMigration();

    if ($argc > 1 && $argv[1] === 'down') {
        $migration->down();
    } else {
        $migration->up();
    }
}
?>
