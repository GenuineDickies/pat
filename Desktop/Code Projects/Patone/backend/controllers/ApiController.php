<?php
/**
 * Roadside Assistance Admin Platform - API Controller
 * Handles RESTful API endpoints for mobile and third-party integrations
 */

require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Customer.php';
require_once BACKEND_PATH . 'models/Driver.php';
require_once BACKEND_PATH . 'models/ServiceRequest.php';
require_once BACKEND_PATH . 'models/ServiceType.php';

class ApiController extends Controller {
    private $customerModel;
    private $driverModel;
    private $requestModel;
    private $serviceTypeModel;

    public function __construct() {
        parent::__construct();
        $this->customerModel = new Customer();
        $this->driverModel = new Driver();
        $this->requestModel = new ServiceRequest();
        $this->serviceTypeModel = new ServiceType();
    }

    // Authenticate API request (basic authentication)
    private function authenticateApi() {
        // For now, check if user is logged in
        // In production, implement JWT or API key authentication
        if (!isLoggedIn()) {
            $this->jsonError('Unauthorized', 401);
        }
    }

    // Get all customers (API)
    public function getCustomers() {
        $this->authenticateApi();

        try {
            $limit = min(100, intval($_GET['limit'] ?? 25));
            $offset = intval($_GET['offset'] ?? 0);
            $search = sanitize($_GET['search'] ?? '');

            $result = $this->customerModel->getAll($limit, $offset, $search);

            $this->jsonSuccess([
                'customers' => $result['customers'],
                'total' => $result['total'],
                'limit' => $limit,
                'offset' => $offset
            ]);

        } catch (Exception $e) {
            error_log("API customers error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Get single customer (API)
    public function getCustomer($id) {
        $this->authenticateApi();

        try {
            $customer = $this->customerModel->getById($id);

            if (!$customer) {
                $this->jsonError('Customer not found', 404);
            }

            $this->jsonSuccess(['customer' => $customer]);

        } catch (Exception $e) {
            error_log("API customer error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Get all service requests (API)
    public function getRequests() {
        $this->authenticateApi();

        try {
            $limit = min(100, intval($_GET['limit'] ?? 25));
            $offset = intval($_GET['offset'] ?? 0);
            $status = sanitize($_GET['status'] ?? '');

            $filters = [];
            if (!empty($status)) {
                $filters['status'] = $status;
            }

            $result = $this->requestModel->getAll($limit, $offset, '', $filters);

            $this->jsonSuccess([
                'requests' => $result['requests'],
                'total' => $result['total'],
                'limit' => $limit,
                'offset' => $offset
            ]);

        } catch (Exception $e) {
            error_log("API requests error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Get single service request (API)
    public function getRequest($id) {
        $this->authenticateApi();

        try {
            $request = $this->requestModel->getById($id);

            if (!$request) {
                $this->jsonError('Request not found', 404);
            }

            $this->jsonSuccess(['request' => $request]);

        } catch (Exception $e) {
            error_log("API request error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Get all drivers (API)
    public function getDrivers() {
        $this->authenticateApi();

        try {
            $limit = min(100, intval($_GET['limit'] ?? 25));
            $offset = intval($_GET['offset'] ?? 0);
            $status = sanitize($_GET['status'] ?? '');

            $filters = [];
            if (!empty($status)) {
                $filters['status'] = $status;
            }

            $result = $this->driverModel->getAll($limit, $offset, '', $filters);

            $this->jsonSuccess([
                'drivers' => $result['drivers'],
                'total' => $result['total'],
                'limit' => $limit,
                'offset' => $offset
            ]);

        } catch (Exception $e) {
            error_log("API drivers error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Get single driver (API)
    public function getDriver($id) {
        $this->authenticateApi();

        try {
            $driver = $this->driverModel->getById($id);

            if (!$driver) {
                $this->jsonError('Driver not found', 404);
            }

            $this->jsonSuccess(['driver' => $driver]);

        } catch (Exception $e) {
            error_log("API driver error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Get available drivers (API)
    public function getAvailableDrivers() {
        $this->authenticateApi();

        try {
            $latitude = floatval($_GET['latitude'] ?? 0);
            $longitude = floatval($_GET['longitude'] ?? 0);
            $maxDistance = intval($_GET['max_distance'] ?? 50);

            $drivers = $this->driverModel->getAvailable($latitude, $longitude, $maxDistance);

            $this->jsonSuccess([
                'drivers' => $drivers,
                'count' => count($drivers)
            ]);

        } catch (Exception $e) {
            error_log("API available drivers error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Get service types (API)
    public function getServiceTypes() {
        $this->authenticateApi();

        try {
            $serviceTypes = $this->serviceTypeModel->getActive();

            $this->jsonSuccess([
                'service_types' => $serviceTypes,
                'count' => count($serviceTypes)
            ]);

        } catch (Exception $e) {
            error_log("API service types error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Get dashboard statistics (API)
    public function getDashboardStats() {
        $this->authenticateApi();

        try {
            $customerStats = $this->customerModel->getStats();
            $driverStats = $this->driverModel->getStats();
            $requestStats = $this->requestModel->getStats(date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59'));

            $stats = [
                'customers' => $customerStats,
                'drivers' => $driverStats,
                'requests' => $requestStats,
                'timestamp' => date('c')
            ];

            $this->jsonSuccess($stats);

        } catch (Exception $e) {
            error_log("API dashboard stats error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Create service request (API)
    public function createRequest() {
        $this->authenticateApi();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = $_POST;
            }

            // Validate required fields
            $required = ['customer_id', 'service_type_id', 'location_address', 'location_city', 'location_state'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->jsonError("Field '$field' is required");
                }
            }

            $requestId = $this->requestModel->create($data);

            logActivity('api_request_created', "Service request created via API: #$requestId");

            $this->jsonSuccess([
                'request_id' => $requestId,
                'message' => 'Service request created successfully'
            ], 201);

        } catch (Exception $e) {
            error_log("API create request error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Update driver location (API)
    public function updateDriverLocation($driverId) {
        $this->authenticateApi();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = $_POST;
            }

            $latitude = floatval($data['latitude'] ?? 0);
            $longitude = floatval($data['longitude'] ?? 0);

            if ($latitude == 0 || $longitude == 0) {
                $this->jsonError('Invalid coordinates');
            }

            $this->driverModel->updateLocation($driverId, $latitude, $longitude);

            $this->jsonSuccess([
                'driver_id' => $driverId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'timestamp' => date('c')
            ]);

        } catch (Exception $e) {
            error_log("API update location error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Update request status (API)
    public function updateRequestStatus($requestId) {
        $this->authenticateApi();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = $_POST;
            }

            $status = sanitize($data['status'] ?? '');
            $notes = sanitize($data['notes'] ?? '');

            if (empty($status)) {
                $this->jsonError('Status is required');
            }

            $this->requestModel->updateStatus($requestId, $status, $notes);

            logActivity('api_request_updated', "Request #$requestId status updated via API: $status");

            $this->jsonSuccess([
                'request_id' => $requestId,
                'status' => $status,
                'timestamp' => date('c')
            ]);

        } catch (Exception $e) {
            error_log("API update status error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }
}
?>
