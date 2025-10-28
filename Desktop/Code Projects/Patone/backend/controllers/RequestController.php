<?php
/**
 * Roadside Assistance Admin Platform - Request Controller
 * Handles service request operations
 */

require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/ServiceRequest.php';
require_once BACKEND_PATH . 'models/Customer.php';
require_once BACKEND_PATH . 'models/Driver.php';
require_once BACKEND_PATH . 'models/ServiceType.php';
require_once BACKEND_PATH . 'models/RequestHistory.php';
require_once BACKEND_PATH . 'models/RequestCommunication.php';
require_once BACKEND_PATH . 'models/Notification.php';

class RequestController extends Controller {
    private $requestModel;
    private $customerModel;
    private $driverModel;
    private $serviceTypeModel;
    private $historyModel;
    private $communicationModel;
    private $notificationModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->requestModel = new ServiceRequest();
        $this->customerModel = new Customer();
        $this->driverModel = new Driver();
        $this->serviceTypeModel = new ServiceType();
        $this->historyModel = new RequestHistory();
        $this->communicationModel = new RequestCommunication();
        $this->notificationModel = new Notification();
    }

    // List service requests
    public function index() {
        try {
            $pagination = $this->getPaginationParams();
            $search = sanitize($_GET['search'] ?? '');
            $status = sanitize($_GET['status'] ?? '');
            $priority = sanitize($_GET['priority'] ?? '');
            $dateFrom = sanitize($_GET['date_from'] ?? '');
            $dateTo = sanitize($_GET['date_to'] ?? '');

            $filters = [];
            if (!empty($status)) {
                $filters['status'] = $status;
            }
            if (!empty($priority)) {
                $filters['priority'] = $priority;
            }
            if (!empty($dateFrom)) {
                $filters['date_from'] = $dateFrom;
            }
            if (!empty($dateTo)) {
                $filters['date_to'] = $dateTo;
            }

            $result = $this->requestModel->getAll($pagination['limit'], $pagination['offset'], $search, $filters);
            $totalPages = ceil($result['total'] / $pagination['limit']);

            $data = [
                'pageTitle' => 'Service Requests',
                'requests' => $result['requests'],
                'totalRequests' => $result['total'],
                'currentPage' => $pagination['page'],
                'totalPages' => $totalPages,
                'search' => $search,
                'status' => $status,
                'priority' => $priority,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ];

            $this->render('requests', $data);

        } catch (Exception $e) {
            error_log("Request index error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'dashboard', 'Error loading requests.');
        }
    }

    // Show add request form
    public function add() {
        $this->requirePermission('manage_requests');

        try {
            $customers = $this->customerModel->all();
            $serviceTypes = $this->serviceTypeModel->getActive();
            $drivers = $this->driverModel->getAvailable();

            $data = [
                'pageTitle' => 'Create Service Request',
                'customers' => $customers,
                'serviceTypes' => $serviceTypes,
                'drivers' => $drivers,
                'action' => 'add'
            ];

            $this->render('request_form', $data);

        } catch (Exception $e) {
            error_log("Request add form error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'requests', 'Error loading form.');
        }
    }

    // Process add request
    public function doAdd() {
        $this->requirePermission('manage_requests');
        $this->validateCSRF();

        try {
            $data = $this->getPostData();

            // Validate required fields
            $errors = $this->validateRequired($data, [
                'customer_id', 'service_type_id', 'location_address', 
                'location_city', 'location_state'
            ]);

            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $data;
                $this->redirect(SITE_URL . 'requests/add');
            }

            $requestId = $this->requestModel->create($data);

            // Log history
            $this->historyModel->addEntry($requestId, 'created', null, null, 'Request created');
            
            // Log communication
            $this->communicationModel->logSystem($requestId, 'Service request created');

            logActivity('request_created', "Service request created: #$requestId");
            $this->redirectWithSuccess(SITE_URL . "requests/$requestId", 'Service request created successfully.');

        } catch (Exception $e) {
            error_log("Request add error: " . $e->getMessage());
            $_SESSION['form_errors'] = [$e->getMessage()];
            $_SESSION['form_data'] = $_POST;
            $this->redirect(SITE_URL . 'requests/add');
        }
    }

    // View request details
    public function view($id) {
        try {
            $request = $this->requestModel->getById($id);

            if (!$request) {
                $this->redirectWithError(SITE_URL . 'requests', 'Request not found.');
            }

            $drivers = $this->driverModel->getAvailable();
            $history = $this->historyModel->getByRequest($id);
            $communications = $this->communicationModel->getByRequest($id);

            $data = [
                'pageTitle' => 'Service Request #' . $id,
                'request' => $request,
                'drivers' => $drivers,
                'history' => $history,
                'communications' => $communications
            ];

            $this->render('request_details', $data);

        } catch (Exception $e) {
            error_log("Request view error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'requests', 'Error loading request.');
        }
    }

    // Update request
    public function update($id) {
        $this->requirePermission('manage_requests');
        $this->validateCSRF();

        try {
            $data = $this->getPostData();
            $this->requestModel->update($id, $data);

            logActivity('request_updated', "Service request updated: #$id");
            $this->redirectWithSuccess(SITE_URL . "requests/$id", 'Request updated successfully.');

        } catch (Exception $e) {
            error_log("Request update error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . "requests/$id", $e->getMessage());
        }
    }

    // Assign driver to request
    public function assignDriver($id) {
        $this->requirePermission('dispatch_requests');

        try {
            $driverId = intval($_POST['driver_id'] ?? 0);

            if ($driverId <= 0) {
                $this->jsonError('Invalid driver ID.');
            }

            // Get request and driver details for logging
            $request = $this->requestModel->getById($id);
            $driver = $this->driverModel->find($driverId);

            $this->requestModel->assignDriver($id, $driverId);

            // Update driver status to busy
            $this->driverModel->updateStatus($driverId, 'busy');

            // Log history
            $driverName = $driver ? $driver['first_name'] . ' ' . $driver['last_name'] : "Driver #$driverId";
            $this->historyModel->logDriverAssignment($id, $driverId, $driverName);
            
            // Log communication
            $this->communicationModel->logSystem($id, "Driver assigned: $driverName");

            // Send notification to driver (if driver has a user account)
            if ($driver && $driver['user_id']) {
                $this->notificationModel->notifyRequestAssigned(
                    $driver['user_id'], 
                    $id, 
                    "Service: {$request['service_type_name']} at {$request['location_city']}, {$request['location_state']}"
                );
            }

            logActivity('request_assigned', "Request #$id assigned to driver #$driverId");
            $this->jsonSuccess(['request_id' => $id, 'driver_id' => $driverId], 'Driver assigned successfully.');

        } catch (Exception $e) {
            error_log("Request assign error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Update request status
    public function updateStatus($id) {
        $this->requirePermission('manage_requests');

        try {
            $status = sanitize($_POST['status'] ?? '');
            $notes = sanitize($_POST['notes'] ?? '');

            if (empty($status)) {
                $this->jsonError('Status is required.');
            }

            // Get current status for logging
            $request = $this->requestModel->getById($id);
            $oldStatus = $request['status'];

            $this->requestModel->updateStatus($id, $status, $notes);

            // Log history
            $this->historyModel->logStatusChange($id, $oldStatus, $status, $notes);
            
            // Log communication
            $this->communicationModel->logSystem($id, "Status changed from $oldStatus to $status" . ($notes ? ": $notes" : ""));

            // If completed, update driver status back to available
            if ($status === 'completed') {
                if ($request && $request['driver_id']) {
                    $this->driverModel->updateStatus($request['driver_id'], 'available');
                    $this->driverModel->incrementJobCounters($request['driver_id'], true);
                    
                    // Notify driver of completion
                    $driver = $this->driverModel->find($request['driver_id']);
                    if ($driver && $driver['user_id']) {
                        $this->notificationModel->notifyRequestCompleted($driver['user_id'], $id);
                    }
                }
            }

            logActivity('request_status_updated', "Request #$id status updated to: $status");
            $this->jsonSuccess(['status' => $status], 'Status updated successfully.');

        } catch (Exception $e) {
            error_log("Request status update error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Complete request
    public function complete($id) {
        $this->requirePermission('manage_requests');
        $this->validateCSRF();

        try {
            $finalCost = floatval($_POST['final_cost'] ?? 0);
            $driverNotes = sanitize($_POST['driver_notes'] ?? '');

            $this->requestModel->complete($id, $finalCost, $driverNotes);

            // Log history
            $this->historyModel->logCompletion($id, $finalCost);
            
            // Log communication
            $this->communicationModel->logSystem($id, "Request completed. Final cost: $" . number_format($finalCost, 2));

            // Update driver status
            $request = $this->requestModel->getById($id);
            if ($request && $request['driver_id']) {
                $this->driverModel->updateStatus($request['driver_id'], 'available');
                $this->driverModel->incrementJobCounters($request['driver_id'], true);
                
                // Notify driver
                $driver = $this->driverModel->find($request['driver_id']);
                if ($driver && $driver['user_id']) {
                    $this->notificationModel->notifyRequestCompleted($driver['user_id'], $id);
                }
            }

            logActivity('request_completed', "Request #$id marked as completed");
            $this->redirectWithSuccess(SITE_URL . "requests/$id", 'Request completed successfully.');

        } catch (Exception $e) {
            error_log("Request complete error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . "requests/$id", $e->getMessage());
        }
    }

    // Cancel request
    public function cancel($id) {
        $this->requirePermission('manage_requests');
        $this->validateCSRF();

        try {
            $reason = sanitize($_POST['cancellation_reason'] ?? '');

            $this->requestModel->cancel($id, $reason);

            // Log history
            $this->historyModel->logCancellation($id, $reason);
            
            // Log communication
            $this->communicationModel->logSystem($id, "Request cancelled: $reason");

            // Update driver status if assigned
            $request = $this->requestModel->getById($id);
            if ($request && $request['driver_id']) {
                $this->driverModel->updateStatus($request['driver_id'], 'available');
                
                // Notify driver
                $driver = $this->driverModel->find($request['driver_id']);
                if ($driver && $driver['user_id']) {
                    $this->notificationModel->create(
                        $driver['user_id'],
                        'request_cancelled',
                        'Request Cancelled',
                        "Request #$id has been cancelled: $reason",
                        'request',
                        $id
                    );
                }
            }

            logActivity('request_cancelled', "Request #$id cancelled");
            $this->redirectWithSuccess(SITE_URL . 'requests', 'Request cancelled successfully.');

        } catch (Exception $e) {
            error_log("Request cancel error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . "requests/$id", $e->getMessage());
        }
    }

    // Add rating to completed request
    public function addRating($id) {
        try {
            $rating = intval($_POST['rating'] ?? 0);

            if ($rating < 1 || $rating > 5) {
                $this->jsonError('Rating must be between 1 and 5.');
            }

            $this->requestModel->addRating($id, $rating);

            // Log history
            $this->historyModel->addEntry($id, 'rating_added', null, $rating, "Customer rated: $rating stars");

            // Update driver rating
            $request = $this->requestModel->getById($id);
            if ($request && $request['driver_id']) {
                $avgRating = $this->db->getValue(
                    "SELECT AVG(rating) FROM service_requests WHERE driver_id = ? AND rating IS NOT NULL",
                    [$request['driver_id']]
                );
                $this->driverModel->updateRating($request['driver_id'], $avgRating);
            }

            logActivity('request_rated', "Request #$id rated: $rating stars");
            $this->jsonSuccess(['rating' => $rating], 'Rating added successfully.');

        } catch (Exception $e) {
            error_log("Request rating error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Auto-dispatch pending requests to available drivers
    public function autoDispatch() {
        $this->requirePermission('dispatch_requests');

        try {
            $pendingRequests = $this->requestModel->getPending();
            $assignedCount = 0;

            foreach ($pendingRequests as $request) {
                // Find best available driver
                $driver = $this->findBestDriver($request);

                if ($driver) {
                    // Assign driver
                    $this->requestModel->assignDriver($request['id'], $driver['id']);
                    $this->driverModel->updateStatus($driver['id'], 'busy');

                    // Log history
                    $driverName = $driver['first_name'] . ' ' . $driver['last_name'];
                    $this->historyModel->logDriverAssignment($request['id'], $driver['id'], $driverName);
                    $this->communicationModel->logSystem($request['id'], "Auto-assigned to driver: $driverName");

                    // Send notification
                    if ($driver['user_id']) {
                        $this->notificationModel->notifyRequestAssigned(
                            $driver['user_id'],
                            $request['id'],
                            "Service: {$request['service_type_name']} at {$request['location_city']}, {$request['location_state']}"
                        );
                    }

                    $assignedCount++;
                    logActivity('auto_dispatch', "Request #{$request['id']} auto-assigned to driver #{$driver['id']}");
                }
            }

            $this->jsonSuccess(['assigned' => $assignedCount], "$assignedCount request(s) dispatched successfully.");

        } catch (Exception $e) {
            error_log("Auto dispatch error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Find best available driver for a request
    private function findBestDriver($request) {
        // Get available drivers
        $drivers = $this->driverModel->getAvailable();

        if (empty($drivers)) {
            return null;
        }

        // Simple algorithm: prioritize by rating and availability
        // In a real-world scenario, you would factor in:
        // - Distance from request location
        // - Driver specialization
        // - Current workload
        // - Historical performance

        usort($drivers, function($a, $b) {
            // Sort by rating (descending), then by completed jobs (descending)
            if ($a['rating'] != $b['rating']) {
                return $b['rating'] <=> $a['rating'];
            }
            return $b['completed_jobs'] <=> $a['completed_jobs'];
        });

        return $drivers[0];
    }
}
?>
