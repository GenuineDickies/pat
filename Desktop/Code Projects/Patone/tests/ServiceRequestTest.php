<?php
/**
 * Service Request Management System Tests
 * Tests request creation, status management, dispatch, and notifications
 */

// Load configuration
require_once __DIR__ . '/../config.php';
require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Model.php';
require_once BACKEND_PATH . 'models/ServiceRequest.php';
require_once BACKEND_PATH . 'models/RequestHistory.php';
require_once BACKEND_PATH . 'models/RequestCommunication.php';
require_once BACKEND_PATH . 'models/Notification.php';
require_once BACKEND_PATH . 'models/Customer.php';
require_once BACKEND_PATH . 'models/Driver.php';
require_once BACKEND_PATH . 'models/ServiceType.php';

class ServiceRequestTest {
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    private $db;
    private $requestModel;
    private $historyModel;
    private $communicationModel;
    private $notificationModel;
    private $customerModel;
    private $driverModel;
    private $serviceTypeModel;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->requestModel = new ServiceRequest();
        $this->historyModel = new RequestHistory();
        $this->communicationModel = new RequestCommunication();
        $this->notificationModel = new Notification();
        $this->customerModel = new Customer();
        $this->driverModel = new Driver();
        $this->serviceTypeModel = new ServiceType();
    }

    public function run() {
        echo "=================================\n";
        echo "Service Request Management Tests\n";
        echo "=================================\n\n";

        // Test models exist
        $this->testModelsExist();

        // Test request model methods
        $this->testRequestModelMethods();

        // Test history tracking
        $this->testHistoryTracking();

        // Test communication logs
        $this->testCommunicationLogs();

        // Test notifications
        $this->testNotifications();

        // Test dispatch algorithm
        $this->testDispatchAlgorithm();

        // Display results
        $this->displayResults();
    }

    private function testModelsExist() {
        try {
            if (class_exists('ServiceRequest')) {
                $this->pass("ServiceRequest model exists");
            } else {
                $this->fail("ServiceRequest model not found");
            }

            if (class_exists('RequestHistory')) {
                $this->pass("RequestHistory model exists");
            } else {
                $this->fail("RequestHistory model not found");
            }

            if (class_exists('RequestCommunication')) {
                $this->pass("RequestCommunication model exists");
            } else {
                $this->fail("RequestCommunication model not found");
            }

            if (class_exists('Notification')) {
                $this->pass("Notification model exists");
            } else {
                $this->fail("Notification model not found");
            }
        } catch (Exception $e) {
            $this->fail("Model existence test failed: " . $e->getMessage());
        }
    }

    private function testRequestModelMethods() {
        try {
            // Test getById method exists
            if (method_exists($this->requestModel, 'getById')) {
                $this->pass("ServiceRequest::getById() method exists");
            } else {
                $this->fail("ServiceRequest::getById() method not found");
            }

            // Test getAll method exists
            if (method_exists($this->requestModel, 'getAll')) {
                $this->pass("ServiceRequest::getAll() method exists");
            } else {
                $this->fail("ServiceRequest::getAll() method not found");
            }

            // Test create method exists
            if (method_exists($this->requestModel, 'create')) {
                $this->pass("ServiceRequest::create() method exists");
            } else {
                $this->fail("ServiceRequest::create() method not found");
            }

            // Test assignDriver method exists
            if (method_exists($this->requestModel, 'assignDriver')) {
                $this->pass("ServiceRequest::assignDriver() method exists");
            } else {
                $this->fail("ServiceRequest::assignDriver() method not found");
            }

            // Test updateStatus method exists
            if (method_exists($this->requestModel, 'updateStatus')) {
                $this->pass("ServiceRequest::updateStatus() method exists");
            } else {
                $this->fail("ServiceRequest::updateStatus() method not found");
            }

            // Test complete method exists
            if (method_exists($this->requestModel, 'complete')) {
                $this->pass("ServiceRequest::complete() method exists");
            } else {
                $this->fail("ServiceRequest::complete() method not found");
            }

            // Test cancel method exists
            if (method_exists($this->requestModel, 'cancel')) {
                $this->pass("ServiceRequest::cancel() method exists");
            } else {
                $this->fail("ServiceRequest::cancel() method not found");
            }

            // Test getPending method exists
            if (method_exists($this->requestModel, 'getPending')) {
                $this->pass("ServiceRequest::getPending() method exists");
            } else {
                $this->fail("ServiceRequest::getPending() method not found");
            }

            // Test getStats method exists
            if (method_exists($this->requestModel, 'getStats')) {
                $this->pass("ServiceRequest::getStats() method exists");
            } else {
                $this->fail("ServiceRequest::getStats() method not found");
            }

        } catch (Exception $e) {
            $this->fail("Request model methods test failed: " . $e->getMessage());
        }
    }

    private function testHistoryTracking() {
        try {
            // Test addEntry method exists
            if (method_exists($this->historyModel, 'addEntry')) {
                $this->pass("RequestHistory::addEntry() method exists");
            } else {
                $this->fail("RequestHistory::addEntry() method not found");
            }

            // Test getByRequest method exists
            if (method_exists($this->historyModel, 'getByRequest')) {
                $this->pass("RequestHistory::getByRequest() method exists");
            } else {
                $this->fail("RequestHistory::getByRequest() method not found");
            }

            // Test logStatusChange method exists
            if (method_exists($this->historyModel, 'logStatusChange')) {
                $this->pass("RequestHistory::logStatusChange() method exists");
            } else {
                $this->fail("RequestHistory::logStatusChange() method not found");
            }

            // Test logDriverAssignment method exists
            if (method_exists($this->historyModel, 'logDriverAssignment')) {
                $this->pass("RequestHistory::logDriverAssignment() method exists");
            } else {
                $this->fail("RequestHistory::logDriverAssignment() method not found");
            }

            // Test logCompletion method exists
            if (method_exists($this->historyModel, 'logCompletion')) {
                $this->pass("RequestHistory::logCompletion() method exists");
            } else {
                $this->fail("RequestHistory::logCompletion() method not found");
            }

            // Test logCancellation method exists
            if (method_exists($this->historyModel, 'logCancellation')) {
                $this->pass("RequestHistory::logCancellation() method exists");
            } else {
                $this->fail("RequestHistory::logCancellation() method not found");
            }

        } catch (Exception $e) {
            $this->fail("History tracking test failed: " . $e->getMessage());
        }
    }

    private function testCommunicationLogs() {
        try {
            // Test addLog method exists
            if (method_exists($this->communicationModel, 'addLog')) {
                $this->pass("RequestCommunication::addLog() method exists");
            } else {
                $this->fail("RequestCommunication::addLog() method not found");
            }

            // Test getByRequest method exists
            if (method_exists($this->communicationModel, 'getByRequest')) {
                $this->pass("RequestCommunication::getByRequest() method exists");
            } else {
                $this->fail("RequestCommunication::getByRequest() method not found");
            }

            // Test addNote method exists
            if (method_exists($this->communicationModel, 'addNote')) {
                $this->pass("RequestCommunication::addNote() method exists");
            } else {
                $this->fail("RequestCommunication::addNote() method not found");
            }

            // Test logEmail method exists
            if (method_exists($this->communicationModel, 'logEmail')) {
                $this->pass("RequestCommunication::logEmail() method exists");
            } else {
                $this->fail("RequestCommunication::logEmail() method not found");
            }

            // Test logSMS method exists
            if (method_exists($this->communicationModel, 'logSMS')) {
                $this->pass("RequestCommunication::logSMS() method exists");
            } else {
                $this->fail("RequestCommunication::logSMS() method not found");
            }

        } catch (Exception $e) {
            $this->fail("Communication logs test failed: " . $e->getMessage());
        }
    }

    private function testNotifications() {
        try {
            // Test createNotification method exists
            if (method_exists($this->notificationModel, 'createNotification')) {
                $this->pass("Notification::createNotification() method exists");
            } else {
                $this->fail("Notification::createNotification() method not found");
            }

            // Test getUnread method exists
            if (method_exists($this->notificationModel, 'getUnread')) {
                $this->pass("Notification::getUnread() method exists");
            } else {
                $this->fail("Notification::getUnread() method not found");
            }

            // Test markAsRead method exists
            if (method_exists($this->notificationModel, 'markAsRead')) {
                $this->pass("Notification::markAsRead() method exists");
            } else {
                $this->fail("Notification::markAsRead() method not found");
            }

            // Test notifyRequestAssigned method exists
            if (method_exists($this->notificationModel, 'notifyRequestAssigned')) {
                $this->pass("Notification::notifyRequestAssigned() method exists");
            } else {
                $this->fail("Notification::notifyRequestAssigned() method not found");
            }

            // Test notifyStatusChanged method exists
            if (method_exists($this->notificationModel, 'notifyStatusChanged')) {
                $this->pass("Notification::notifyStatusChanged() method exists");
            } else {
                $this->fail("Notification::notifyStatusChanged() method not found");
            }

            // Test notifyRequestCompleted method exists
            if (method_exists($this->notificationModel, 'notifyRequestCompleted')) {
                $this->pass("Notification::notifyRequestCompleted() method exists");
            } else {
                $this->fail("Notification::notifyRequestCompleted() method not found");
            }

        } catch (Exception $e) {
            $this->fail("Notifications test failed: " . $e->getMessage());
        }
    }

    private function testDispatchAlgorithm() {
        try {
            // Test that getPending returns an array
            $pendingRequests = $this->requestModel->getPending();
            if (is_array($pendingRequests)) {
                $this->pass("ServiceRequest::getPending() returns array");
            } else {
                $this->fail("ServiceRequest::getPending() should return array");
            }

            // Test that getAvailable exists on Driver model
            if (method_exists($this->driverModel, 'getAvailable')) {
                $this->pass("Driver::getAvailable() method exists for dispatch");
            } else {
                $this->fail("Driver::getAvailable() method not found");
            }

            // Test that updateStatus exists on Driver model
            if (method_exists($this->driverModel, 'updateStatus')) {
                $this->pass("Driver::updateStatus() method exists for dispatch");
            } else {
                $this->fail("Driver::updateStatus() method not found");
            }

        } catch (Exception $e) {
            $this->fail("Dispatch algorithm test failed: " . $e->getMessage());
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
    $test = new ServiceRequestTest();
    $test->run();
} else {
    echo "<html><body><pre>";
    $test = new ServiceRequestTest();
    $test->run();
    echo "</pre></body></html>";
}
?>
