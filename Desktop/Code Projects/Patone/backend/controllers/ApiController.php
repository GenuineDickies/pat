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

    // Send success JSON response (override parent for API consistency)
    protected function jsonSuccess($data = [], $statusCode = 200) {
        $response = ['success' => true];
        
        if (!empty($data)) {
            $response['data'] = $data;
        }
        
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($response);
        exit;
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

    // ========== CUSTOMER CRUD OPERATIONS ==========

    // Create customer (API)
    public function createCustomer() {
        $this->authenticateApi();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = $_POST;
            }

            // Validate required fields
            $required = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->jsonError("Field '$field' is required", 400);
                }
            }

            // Validate email
            if (!isValidEmail($data['email'])) {
                $this->jsonError("Invalid email address", 400);
            }

            $customerId = $this->customerModel->create($data);

            logActivity('api_customer_created', "Customer created via API: #$customerId");

            $this->jsonSuccess([
                'customer_id' => $customerId,
                'message' => 'Customer created successfully'
            ], 201);

        } catch (Exception $e) {
            error_log("API create customer error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 400);
        }
    }

    // Update customer (API)
    public function updateCustomer($id) {
        $this->authenticateApi();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = $_POST;
            }

            // Check if customer exists
            $customer = $this->customerModel->getById($id);
            if (!$customer) {
                $this->jsonError('Customer not found', 404);
            }

            // Validate email if provided
            if (isset($data['email']) && !isValidEmail($data['email'])) {
                $this->jsonError("Invalid email address", 400);
            }

            $this->customerModel->update($id, $data);

            logActivity('api_customer_updated', "Customer updated via API: #$id");

            $this->jsonSuccess([
                'customer_id' => $id,
                'message' => 'Customer updated successfully'
            ]);

        } catch (Exception $e) {
            error_log("API update customer error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 400);
        }
    }

    // Delete customer (API)
    public function deleteCustomer($id) {
        $this->authenticateApi();

        try {
            // Check if customer exists
            $customer = $this->customerModel->getById($id);
            if (!$customer) {
                $this->jsonError('Customer not found', 404);
            }

            $this->customerModel->delete($id);

            logActivity('api_customer_deleted', "Customer deleted via API: #$id");

            $this->jsonSuccess([
                'customer_id' => $id,
                'message' => 'Customer deleted successfully'
            ]);

        } catch (Exception $e) {
            error_log("API delete customer error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 400);
        }
    }

    // ========== SERVICE REQUEST CRUD OPERATIONS ==========

    // Update service request (API)
    public function updateRequest($id) {
        $this->authenticateApi();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = $_POST;
            }

            // Check if request exists
            $request = $this->requestModel->getById($id);
            if (!$request) {
                $this->jsonError('Request not found', 404);
            }

            $this->requestModel->update($id, $data);

            logActivity('api_request_updated', "Request updated via API: #$id");

            $this->jsonSuccess([
                'request_id' => $id,
                'message' => 'Request updated successfully'
            ]);

        } catch (Exception $e) {
            error_log("API update request error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 400);
        }
    }

    // Delete service request (API)
    public function deleteRequest($id) {
        $this->authenticateApi();

        try {
            // Check if request exists
            $request = $this->requestModel->getById($id);
            if (!$request) {
                $this->jsonError('Request not found', 404);
            }

            $this->requestModel->delete($id);

            logActivity('api_request_deleted', "Request deleted via API: #$id");

            $this->jsonSuccess([
                'request_id' => $id,
                'message' => 'Request deleted successfully'
            ]);

        } catch (Exception $e) {
            error_log("API delete request error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 400);
        }
    }

    // ========== DRIVER OPERATIONS ==========

    // Update driver (API)
    public function updateDriver($id) {
        $this->authenticateApi();

        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = $_POST;
            }

            // Check if driver exists
            $driver = $this->driverModel->getById($id);
            if (!$driver) {
                $this->jsonError('Driver not found', 404);
            }

            // Validate email if provided
            if (isset($data['email']) && !isValidEmail($data['email'])) {
                $this->jsonError("Invalid email address", 400);
            }

            $this->driverModel->update($id, $data);

            logActivity('api_driver_updated', "Driver updated via API: #$id");

            $this->jsonSuccess([
                'driver_id' => $id,
                'message' => 'Driver updated successfully'
            ]);

        } catch (Exception $e) {
            error_log("API update driver error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 400);
        }
    }

    // ========== AUTHENTICATION ENDPOINTS ==========

    // Login endpoint (API)
    public function login() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data)) {
                $data = $_POST;
            }

            $email = sanitize($data['email'] ?? '');
            $password = $data['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->jsonError('Email and password are required', 400);
            }

            // Check user credentials
            require_once BACKEND_PATH . 'models/User.php';
            $userModel = new User();
            $user = $userModel->getByEmail($email);

            if (!$user || !verifyPassword($password, $user['password'])) {
                logActivity('api_login_failed', "Failed login attempt via API: $email");
                $this->jsonError('Invalid credentials', 401);
            }

            if ($user['status'] !== 'active') {
                $this->jsonError('Account is inactive', 403);
            }

            // Generate JWT token (simplified - in production use proper JWT library)
            $token = $this->generateApiToken($user['id'], $user['email'], $user['role']);

            // Update last login
            $userModel->updateLastLogin($user['id']);

            logActivity('api_login_success', "Successful login via API: {$user['email']}");

            $this->jsonSuccess([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ],
                'expires_in' => 86400 // 24 hours
            ]);

        } catch (Exception $e) {
            error_log("API login error: " . $e->getMessage());
            $this->jsonError('Authentication failed', 500);
        }
    }

    // Logout endpoint (API)
    public function logout() {
        try {
            $token = $this->getAuthToken();
            
            if ($token) {
                // In production, add token to blacklist
                logActivity('api_logout', "User logged out via API");
            }

            $this->jsonSuccess([
                'message' => 'Logged out successfully'
            ]);

        } catch (Exception $e) {
            error_log("API logout error: " . $e->getMessage());
            $this->jsonError('Logout failed', 500);
        }
    }

    // Refresh token endpoint (API)
    public function refresh() {
        try {
            $token = $this->getAuthToken();
            
            if (!$token) {
                $this->jsonError('No token provided', 401);
            }

            // Verify and decode token
            $payload = $this->verifyApiToken($token);
            
            if (!$payload) {
                $this->jsonError('Invalid token', 401);
            }

            // Generate new token
            $newToken = $this->generateApiToken($payload['user_id'], $payload['email'], $payload['role']);

            $this->jsonSuccess([
                'token' => $newToken,
                'expires_in' => 86400 // 24 hours
            ]);

        } catch (Exception $e) {
            error_log("API refresh error: " . $e->getMessage());
            $this->jsonError('Token refresh failed', 500);
        }
    }

    // ========== REPORT ENDPOINTS ==========

    // Daily report (API)
    public function getDailyReport() {
        $this->authenticateApi();

        try {
            $date = sanitize($_GET['date'] ?? date('Y-m-d'));
            
            $dateStart = $date . ' 00:00:00';
            $dateEnd = $date . ' 23:59:59';

            $stats = $this->requestModel->getStats($dateStart, $dateEnd);
            
            $requests = $this->db->getRows(
                "SELECT sr.id, sr.status, sr.priority, sr.created_at,
                        c.first_name as customer_first_name, c.last_name as customer_last_name,
                        d.first_name as driver_first_name, d.last_name as driver_last_name,
                        st.name as service_type_name, sr.final_cost
                 FROM service_requests sr
                 LEFT JOIN customers c ON sr.customer_id = c.id
                 LEFT JOIN drivers d ON sr.driver_id = d.id
                 LEFT JOIN service_types st ON sr.service_type_id = st.id
                 WHERE sr.created_at BETWEEN ? AND ?
                 ORDER BY sr.created_at DESC",
                [$dateStart, $dateEnd]
            );

            $this->jsonSuccess([
                'date' => $date,
                'stats' => $stats,
                'requests' => $requests
            ]);

        } catch (Exception $e) {
            error_log("API daily report error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 500);
        }
    }

    // Monthly report (API)
    public function getMonthlyReport() {
        $this->authenticateApi();

        try {
            $year = intval($_GET['year'] ?? date('Y'));
            $month = intval($_GET['month'] ?? date('m'));

            $dateStart = sprintf('%04d-%02d-01 00:00:00', $year, $month);
            $dateEnd = date('Y-m-t 23:59:59', strtotime($dateStart));

            $stats = $this->requestModel->getStats($dateStart, $dateEnd);

            // Get daily breakdown
            $dailyStats = $this->db->getRows(
                "SELECT DATE(created_at) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'completed' THEN final_cost ELSE 0 END) as revenue
                 FROM service_requests
                 WHERE created_at BETWEEN ? AND ?
                 GROUP BY DATE(created_at)
                 ORDER BY date ASC",
                [$dateStart, $dateEnd]
            );

            // Get service type breakdown
            $serviceTypeStats = $this->db->getRows(
                "SELECT st.name,
                        COUNT(sr.id) as total,
                        SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) as completed,
                        AVG(CASE WHEN sr.status = 'completed' THEN sr.final_cost ELSE NULL END) as avg_cost
                 FROM service_types st
                 LEFT JOIN service_requests sr ON st.id = sr.service_type_id 
                     AND sr.created_at BETWEEN ? AND ?
                 GROUP BY st.id, st.name
                 ORDER BY total DESC",
                [$dateStart, $dateEnd]
            );

            $this->jsonSuccess([
                'year' => $year,
                'month' => $month,
                'period' => date('F Y', strtotime($dateStart)),
                'stats' => $stats,
                'daily_breakdown' => $dailyStats,
                'service_type_breakdown' => $serviceTypeStats
            ]);

        } catch (Exception $e) {
            error_log("API monthly report error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 500);
        }
    }

    // Custom report (API)
    public function getCustomReport() {
        $this->authenticateApi();

        try {
            $startDate = sanitize($_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')));
            $endDate = sanitize($_GET['end_date'] ?? date('Y-m-d'));
            
            $dateStart = $startDate . ' 00:00:00';
            $dateEnd = $endDate . ' 23:59:59';

            $stats = $this->requestModel->getStats($dateStart, $dateEnd);

            // Get driver performance
            $driverStats = $this->db->getRows(
                "SELECT d.id, CONCAT(d.first_name, ' ', d.last_name) as name,
                        COUNT(sr.id) as total_jobs,
                        SUM(CASE WHEN sr.status = 'completed' THEN 1 ELSE 0 END) as completed_jobs,
                        AVG(CASE WHEN sr.status = 'completed' THEN sr.final_cost ELSE NULL END) as avg_revenue,
                        d.rating
                 FROM drivers d
                 LEFT JOIN service_requests sr ON d.id = sr.driver_id 
                     AND sr.created_at BETWEEN ? AND ?
                 GROUP BY d.id, d.first_name, d.last_name, d.rating
                 ORDER BY completed_jobs DESC",
                [$dateStart, $dateEnd]
            );

            // Get top customers
            $topCustomers = $this->db->getRows(
                "SELECT c.id, CONCAT(c.first_name, ' ', c.last_name) as name,
                        COUNT(sr.id) as total_requests,
                        SUM(CASE WHEN sr.status = 'completed' THEN sr.final_cost ELSE 0 END) as total_spent
                 FROM customers c
                 LEFT JOIN service_requests sr ON c.id = sr.customer_id 
                     AND sr.created_at BETWEEN ? AND ?
                 GROUP BY c.id, c.first_name, c.last_name
                 HAVING total_requests > 0
                 ORDER BY total_spent DESC
                 LIMIT 10",
                [$dateStart, $dateEnd]
            );

            $this->jsonSuccess([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'stats' => $stats,
                'driver_performance' => $driverStats,
                'top_customers' => $topCustomers
            ]);

        } catch (Exception $e) {
            error_log("API custom report error: " . $e->getMessage());
            $this->jsonError($e->getMessage(), 500);
        }
    }

    // ========== HELPER METHODS ==========

    // Generate API token (simplified JWT-like token)
    private function generateApiToken($userId, $email, $role) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        
        $payload = base64_encode(json_encode([
            'user_id' => $userId,
            'email' => $email,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + 86400 // 24 hours
        ]));
        
        $signature = hash_hmac('sha256', "$header.$payload", ENCRYPTION_KEY);
        
        return "$header.$payload.$signature";
    }

    // Verify API token
    private function verifyApiToken($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header, $payload, $signature) = $parts;
        
        // Verify signature
        $validSignature = hash_hmac('sha256', "$header.$payload", ENCRYPTION_KEY);
        
        if ($signature !== $validSignature) {
            return false;
        }
        
        // Decode payload
        $payloadData = json_decode(base64_decode($payload), true);
        
        // Check expiration
        if (isset($payloadData['exp']) && $payloadData['exp'] < time()) {
            return false;
        }
        
        return $payloadData;
    }

    // Get auth token from request
    private function getAuthToken() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        
        return $_GET['token'] ?? $_POST['token'] ?? null;
    }

    // Enhanced authentication for API with JWT support
    private function authenticateApi() {
        // Check rate limit first
        $clientIp = getClientIp();
        if (!checkApiRateLimit($clientIp, 100, 60)) {
            $this->jsonError('Rate limit exceeded. Please try again later.', 429);
        }
        
        // Try JWT token first
        $token = $this->getAuthToken();
        
        if ($token) {
            $payload = $this->verifyApiToken($token);
            if ($payload) {
                // Token is valid, set session-like data for compatibility
                $_SESSION['api_user_id'] = $payload['user_id'];
                $_SESSION['api_user_email'] = $payload['email'];
                $_SESSION['api_user_role'] = $payload['role'];
                return;
            }
        }
        
        // Fallback to session-based auth
        if (isLoggedIn()) {
            return;
        }
        
        $this->jsonError('Unauthorized - Invalid or missing authentication token', 401);
    }
}
?>

