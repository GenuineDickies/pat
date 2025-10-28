<?php
/**
 * Roadside Assistance Admin Platform - Driver Controller
 * Handles driver management operations
 */

require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Driver.php';

class DriverController extends Controller {
    private $driverModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->driverModel = new Driver();
    }

    // List drivers
    public function index() {
        try {
            $pagination = $this->getPaginationParams();
            $search = sanitize($_GET['search'] ?? '');
            $status = sanitize($_GET['status'] ?? '');

            $filters = [];
            if (!empty($status)) {
                $filters['status'] = $status;
            }

            $result = $this->driverModel->getAll($pagination['limit'], $pagination['offset'], $search, $filters);
            $totalPages = ceil($result['total'] / $pagination['limit']);

            $data = [
                'pageTitle' => 'Driver Management',
                'drivers' => $result['drivers'],
                'totalDrivers' => $result['total'],
                'currentPage' => $pagination['page'],
                'totalPages' => $totalPages,
                'search' => $search,
                'status' => $status
            ];

            $this->render('drivers', $data);

        } catch (Exception $e) {
            error_log("Driver index error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'dashboard', 'Error loading drivers.');
        }
    }

    // Show add driver form
    public function add() {
        $this->requirePermission('manage_drivers');

        $data = [
            'pageTitle' => 'Add Driver',
            'action' => 'add'
        ];

        $this->render('driver_form', $data);
    }

    // Process add driver
    public function doAdd() {
        $this->requirePermission('manage_drivers');
        $this->validateCSRF();

        try {
            $data = $this->getPostData();

            // Validate required fields
            $errors = $this->validateRequired($data, [
                'first_name', 'last_name', 'email', 'phone', 
                'license_number', 'license_state', 'license_expiry'
            ]);

            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $data;
                $this->redirect(SITE_URL . 'drivers/add');
            }

            // Validate email
            $emailError = $this->validateEmail($data['email']);
            if ($emailError) {
                $_SESSION['form_errors'] = [$emailError];
                $_SESSION['form_data'] = $data;
                $this->redirect(SITE_URL . 'drivers/add');
            }

            // Validate phone
            $phoneError = $this->validatePhone($data['phone']);
            if ($phoneError) {
                $_SESSION['form_errors'] = [$phoneError];
                $_SESSION['form_data'] = $data;
                $this->redirect(SITE_URL . 'drivers/add');
            }

            $driverId = $this->driverModel->create($data);

            logActivity('driver_created', "Driver created: {$data['first_name']} {$data['last_name']}");
            $this->redirectWithSuccess(SITE_URL . 'drivers', 'Driver added successfully.');

        } catch (Exception $e) {
            error_log("Driver add error: " . $e->getMessage());
            $_SESSION['form_errors'] = [$e->getMessage()];
            $_SESSION['form_data'] = $_POST;
            $this->redirect(SITE_URL . 'drivers/add');
        }
    }

    // Show edit driver form
    public function edit($id) {
        $this->requirePermission('manage_drivers');

        try {
            $driver = $this->driverModel->getById($id);

            if (!$driver) {
                $this->redirectWithError(SITE_URL . 'drivers', 'Driver not found.');
            }

            $data = [
                'pageTitle' => 'Edit Driver',
                'driver' => $driver,
                'action' => 'edit'
            ];

            $this->render('driver_form', $data);

        } catch (Exception $e) {
            error_log("Driver edit error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'drivers', 'Error loading driver.');
        }
    }

    // Process edit driver
    public function doEdit($id) {
        $this->requirePermission('manage_drivers');
        $this->validateCSRF();

        try {
            $data = $this->getPostData();

            // Validate required fields
            $errors = $this->validateRequired($data, [
                'first_name', 'last_name', 'email', 'phone',
                'license_number', 'license_state', 'license_expiry'
            ]);

            if (!empty($errors)) {
                $_SESSION['form_errors'] = $errors;
                $_SESSION['form_data'] = $data;
                $this->redirect(SITE_URL . "drivers/edit/$id");
            }

            $this->driverModel->update($id, $data);

            logActivity('driver_updated', "Driver updated: {$data['first_name']} {$data['last_name']}");
            $this->redirectWithSuccess(SITE_URL . 'drivers', 'Driver updated successfully.');

        } catch (Exception $e) {
            error_log("Driver update error: " . $e->getMessage());
            $_SESSION['form_errors'] = [$e->getMessage()];
            $_SESSION['form_data'] = $_POST;
            $this->redirect(SITE_URL . "drivers/edit/$id");
        }
    }

    // Delete driver
    public function delete($id) {
        $this->requirePermission('manage_drivers');

        try {
            $driver = $this->driverModel->getById($id);

            if (!$driver) {
                $this->redirectWithError(SITE_URL . 'drivers', 'Driver not found.');
            }

            $this->driverModel->delete($id);

            logActivity('driver_deleted', "Driver deleted: {$driver['first_name']} {$driver['last_name']}");
            $this->redirectWithSuccess(SITE_URL . 'drivers', 'Driver deleted successfully.');

        } catch (Exception $e) {
            error_log("Driver delete error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'drivers', $e->getMessage());
        }
    }

    // View driver details
    public function view($id) {
        try {
            $driver = $this->driverModel->getById($id);

            if (!$driver) {
                $this->redirectWithError(SITE_URL . 'drivers', 'Driver not found.');
            }

            $stats = $this->driverModel->getPerformanceStats($id, 30);

            $data = [
                'pageTitle' => 'Driver Details',
                'driver' => $driver,
                'stats' => $stats
            ];

            $this->render('driver_details', $data);

        } catch (Exception $e) {
            error_log("Driver view error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'drivers', 'Error loading driver details.');
        }
    }

    // Update driver status
    public function updateStatus($id) {
        $this->requirePermission('manage_drivers');

        try {
            $status = sanitize($_POST['status'] ?? '');

            if (empty($status)) {
                $this->jsonError('Status is required.');
            }

            $this->driverModel->updateStatus($id, $status);

            logActivity('driver_status_updated', "Driver status updated to: $status");
            $this->jsonSuccess(['status' => $status], 'Status updated successfully.');

        } catch (Exception $e) {
            error_log("Driver status update error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Update driver location (GPS)
    public function updateLocation($id) {
        try {
            $latitude = floatval($_POST['latitude'] ?? 0);
            $longitude = floatval($_POST['longitude'] ?? 0);

            if ($latitude == 0 || $longitude == 0) {
                $this->jsonError('Invalid coordinates.');
            }

            $this->driverModel->updateLocation($id, $latitude, $longitude);

            $this->jsonSuccess(['latitude' => $latitude, 'longitude' => $longitude], 'Location updated successfully.');

        } catch (Exception $e) {
            error_log("Driver location update error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }
}
?>
