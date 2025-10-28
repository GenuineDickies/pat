<?php
/**
 * Roadside Assistance Admin Platform - Dispatch System Migration
 * Migration file for setting up the dispatch system tables
 */

require_once '../../config.php';

class DispatchSystemMigration {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function up() {
        try {
            echo "Starting dispatch system migration...\n";

            // Read and execute migration SQL file
            $migrationFile = __DIR__ . '/002_dispatch_system.sql';
            if (!file_exists($migrationFile)) {
                throw new Exception("Migration file not found: $migrationFile");
            }

            $sql = file_get_contents($migrationFile);
            $statements = $this->splitSQLStatements($sql);

            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    echo "Executing: " . substr($statement, 0, 80) . "...\n";
                    $this->db->query($statement);
                }
            }

            echo "Dispatch system migration completed successfully!\n";
            return true;

        } catch (Exception $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
            echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
            return false;
        }
    }

    public function down() {
        try {
            echo "Rolling back dispatch system migration...\n";

            // Drop tables in reverse order to respect foreign keys
            $tables = [
                'driver_performance',
                'driver_certifications',
                'dispatch_history',
                'dispatch_queue'
            ];

            foreach ($tables as $table) {
                echo "Dropping table: $table\n";
                $this->db->query("DROP TABLE IF EXISTS `$table`");
            }

            echo "Dispatch system rollback completed successfully!\n";
            return true;

        } catch (Exception $e) {
            echo "Rollback failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    private function splitSQLStatements($sql) {
        // Split SQL into individual statements
        $statements = [];
        $currentStatement = '';
        $lines = explode("\n", $sql);

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || strpos($line, '--') === 0) {
                continue;
            }

            $currentStatement .= $line . "\n";

            // Check if statement is complete (ends with semicolon)
            if (substr(rtrim($line), -1) === ';') {
                $statements[] = $currentStatement;
                $currentStatement = '';
            }
        }

        // Add any remaining statement
        if (!empty(trim($currentStatement))) {
            $statements[] = $currentStatement;
        }

        return $statements;
    }
}

// Run migration if called from command line
if (php_sapi_name() === 'cli') {
    $migration = new DispatchSystemMigration();
    
    // Check for command line arguments
    $action = $argv[1] ?? 'up';
    
    if ($action === 'down') {
        $success = $migration->down();
    } else {
        $success = $migration->up();
    }
    
    exit($success ? 0 : 1);
}
?>
