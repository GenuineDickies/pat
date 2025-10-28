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

    // ============================================
    // Certification Management
    // ============================================

    // View driver certifications
    public function certifications($id) {
        try {
            $driver = $this->driverModel->getById($id);
            if (!$driver) {
                $this->redirectWithError(SITE_URL . 'drivers', 'Driver not found.');
            }

            $certifications = $this->driverModel->getCertifications($id);

            $data = [
                'pageTitle' => 'Driver Certifications',
                'driver' => $driver,
                'certifications' => $certifications
            ];

            $this->render('driver_certifications', $data);

        } catch (Exception $e) {
            error_log("Driver certifications error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'drivers', 'Error loading certifications.');
        }
    }

    // Add certification
    public function addCertification($id) {
        $this->requirePermission('manage_drivers');
        $this->validateCSRF();

        try {
            $data = $this->getPostData();
            $data['driver_id'] = $id;

            $this->driverModel->addCertification($id, $data);

            logActivity('certification_added', "Certification added for driver ID: $id");
            $this->redirectWithSuccess(SITE_URL . "drivers/certifications/$id", 'Certification added successfully.');

        } catch (Exception $e) {
            error_log("Add certification error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . "drivers/certifications/$id", $e->getMessage());
        }
    }

    // Delete certification
    public function deleteCertification($driverId, $certId) {
        $this->requirePermission('manage_drivers');

        try {
            $this->driverModel->deleteCertification($certId);

            logActivity('certification_deleted', "Certification deleted: ID $certId");
            $this->redirectWithSuccess(SITE_URL . "drivers/certifications/$driverId", 'Certification deleted successfully.');

        } catch (Exception $e) {
            error_log("Delete certification error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . "drivers/certifications/$driverId", $e->getMessage());
        }
    }

    // ============================================
    // Document Management
    // ============================================

    // View driver documents
    public function documents($id) {
        try {
            $driver = $this->driverModel->getById($id);
            if (!$driver) {
                $this->redirectWithError(SITE_URL . 'drivers', 'Driver not found.');
            }

            $documents = $this->driverModel->getDocuments($id);

            $data = [
                'pageTitle' => 'Driver Documents',
                'driver' => $driver,
                'documents' => $documents
            ];

            $this->render('driver_documents', $data);

        } catch (Exception $e) {
            error_log("Driver documents error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'drivers', 'Error loading documents.');
        }
    }

    // ============================================
    // Availability Scheduling
    // ============================================

    // View and manage driver availability schedule
    public function schedule($id) {
        $this->requirePermission('manage_drivers');

        try {
            $driver = $this->driverModel->getById($id);
            if (!$driver) {
                $this->redirectWithError(SITE_URL . 'drivers', 'Driver not found.');
            }

            $schedule = $this->driverModel->getAvailabilitySchedule($id);

            $data = [
                'pageTitle' => 'Driver Schedule',
                'driver' => $driver,
                'schedule' => $schedule
            ];

            $this->render('driver_schedule', $data);

        } catch (Exception $e) {
            error_log("Driver schedule error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'drivers', 'Error loading schedule.');
        }
    }

    // Save availability schedule
    public function saveSchedule($id) {
        $this->requirePermission('manage_drivers');
        $this->validateCSRF();

        try {
            $schedules = $_POST['schedules'] ?? [];

            foreach ($schedules as $schedule) {
                $this->driverModel->setAvailabilitySchedule(
                    $id,
                    $schedule['day_of_week'],
                    $schedule['start_time'],
                    $schedule['end_time'],
                    isset($schedule['is_available']),
                    $schedule['notes'] ?? null
                );
            }

            logActivity('schedule_updated', "Schedule updated for driver ID: $id");
            $this->redirectWithSuccess(SITE_URL . "drivers/schedule/$id", 'Schedule updated successfully.');

        } catch (Exception $e) {
            error_log("Save schedule error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . "drivers/schedule/$id", $e->getMessage());
        }
    }

    // ============================================
    // Workload Balancing
    // ============================================

    // View driver workload
    public function workload($id = null) {
        try {
            if ($id) {
                // Individual driver workload
                $driver = $this->driverModel->getById($id);
                if (!$driver) {
                    $this->redirectWithError(SITE_URL . 'drivers', 'Driver not found.');
                }

                $workload = $this->driverModel->getWorkload($id);

                $data = [
                    'pageTitle' => 'Driver Workload',
                    'driver' => $driver,
                    'workload' => $workload
                ];

                $this->render('driver_workload', $data);
            } else {
                // All drivers workload distribution
                $distribution = $this->driverModel->getWorkloadDistribution();

                $data = [
                    'pageTitle' => 'Workload Distribution',
                    'distribution' => $distribution
                ];

                $this->render('workload_distribution', $data);
            }

        } catch (Exception $e) {
            error_log("Driver workload error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'drivers', 'Error loading workload data.');
        }
    }

    // Update max workload
    public function updateMaxWorkload($id) {
        $this->requirePermission('manage_drivers');

        try {
            $maxWorkload = intval($_POST['max_workload'] ?? 3);

            if ($maxWorkload < 1) {
                $this->jsonError('Max workload must be at least 1.');
            }

            $this->driverModel->setMaxWorkload($id, $maxWorkload);

            logActivity('max_workload_updated', "Max workload updated for driver ID: $id to $maxWorkload");
            $this->jsonSuccess(['max_workload' => $maxWorkload], 'Max workload updated successfully.');

        } catch (Exception $e) {
            error_log("Update max workload error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Dashboard with comprehensive metrics
    public function dashboard($id) {
        try {
            $driver = $this->driverModel->getById($id);
            if (!$driver) {
                $this->redirectWithError(SITE_URL . 'drivers', 'Driver not found.');
            }

            // Get all relevant data
            $stats = $this->driverModel->getPerformanceStats($id, 30);
            $workload = $this->driverModel->getWorkload($id);
            $certifications = $this->driverModel->getCertifications($id);
            $documents = $this->driverModel->getDocuments($id);
            $schedule = $this->driverModel->getAvailabilitySchedule($id);
            $isScheduledAvailable = $this->driverModel->isScheduledAvailable($id);

            // Get expiring certifications
            $expiringCerts = array_filter($certifications, function($cert) {
                if (!$cert['expiry_date']) return false;
                $expiry = new DateTime($cert['expiry_date']);
                $now = new DateTime();
                $diff = $now->diff($expiry)->days;
                return $diff <= 30 && $expiry >= $now;
            });

            $data = [
                'pageTitle' => 'Driver Dashboard',
                'driver' => $driver,
                'stats' => $stats,
                'workload' => $workload,
                'certifications' => $certifications,
                'expiring_certifications' => $expiringCerts,
                'documents' => $documents,
                'schedule' => $schedule,
                'is_scheduled_available' => $isScheduledAvailable
            ];

            $this->render('driver_dashboard', $data);

        } catch (Exception $e) {
            error_log("Driver dashboard error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'drivers', 'Error loading dashboard.');
        }
    }
}
?>
