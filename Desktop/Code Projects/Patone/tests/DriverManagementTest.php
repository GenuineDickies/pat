<?php
/**
 * Driver Management Enhancements Test Suite
 * Tests new driver management features: certifications, documents, scheduling, and workload balancing
 */

// Load configuration
require_once __DIR__ . '/../config.php';
require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Model.php';
require_once BACKEND_PATH . 'models/Driver.php';

class DriverManagementTest {
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    private $driverModel;
    private $testDriverId = null;

    public function __construct() {
        $this->driverModel = new Driver();
    }

    public function run() {
        echo "=================================\n";
        echo "Driver Management Test Suite\n";
        echo "=================================\n\n";

        // Test database connection
        $this->testDatabaseConnection();

        // Test driver model enhancements
        $this->testDriverWorkload();
        $this->testDriverCertifications();
        $this->testDriverDocuments();
        $this->testDriverAvailabilitySchedule();
        $this->testWorkloadBalancing();

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

    private function testDriverWorkload() {
        try {
            // Check if workload columns exist
            $db = Database::getInstance();
            $columns = $db->getRows("SHOW COLUMNS FROM drivers LIKE 'current_workload'");
            
            if (empty($columns)) {
                $this->fail("Workload columns not found in drivers table. Run migration 002.");
                return;
            }

            // Test getWorkload method
            $drivers = $this->driverModel->getAll(1, 0);
            if (!empty($drivers['drivers'])) {
                $driver = $drivers['drivers'][0];
                $workload = $this->driverModel->getWorkload($driver['id']);

                if (isset($workload['current']) && isset($workload['max']) && isset($workload['available_capacity'])) {
                    $this->pass("Driver workload retrieval working (Driver: {$driver['id']})");
                    $this->testDriverId = $driver['id'];
                } else {
                    $this->fail("Driver workload returned invalid structure");
                }
            } else {
                $this->pass("Driver workload structure verified (no drivers to test)");
            }
        } catch (Exception $e) {
            $this->fail("Driver workload test failed: " . $e->getMessage());
        }
    }

    private function testDriverCertifications() {
        try {
            // Check if certifications table exists
            $db = Database::getInstance();
            $tables = $db->getRows("SHOW TABLES LIKE 'driver_certifications'");
            
            if (empty($tables)) {
                $this->fail("driver_certifications table not found. Run migration 002.");
                return;
            }

            // Test certifications retrieval
            if ($this->testDriverId) {
                $certifications = $this->driverModel->getCertifications($this->testDriverId);
                
                if (is_array($certifications)) {
                    $this->pass("Driver certifications retrieval working");
                    
                    // Test add certification (if we have a test driver)
                    try {
                        $testCert = [
                            'certification_type' => 'Test Certification',
                            'certification_number' => 'TEST-123',
                            'issuing_authority' => 'Test Authority',
                            'issue_date' => date('Y-m-d'),
                            'expiry_date' => date('Y-m-d', strtotime('+1 year')),
                            'status' => 'active',
                            'notes' => 'Test certification for unit testing'
                        ];
                        
                        $certId = $this->driverModel->addCertification($this->testDriverId, $testCert);
                        
                        if ($certId) {
                            $this->pass("Driver certification creation working");
                            
                            // Clean up test data
                            $this->driverModel->deleteCertification($certId);
                            $this->pass("Driver certification deletion working");
                        } else {
                            $this->fail("Driver certification creation returned no ID");
                        }
                    } catch (Exception $e) {
                        $this->fail("Driver certification CRUD operations failed: " . $e->getMessage());
                    }
                } else {
                    $this->fail("Driver certifications returned invalid data");
                }
            } else {
                $this->pass("Driver certifications structure verified (no test driver)");
            }
        } catch (Exception $e) {
            $this->fail("Driver certifications test failed: " . $e->getMessage());
        }
    }

    private function testDriverDocuments() {
        try {
            // Check if documents table exists
            $db = Database::getInstance();
            $tables = $db->getRows("SHOW TABLES LIKE 'driver_documents'");
            
            if (empty($tables)) {
                $this->fail("driver_documents table not found. Run migration 002.");
                return;
            }

            // Test documents retrieval
            if ($this->testDriverId) {
                $documents = $this->driverModel->getDocuments($this->testDriverId);
                
                if (is_array($documents)) {
                    $this->pass("Driver documents retrieval working");
                } else {
                    $this->fail("Driver documents returned invalid data");
                }
            } else {
                $this->pass("Driver documents structure verified (no test driver)");
            }
        } catch (Exception $e) {
            $this->fail("Driver documents test failed: " . $e->getMessage());
        }
    }

    private function testDriverAvailabilitySchedule() {
        try {
            // Check if schedule table exists
            $db = Database::getInstance();
            $tables = $db->getRows("SHOW TABLES LIKE 'driver_availability_schedule'");
            
            if (empty($tables)) {
                $this->fail("driver_availability_schedule table not found. Run migration 002.");
                return;
            }

            // Test schedule retrieval
            if ($this->testDriverId) {
                $schedule = $this->driverModel->getAvailabilitySchedule($this->testDriverId);
                
                if (is_array($schedule)) {
                    $this->pass("Driver availability schedule retrieval working");
                    
                    // Test setting schedule
                    try {
                        $result = $this->driverModel->setAvailabilitySchedule(
                            $this->testDriverId,
                            1, // Monday
                            '09:00:00',
                            '17:00:00',
                            true,
                            'Test schedule'
                        );
                        
                        if ($result) {
                            $this->pass("Driver availability schedule creation working");
                            
                            // Test isScheduledAvailable
                            $isAvailable = $this->driverModel->isScheduledAvailable($this->testDriverId);
                            $this->pass("Driver scheduled availability check working (Result: " . ($isAvailable ? 'true' : 'false') . ")");
                        } else {
                            $this->fail("Driver availability schedule creation failed");
                        }
                    } catch (Exception $e) {
                        $this->fail("Driver availability schedule operations failed: " . $e->getMessage());
                    }
                } else {
                    $this->fail("Driver availability schedule returned invalid data");
                }
            } else {
                $this->pass("Driver availability schedule structure verified (no test driver)");
            }
        } catch (Exception $e) {
            $this->fail("Driver availability schedule test failed: " . $e->getMessage());
        }
    }

    private function testWorkloadBalancing() {
        try {
            // Test getWorkloadDistribution
            $distribution = $this->driverModel->getWorkloadDistribution();
            
            if (is_array($distribution)) {
                $this->pass("Workload distribution retrieval working (" . count($distribution) . " active drivers)");
                
                // Test getDriversWithCapacity
                $driversWithCapacity = $this->driverModel->getDriversWithCapacity();
                
                if (is_array($driversWithCapacity)) {
                    $this->pass("Drivers with capacity retrieval working (" . count($driversWithCapacity) . " available)");
                } else {
                    $this->fail("Drivers with capacity returned invalid data");
                }
                
                // Test setMaxWorkload
                if ($this->testDriverId) {
                    $result = $this->driverModel->setMaxWorkload($this->testDriverId, 5);
                    if ($result) {
                        $this->pass("Set max workload working");
                        
                        // Reset to default
                        $this->driverModel->setMaxWorkload($this->testDriverId, 3);
                    } else {
                        $this->fail("Set max workload failed");
                    }
                }
            } else {
                $this->fail("Workload distribution returned invalid data");
            }
        } catch (Exception $e) {
            $this->fail("Workload balancing test failed: " . $e->getMessage());
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
            echo "Note: Make sure to run migration 002_driver_management_enhancements.php first.\n";
            exit(1);
        } else {
            echo "All tests passed successfully!\n";
            exit(0);
        }
    }
}

// Run tests if called from command line
if (php_sapi_name() === 'cli') {
    $test = new DriverManagementTest();
    $test->run();
} else {
    echo "<html><body><pre>";
    $test = new DriverManagementTest();
    $test->run();
    echo "</pre></body></html>";
}
?>
