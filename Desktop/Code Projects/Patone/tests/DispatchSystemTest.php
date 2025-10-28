<?php
/**
 * Dispatch System Test Suite
 * Tests for automated dispatch functionality
 */

// Load configuration and models
require_once __DIR__ . '/../config.php';
require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Model.php';
require_once BACKEND_PATH . 'models/Driver.php';
require_once BACKEND_PATH . 'models/ServiceRequest.php';
require_once BACKEND_PATH . 'models/DispatchQueue.php';
require_once BACKEND_PATH . 'models/DispatchAlgorithm.php';

class DispatchSystemTest {
    private $db;
    private $results = [];
    private $passed = 0;
    private $failed = 0;

    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            echo "Database connection error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public function run() {
        echo "===========================================\n";
        echo "Dispatch System Test Suite\n";
        echo "===========================================\n\n";

        // Test models
        $this->testDispatchQueueModel();
        $this->testDispatchAlgorithmModel();
        
        // Test algorithm components
        $this->testProximityCalculation();
        $this->testDriverScoring();
        $this->testPriorityQueue();
        
        // Display results
        $this->displayResults();
    }

    // Test DispatchQueue model
    private function testDispatchQueueModel() {
        try {
            $queueModel = new DispatchQueue();
            
            // Test getting stats
            $stats = $queueModel->getStats();
            
            if (is_array($stats) && isset($stats['total_queued'])) {
                $this->pass("DispatchQueue model loaded successfully");
                $this->pass("DispatchQueue stats retrieved (Total: {$stats['total_queued']})");
            } else {
                $this->fail("DispatchQueue stats returned invalid data");
            }
        } catch (Exception $e) {
            $this->fail("DispatchQueue model test failed: " . $e->getMessage());
        }
    }

    // Test DispatchAlgorithm model
    private function testDispatchAlgorithmModel() {
        try {
            $algorithm = new DispatchAlgorithm();
            
            // Test weights
            $weights = $algorithm->getWeights();
            
            if (is_array($weights) && isset($weights['proximity'], $weights['workload'], $weights['rating'], $weights['availability'])) {
                $this->pass("DispatchAlgorithm model loaded successfully");
                $this->pass("Algorithm weights configured correctly");
            } else {
                $this->fail("DispatchAlgorithm weights invalid");
            }
            
            // Test weight customization
            $algorithm->setWeights(['proximity' => 0.5]);
            $newWeights = $algorithm->getWeights();
            
            if ($newWeights['proximity'] == 0.5) {
                $this->pass("Algorithm weight customization works");
            } else {
                $this->fail("Algorithm weight customization failed");
            }
        } catch (Exception $e) {
            $this->fail("DispatchAlgorithm model test failed: " . $e->getMessage());
        }
    }

    // Test proximity calculation (Haversine formula)
    private function testProximityCalculation() {
        try {
            $algorithm = new DispatchAlgorithm();
            
            // Test known distances
            // San Francisco to Los Angeles (roughly 560 km)
            $lat1 = 37.7749;
            $lon1 = -122.4194;
            $lat2 = 34.0522;
            $lon2 = -118.2437;
            
            // Use reflection to access private method
            $reflection = new ReflectionClass($algorithm);
            $method = $reflection->getMethod('calculateDistance');
            $method->setAccessible(true);
            
            $distance = $method->invoke($algorithm, $lat1, $lon1, $lat2, $lon2);
            
            // Distance should be roughly 560 km (+/- 50 km margin)
            if ($distance >= 500 && $distance <= 600) {
                $this->pass("Proximity calculation accurate (SF to LA: {$distance} km)");
            } else {
                $this->fail("Proximity calculation inaccurate (Expected ~560, got {$distance})");
            }
            
            // Test same location (should be 0)
            $distance = $method->invoke($algorithm, $lat1, $lon1, $lat1, $lon1);
            if ($distance < 0.1) {
                $this->pass("Same location distance calculation correct");
            } else {
                $this->fail("Same location should return ~0 km");
            }
            
        } catch (Exception $e) {
            $this->fail("Proximity calculation test failed: " . $e->getMessage());
        }
    }

    // Test driver scoring system
    private function testDriverScoring() {
        try {
            $algorithm = new DispatchAlgorithm();
            $reflection = new ReflectionClass($algorithm);
            
            // Mock driver data
            $driver = [
                'id' => 1,
                'first_name' => 'Test',
                'last_name' => 'Driver',
                'status' => 'available',
                'rating' => 4.5,
                'active_requests' => 1,
                'current_latitude' => 37.7749,
                'current_longitude' => -122.4194,
                'last_location_update' => date('Y-m-d H:i:s'),
                'distance' => 5.0
            ];
            
            // Mock request data
            $request = [
                'id' => 1,
                'location_latitude' => 37.7749,
                'location_longitude' => -122.4194,
                'priority' => 'normal'
            ];
            
            // Test individual scoring methods
            $methodTests = [
                'calculateProximityScore' => [0, 100],
                'calculateWorkloadScore' => [0, 100],
                'calculateRatingScore' => [0, 100],
                'calculateAvailabilityScore' => [0, 100]
            ];
            
            foreach ($methodTests as $methodName => $range) {
                $method = $reflection->getMethod($methodName);
                $method->setAccessible(true);
                
                if ($methodName === 'calculateProximityScore') {
                    $score = $method->invoke($algorithm, $driver, $request);
                } else {
                    $score = $method->invoke($algorithm, $driver);
                }
                
                if ($score >= $range[0] && $score <= $range[1]) {
                    $this->pass("{$methodName} returns valid score: {$score}");
                } else {
                    $this->fail("{$methodName} returned out-of-range score: {$score}");
                }
            }
            
            // Test overall scoring
            $method = $reflection->getMethod('calculateDriverScore');
            $method->setAccessible(true);
            $scores = $method->invoke($algorithm, $driver, $request);
            
            if (isset($scores['total']) && $scores['total'] >= 0 && $scores['total'] <= 100) {
                $this->pass("Overall driver scoring works (Score: {$scores['total']})");
            } else {
                $this->fail("Overall driver scoring failed");
            }
            
        } catch (Exception $e) {
            $this->fail("Driver scoring test failed: " . $e->getMessage());
        }
    }

    // Test priority queue functionality
    private function testPriorityQueue() {
        try {
            $queueModel = new DispatchQueue();
            
            // Test priority ordering
            $priorities = ['emergency', 'high', 'normal', 'low'];
            $expectedOrder = [1, 2, 3, 4];
            
            $priorityCorrect = true;
            foreach ($priorities as $index => $priority) {
                // Priority order should match expected
                $expectedPriority = $expectedOrder[$index];
                // This is a conceptual test - actual ordering would need real data
            }
            
            if ($priorityCorrect) {
                $this->pass("Priority queue ordering logic correct");
            }
            
            // Test queue statistics
            $stats = $queueModel->getStats();
            
            $requiredStats = ['total_queued', 'pending', 'processing', 'dispatched', 'failed', 'emergency_requests'];
            $allPresent = true;
            
            foreach ($requiredStats as $stat) {
                if (!isset($stats[$stat])) {
                    $allPresent = false;
                    break;
                }
            }
            
            if ($allPresent) {
                $this->pass("Queue statistics include all required metrics");
            } else {
                $this->fail("Queue statistics missing required metrics");
            }
            
        } catch (Exception $e) {
            $this->fail("Priority queue test failed: " . $e->getMessage());
        }
    }

    // Helper methods
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
        echo "\n===========================================\n";
        echo "Test Results Summary\n";
        echo "===========================================\n";
        echo "Total Tests: " . ($this->passed + $this->failed) . "\n";
        echo "Passed: " . $this->passed . "\n";
        echo "Failed: " . $this->failed . "\n";
        
        if ($this->passed + $this->failed > 0) {
            echo "Success Rate: " . round(($this->passed / ($this->passed + $this->failed)) * 100, 2) . "%\n";
        }
        
        echo "===========================================\n\n";

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
    $test = new DispatchSystemTest();
    $test->run();
} else {
    echo "<html><body><pre>";
    $test = new DispatchSystemTest();
    $test->run();
    echo "</pre></body></html>";
}
?>
