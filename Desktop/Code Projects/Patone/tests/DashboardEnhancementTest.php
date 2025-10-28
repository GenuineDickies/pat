<?php
/**
 * Dashboard Enhancement Test Suite
 * Tests for real-time statistics, charts, and new API endpoints
 */

// Load configuration
require_once __DIR__ . '/../config.php';
require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Model.php';
require_once BACKEND_PATH . 'controllers/Controller.php';
require_once BACKEND_PATH . 'controllers/DashboardController.php';

class DashboardEnhancementTest {
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    private $controller;

    public function __construct() {
        // Create a mock session for testing
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Test Admin';
        $_SESSION['logged_in'] = true;
    }

    public function run() {
        echo "===========================================\n";
        echo "Dashboard Enhancement Test Suite\n";
        echo "===========================================\n\n";

        // Test dashboard controller exists
        $this->testDashboardControllerExists();

        // Test new API methods exist
        $this->testNewApiMethodsExist();

        // Test JavaScript file exists
        $this->testDashboardJsExists();

        // Test Chart.js is included in layout
        $this->testChartJsIncluded();

        // Test dashboard page has new elements
        $this->testDashboardPageEnhancements();

        // Test API routes are registered
        $this->testApiRoutesRegistered();

        // Display results
        $this->displayResults();
    }

    private function testDashboardControllerExists() {
        try {
            if (class_exists('DashboardController')) {
                $this->pass("DashboardController class exists");
            } else {
                $this->fail("DashboardController class not found");
            }
        } catch (Exception $e) {
            $this->fail("Error checking DashboardController: " . $e->getMessage());
        }
    }

    private function testNewApiMethodsExist() {
        try {
            $methods = [
                'getStats',
                'getRecentRequests',
                'getDriverStatus',
                'getChartData',
                'getRecentActivity',
                'getPerformanceMetrics'
            ];

            $missingMethods = [];
            foreach ($methods as $method) {
                if (!method_exists('DashboardController', $method)) {
                    $missingMethods[] = $method;
                }
            }

            if (empty($missingMethods)) {
                $this->pass("All new API methods exist in DashboardController");
            } else {
                $this->fail("Missing API methods: " . implode(', ', $missingMethods));
            }
        } catch (Exception $e) {
            $this->fail("Error checking API methods: " . $e->getMessage());
        }
    }

    private function testDashboardJsExists() {
        try {
            $jsFile = ROOT_PATH . 'assets/js/dashboard.js';
            if (file_exists($jsFile)) {
                $content = file_get_contents($jsFile);
                
                // Check for key components
                $requiredComponents = [
                    'DashboardManager',
                    'initializeCharts',
                    'updateStatistics',
                    'startAutoRefresh',
                    'new Chart'
                ];

                $missingComponents = [];
                foreach ($requiredComponents as $component) {
                    if (strpos($content, $component) === false) {
                        $missingComponents[] = $component;
                    }
                }

                if (empty($missingComponents)) {
                    $this->pass("dashboard.js file exists with all required components");
                } else {
                    $this->fail("dashboard.js missing components: " . implode(', ', $missingComponents));
                }
            } else {
                $this->fail("dashboard.js file not found");
            }
        } catch (Exception $e) {
            $this->fail("Error checking dashboard.js: " . $e->getMessage());
        }
    }

    private function testChartJsIncluded() {
        try {
            $layoutFile = ROOT_PATH . 'frontend/pages/layout.php';
            if (file_exists($layoutFile)) {
                $content = file_get_contents($layoutFile);
                
                if (strpos($content, 'chart.js') !== false || strpos($content, 'Chart.js') !== false) {
                    $this->pass("Chart.js library is included in layout");
                } else {
                    $this->fail("Chart.js library not found in layout");
                }
            } else {
                $this->fail("layout.php file not found");
            }
        } catch (Exception $e) {
            $this->fail("Error checking layout.php: " . $e->getMessage());
        }
    }

    private function testDashboardPageEnhancements() {
        try {
            $dashboardFile = ROOT_PATH . 'frontend/pages/dashboard.php';
            if (file_exists($dashboardFile)) {
                $content = file_get_contents($dashboardFile);
                
                // Check for new elements
                $requiredElements = [
                    'requestsTimelineChart',
                    'serviceTypeChart',
                    'driverPerformanceChart',
                    'hourlyRequestsChart',
                    'recentActivity',
                    'avgResponseTime',
                    'completionRate'
                ];

                $missingElements = [];
                foreach ($requiredElements as $element) {
                    if (strpos($content, $element) === false) {
                        $missingElements[] = $element;
                    }
                }

                if (empty($missingElements)) {
                    $this->pass("Dashboard page contains all new chart elements");
                } else {
                    $this->fail("Dashboard page missing elements: " . implode(', ', $missingElements));
                }
            } else {
                $this->fail("dashboard.php file not found");
            }
        } catch (Exception $e) {
            $this->fail("Error checking dashboard.php: " . $e->getMessage());
        }
    }

    private function testApiRoutesRegistered() {
        try {
            $indexFile = ROOT_PATH . 'index.php';
            if (file_exists($indexFile)) {
                $content = file_get_contents($indexFile);
                
                // Check for new API routes
                $requiredRoutes = [
                    '/api/dashboard/stats',
                    '/api/dashboard/chart-data',
                    '/api/dashboard/recent-activity',
                    '/api/dashboard/performance-metrics'
                ];

                $missingRoutes = [];
                foreach ($requiredRoutes as $route) {
                    if (strpos($content, $route) === false) {
                        $missingRoutes[] = $route;
                    }
                }

                if (empty($missingRoutes)) {
                    $this->pass("All new API routes are registered");
                } else {
                    $this->fail("Missing API routes: " . implode(', ', $missingRoutes));
                }
            } else {
                $this->fail("index.php file not found");
            }
        } catch (Exception $e) {
            $this->fail("Error checking index.php: " . $e->getMessage());
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
        echo "\n===========================================\n";
        echo "Test Results Summary\n";
        echo "===========================================\n";
        echo "Total Tests: " . ($this->passed + $this->failed) . "\n";
        echo "Passed: " . $this->passed . "\n";
        echo "Failed: " . $this->failed . "\n";
        echo "Success Rate: " . round(($this->passed / max(1, $this->passed + $this->failed)) * 100, 2) . "%\n";
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
    $test = new DashboardEnhancementTest();
    $test->run();
} else {
    echo "<html><body><pre>";
    $test = new DashboardEnhancementTest();
    $test->run();
    echo "</pre></body></html>";
}
?>
