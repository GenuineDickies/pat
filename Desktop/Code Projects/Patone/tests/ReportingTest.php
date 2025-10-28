<?php
/**
 * Reporting System Test Suite
 * Tests report generation and analytics functionality
 */

// Load configuration
require_once __DIR__ . '/../config.php';
require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Model.php';
require_once BACKEND_PATH . 'models/ServiceRequest.php';
require_once BACKEND_PATH . 'models/Customer.php';
require_once BACKEND_PATH . 'models/Driver.php';
require_once BACKEND_PATH . 'controllers/Controller.php';
require_once BACKEND_PATH . 'controllers/ReportController.php';

class ReportingTest {
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    private $requestModel;

    public function __construct() {
        $this->requestModel = new ServiceRequest();
    }

    public function run() {
        echo "=================================\n";
        echo "Reporting System Test Suite\n";
        echo "=================================\n\n";

        // Test report models
        $this->testRequestStats();
        $this->testReportDataQueries();
        
        // Test report views exist
        $this->testReportViewsExist();

        // Test Python scripts
        $this->testPythonScripts();

        // Display results
        $this->displayResults();
    }

    private function testRequestStats() {
        try {
            $stats = $this->requestModel->getStats();
            
            if (is_array($stats) && isset($stats['total'])) {
                $this->pass("Service request statistics retrieved successfully");
            } else {
                $this->fail("Service request statistics invalid");
            }
        } catch (Exception $e) {
            $this->fail("Service request statistics test failed: " . $e->getMessage());
        }
    }

    private function testReportDataQueries() {
        try {
            $db = Database::getInstance();
            
            // Test daily stats query
            $date = date('Y-m-d');
            $dateStart = $date . ' 00:00:00';
            $dateEnd = $date . ' 23:59:59';
            
            $dailyStats = $db->getRows(
                "SELECT COUNT(*) as total FROM service_requests WHERE created_at BETWEEN ? AND ?",
                [$dateStart, $dateEnd]
            );
            
            if (is_array($dailyStats)) {
                $this->pass("Daily statistics query executed successfully");
            } else {
                $this->fail("Daily statistics query failed");
            }
            
            // Test revenue query
            $revenueData = $db->getRow(
                "SELECT SUM(final_cost) as total_revenue FROM service_requests WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE())"
            );
            
            if (isset($revenueData['total_revenue']) || $revenueData['total_revenue'] === null) {
                $this->pass("Revenue data query executed successfully");
            } else {
                $this->fail("Revenue data query failed");
            }
            
        } catch (Exception $e) {
            $this->fail("Report data queries test failed: " . $e->getMessage());
        }
    }

    private function testReportViewsExist() {
        $views = [
            'report_daily.php',
            'report_monthly.php',
            'report_driver_performance.php',
            'report_customer.php',
            'report_revenue.php',
            'report_demand_forecast.php',
            'report_custom.php',
            'report_custom_results.php'
        ];
        
        $viewPath = FRONTEND_PATH . 'pages/';
        $missingViews = [];
        
        foreach ($views as $view) {
            if (!file_exists($viewPath . $view)) {
                $missingViews[] = $view;
            }
        }
        
        if (empty($missingViews)) {
            $this->pass("All report view files exist (" . count($views) . " views)");
        } else {
            $this->fail("Missing report views: " . implode(', ', $missingViews));
        }
    }

    private function testPythonScripts() {
        $scripts = [
            'python/report_generator.py',
            'python/data_analyzer.py',
            'python/config.py'
        ];
        
        $basePath = __DIR__ . '/../';
        $missingScripts = [];
        
        foreach ($scripts as $script) {
            if (!file_exists($basePath . $script)) {
                $missingScripts[] = $script;
            }
        }
        
        if (empty($missingScripts)) {
            $this->pass("All Python analytics scripts exist (" . count($scripts) . " scripts)");
            
            // Test Python syntax
            $reportGenerator = $basePath . 'python/report_generator.py';
            $syntaxCheck = shell_exec("python3 -m py_compile " . escapeshellarg($reportGenerator) . " 2>&1");
            
            if (empty($syntaxCheck) || strpos($syntaxCheck, 'SyntaxError') === false) {
                $this->pass("Python report_generator.py syntax is valid");
            } else {
                $this->fail("Python report_generator.py has syntax errors");
            }
        } else {
            $this->fail("Missing Python scripts: " . implode(', ', $missingScripts));
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
    $test = new ReportingTest();
    $test->run();
} else {
    echo "<html><body><pre>";
    $test = new ReportingTest();
    $test->run();
    echo "</pre></body></html>";
}
?>
