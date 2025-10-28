<?php
/**
 * Service Request Management System - Static Tests
 * Tests class and method existence without database connection
 */

// Define constants if not defined
if (!defined('BACKEND_PATH')) {
    define('BACKEND_PATH', __DIR__ . '/../backend/');
}

class ServiceRequestStaticTest {
    private $results = [];
    private $passed = 0;
    private $failed = 0;

    public function run() {
        echo "=================================\n";
        echo "Service Request Static Tests\n";
        echo "=================================\n\n";

        // Test file existence
        $this->testFilesExist();

        // Test class definitions
        $this->testClassDefinitions();

        // Test method signatures
        $this->testMethodSignatures();

        // Display results
        $this->displayResults();
    }

    private function testFilesExist() {
        $files = [
            'frontend/pages/request_details.php' => 'Request details view',
            'backend/models/RequestHistory.php' => 'RequestHistory model',
            'backend/models/RequestCommunication.php' => 'RequestCommunication model',
            'backend/models/Notification.php' => 'Notification model',
            'database/migrations/001_add_request_history.sql' => 'Request history migration',
        ];

        foreach ($files as $file => $description) {
            $fullPath = __DIR__ . '/../' . $file;
            if (file_exists($fullPath)) {
                $this->pass("$description file exists");
            } else {
                $this->fail("$description file not found: $file");
            }
        }
    }

    private function testClassDefinitions() {
        // Include model files without database dependency
        $modelFiles = [
            BACKEND_PATH . 'models/RequestHistory.php',
            BACKEND_PATH . 'models/RequestCommunication.php',
            BACKEND_PATH . 'models/Notification.php',
        ];

        foreach ($modelFiles as $file) {
            if (file_exists($file)) {
                // Read file content and check for class definition
                $content = file_get_contents($file);
                $className = basename($file, '.php');
                
                if (strpos($content, "class $className") !== false) {
                    $this->pass("$className class is defined");
                } else {
                    $this->fail("$className class definition not found");
                }
            }
        }
    }

    private function testMethodSignatures() {
        // Test RequestHistory methods
        $historyMethods = [
            'addEntry',
            'getByRequest',
            'logStatusChange',
            'logDriverAssignment',
            'logCompletion',
            'logCancellation',
        ];

        $this->testMethodsInFile(
            BACKEND_PATH . 'models/RequestHistory.php',
            'RequestHistory',
            $historyMethods
        );

        // Test RequestCommunication methods
        $communicationMethods = [
            'addLog',
            'getByRequest',
            'addNote',
            'logEmail',
            'logSMS',
            'logCall',
            'logSystem',
        ];

        $this->testMethodsInFile(
            BACKEND_PATH . 'models/RequestCommunication.php',
            'RequestCommunication',
            $communicationMethods
        );

        // Test Notification methods
        $notificationMethods = [
            'createNotification',
            'getUnread',
            'getByUser',
            'markAsRead',
            'markAllAsRead',
            'getUnreadCount',
            'notifyRequestAssigned',
            'notifyStatusChanged',
            'notifyRequestCompleted',
        ];

        $this->testMethodsInFile(
            BACKEND_PATH . 'models/Notification.php',
            'Notification',
            $notificationMethods
        );
    }

    private function testMethodsInFile($file, $className, $methods) {
        if (!file_exists($file)) {
            $this->fail("$className file not found");
            return;
        }

        $content = file_get_contents($file);

        foreach ($methods as $method) {
            // Match method with optional visibility modifiers and return types
            if (preg_match('/(public|private|protected)?\s*(static)?\s*function\s+' . preg_quote($method) . '\s*\(/i', $content)) {
                $this->pass("$className::$method() method is defined");
            } else {
                $this->fail("$className::$method() method not found");
            }
        }
    }

    private function pass($message) {
        $this->passed++;
        $this->results[] = ['status' => 'PASS', 'message' => $message];
        echo "✓ PASS: $message\n";
    }

    private function fail($message) {
        $this->failed++;
        $this->results[] = ['status' => 'FAIL', 'message' => $message];
        echo "✗ FAIL: $message\n";
    }

    private function displayResults() {
        echo "\n=================================\n";
        echo "Test Results Summary\n";
        echo "=================================\n";
        echo "Total Tests: " . ($this->passed + $this->failed) . "\n";
        echo "Passed: " . $this->passed . "\n";
        echo "Failed: " . $this->failed . "\n";
        echo "Success Rate: " . round(($this->passed / max(1, $this->passed + $this->failed)) * 100, 2) . "%\n";
        echo "=================================\n\n";

        if ($this->failed > 0) {
            echo "Some tests failed. Please review the errors above.\n";
            exit(1);
        } else {
            echo "All tests passed successfully!\n";
            exit(0);
        }
    }
}

// Run tests
$test = new ServiceRequestStaticTest();
$test->run();
?>
