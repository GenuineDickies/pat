<?php
/**
 * Basic Test Suite for Patone v1.0
 * Tests core functionality of models and database connectivity
 */

// Load configuration
require_once __DIR__ . '/../config.php';
require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Model.php';
require_once BACKEND_PATH . 'models/Customer.php';
require_once BACKEND_PATH . 'models/Driver.php';
require_once BACKEND_PATH . 'models/ServiceRequest.php';
require_once BACKEND_PATH . 'models/ServiceType.php';
require_once BACKEND_PATH . 'models/User.php';
require_once BACKEND_PATH . 'models/Setting.php';

class BasicTest {
    private $results = [];
    private $passed = 0;
    private $failed = 0;

    public function run() {
        echo "=================================\n";
        echo "Patone v1.0 Basic Test Suite\n";
        echo "=================================\n\n";

        // Test database connection
        $this->testDatabaseConnection();

        // Test models
        $this->testCustomerModel();
        $this->testDriverModel();
        $this->testServiceTypeModel();
        $this->testServiceRequestModel();
        $this->testUserModel();
        $this->testSettingModel();

        // Display results
        $this->displayResults();
    }

    private function testDatabaseConnection() {
        try {
            $db = Database::getInstance();
            $this->pass("Database connection successful");
        } catch (Exception $e) {
            $this->fail("Database connection failed: " . $e->getMessage());
        }
    }

    private function testCustomerModel() {
        try {
            $customerModel = new Customer();
            $stats = $customerModel->getStats();
            
            if (is_array($stats) && isset($stats['total'])) {
                $this->pass("Customer model loaded successfully (Total customers: {$stats['total']})");
            } else {
                $this->fail("Customer model returned invalid stats");
            }
        } catch (Exception $e) {
            $this->fail("Customer model test failed: " . $e->getMessage());
        }
    }

    private function testDriverModel() {
        try {
            $driverModel = new Driver();
            $stats = $driverModel->getStats();
            
            if (is_array($stats) && isset($stats['total'])) {
                $this->pass("Driver model loaded successfully (Total drivers: {$stats['total']})");
            } else {
                $this->fail("Driver model returned invalid stats");
            }
        } catch (Exception $e) {
            $this->fail("Driver model test failed: " . $e->getMessage());
        }
    }

    private function testServiceTypeModel() {
        try {
            $serviceTypeModel = new ServiceType();
            $types = $serviceTypeModel->getActive();
            
            if (is_array($types)) {
                $this->pass("ServiceType model loaded successfully (Active types: " . count($types) . ")");
            } else {
                $this->fail("ServiceType model returned invalid data");
            }
        } catch (Exception $e) {
            $this->fail("ServiceType model test failed: " . $e->getMessage());
        }
    }

    private function testServiceRequestModel() {
        try {
            $requestModel = new ServiceRequest();
            $stats = $requestModel->getStats();
            
            if (is_array($stats) && isset($stats['total'])) {
                $this->pass("ServiceRequest model loaded successfully (Total requests: {$stats['total']})");
            } else {
                $this->fail("ServiceRequest model returned invalid stats");
            }
        } catch (Exception $e) {
            $this->fail("ServiceRequest model test failed: " . $e->getMessage());
        }
    }

    private function testUserModel() {
        try {
            $userModel = new User();
            $stats = $userModel->getStats();
            
            if (is_array($stats) && isset($stats['total'])) {
                $this->pass("User model loaded successfully (Total users: {$stats['total']})");
            } else {
                $this->fail("User model returned invalid stats");
            }
        } catch (Exception $e) {
            $this->fail("User model test failed: " . $e->getMessage());
        }
    }

    private function testSettingModel() {
        try {
            $settingModel = new Setting();
            $settings = $settingModel->getAll(true);
            
            if (is_array($settings)) {
                $this->pass("Setting model loaded successfully (Public settings: " . count($settings) . ")");
            } else {
                $this->fail("Setting model returned invalid data");
            }
        } catch (Exception $e) {
            $this->fail("Setting model test failed: " . $e->getMessage());
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

// Run tests if called from command line
if (php_sapi_name() === 'cli') {
    $test = new BasicTest();
    $test->run();
} else {
    echo "<html><body><pre>";
    $test = new BasicTest();
    $test->run();
    echo "</pre></body></html>";
}
?>
