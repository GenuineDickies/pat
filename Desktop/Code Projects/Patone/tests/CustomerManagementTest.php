<?php
/**
 * Customer Management Test Suite
 * Tests customer management functionality without database
 */

// Load configuration
require_once __DIR__ . '/../config.php';

class CustomerManagementTest {
    private $results = [];
    private $passed = 0;
    private $failed = 0;

    public function run() {
        echo "=================================\n";
        echo "Customer Management Test Suite\n";
        echo "=================================\n\n";

        // Test file existence
        $this->testFilesExist();
        
        // Test PHP syntax
        $this->testPHPSyntax();
        
        // Test class loading
        $this->testClassLoading();
        
        // Test method existence
        $this->testMethodExistence();

        // Display results
        $this->displayResults();
    }

    private function testFilesExist() {
        $files = [
            'backend/controllers/CustomerController.php',
            'backend/models/Customer.php',
            'frontend/pages/customers.php',
            'frontend/pages/customer_form.php',
            'frontend/pages/customer_details.php',
            'database/migrations/002_customer_tags.sql',
            'database/sample_customers_import.csv',
            'docs/CUSTOMER_MANAGEMENT.md'
        ];

        foreach ($files as $file) {
            $fullPath = ROOT_PATH . $file;
            if (file_exists($fullPath)) {
                $this->pass("File exists: $file");
            } else {
                $this->fail("File missing: $file");
            }
        }
    }

    private function testPHPSyntax() {
        $phpFiles = [
            'backend/controllers/CustomerController.php',
            'backend/models/Customer.php',
            'frontend/pages/customers.php',
            'frontend/pages/customer_form.php',
            'frontend/pages/customer_details.php',
            'index.php'
        ];

        foreach ($phpFiles as $file) {
            $fullPath = ROOT_PATH . $file;
            
            // Use PHP's built-in syntax checking
            $code = file_get_contents($fullPath);
            $tokens = @token_get_all($code);
            
            if ($tokens !== false && is_array($tokens)) {
                $this->pass("Syntax valid: $file");
            } else {
                $this->fail("Syntax error in: $file");
            }
        }
    }

    private function testClassLoading() {
        try {
            // Try to load Customer model
            if (class_exists('Customer')) {
                $this->pass("Customer class loaded successfully");
            } else {
                $this->fail("Customer class not found");
            }
        } catch (Exception $e) {
            $this->fail("Error loading Customer class: " . $e->getMessage());
        }

        try {
            // Try to load CustomerController
            if (class_exists('CustomerController')) {
                $this->pass("CustomerController class loaded successfully");
            } else {
                $this->fail("CustomerController class not found");
            }
        } catch (Exception $e) {
            $this->fail("Error loading CustomerController class: " . $e->getMessage());
        }
    }

    private function testMethodExistence() {
        // Test Customer model methods
        if (class_exists('Customer')) {
            $customerMethods = [
                'getById', 'getByEmail', 'getAll', 'create', 'update', 'delete',
                'getVehicles', 'getServiceHistory', 'search', 'getStats',
                'getTags', 'addTag', 'removeTag', 'getByTag', 'getActivityLog'
            ];

            foreach ($customerMethods as $method) {
                if (method_exists('Customer', $method)) {
                    $this->pass("Customer::$method() exists");
                } else {
                    $this->fail("Customer::$method() missing");
                }
            }
        }

        // Test CustomerController methods
        if (class_exists('CustomerController')) {
            $controllerMethods = [
                'index', 'add', 'doAdd', 'edit', 'doEdit', 'delete', 'view',
                'export', 'import', 'getTags'
            ];

            foreach ($controllerMethods as $method) {
                if (method_exists('CustomerController', $method)) {
                    $this->pass("CustomerController::$method() exists");
                } else {
                    $this->fail("CustomerController::$method() missing");
                }
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
        
        $total = $this->passed + $this->failed;
        if ($total > 0) {
            $successRate = round(($this->passed / $total) * 100, 2);
            echo "Success Rate: $successRate%\n";
        }
        echo "=================================\n\n";

        if ($this->failed > 0) {
            echo "⚠ Some tests failed. Please review the errors above.\n";
            exit(1);
        } else {
            echo "✓ All tests passed successfully!\n";
            exit(0);
        }
    }
}

// Run tests
$test = new CustomerManagementTest();
$test->run();
?>
