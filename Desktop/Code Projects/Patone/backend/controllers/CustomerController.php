<?php
/**
 * Roadside Assistance Admin Platform - Customer Controller
 * Handles customer management operations
 */

class CustomerController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    // List customers
    public function index() {
        try {
            $pagination = $this->getPaginationParams();
            $search = sanitize($_GET['search'] ?? '');
            $status = sanitize($_GET['status'] ?? '');
            $state = sanitize($_GET['state'] ?? '');

            // Build query
            $whereConditions = [];
            $params = [];

            if (!empty($search)) {
                $whereConditions[] = "(CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($status)) {
                $whereConditions[] = "c.status = ?";
                $params[] = $status;
            }

            if (!empty($state)) {
                $whereConditions[] = "c.state = ?";
                $params[] = $state;
            }

            $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

            // Get total count
            $totalCustomers = (int)$this->db->getValue(
                "SELECT COUNT(*) FROM customers c $whereClause",
                $params
            );

            // Get customers
            $customers = $this->db->getRows(
                "SELECT
                    c.*,
                    (SELECT COUNT(*) FROM service_requests WHERE customer_id = c.id) as total_requests,
                    (SELECT MAX(created_at) FROM service_requests WHERE customer_id = c.id AND status = 'completed') as last_service_date
                 FROM customers c
                 $whereClause
                 ORDER BY c.created_at DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$pagination['limit'], $pagination['offset']])
            );

            $totalPages = ceil($totalCustomers / $pagination['limit']);

            $data = [
                'pageTitle' => 'Customer Management',
                'customers' => $customers,
                'totalCustomers' => $totalCustomers,
                'currentPage' => $pagination['page'],
                'totalPages' => $totalPages,
                'content' => $this->renderPartial('customers')
            ];

            $this->render('layout', $data);

        } catch (Exception $e) {
            error_log("Customer index error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'customers', 'Error loading customers.');
        }
    }

    // Show add customer form
    public function add() {
        $this->requirePermission('add_customers');

        $data = [
            'pageTitle' => 'Add Customer',
            'content' => $this->renderPartial('customer_form')
        ];

        $this->render('layout', $data);
    }

    // Process add customer
    public function doAdd() {
        $this->requirePermission('add_customers');
        $this->validateCSRF();

        try {
            $postData = $this->getPostData();

            // Validate required fields
            $errors = $this->validateRequired($postData, ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip']);
            if (!empty($errors)) {
                $this->redirectWithError(SITE_URL . 'customers/add', implode('<br>', $errors));
            }

            // Validate email format
            $emailError = $this->validateEmail($postData['email']);
            if ($emailError) {
                $this->redirectWithError(SITE_URL . 'customers/add', $emailError);
            }

            // Validate phone format
            $phoneError = $this->validatePhone($postData['phone']);
            if ($phoneError) {
                $this->redirectWithError(SITE_URL . 'customers/add', $phoneError);
            }

            // Check if email already exists
            $existingCustomer = $this->db->getRow("SELECT id FROM customers WHERE email = ?", [$postData['email']]);
            if ($existingCustomer) {
                $this->redirectWithError(SITE_URL . 'customers/add', 'A customer with this email address already exists.');
            }

            // Clean phone numbers
            $postData['phone'] = preg_replace('/[^0-9]/', '', $postData['phone']);
            $postData['emergency_contact'] = preg_replace('/[^0-9]/', '', $postData['emergency_contact'] ?? '');

            // Set default status
            $postData['status'] = $postData['status'] ?? 'active';

            // Insert customer
            $customerId = $this->db->insert(
                "INSERT INTO customers
                 (first_name, last_name, email, phone, emergency_contact, date_of_birth,
                  address, address2, city, state, zip, is_vip, status, notes, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $postData['first_name'], $postData['last_name'], $postData['email'],
                    $postData['phone'], $postData['emergency_contact'], $postData['date_of_birth'],
                    $postData['address'], $postData['address2'], $postData['city'],
                    $postData['state'], $postData['zip'], isset($postData['is_vip']) ? 1 : 0,
                    $postData['status'], $postData['notes']
                ]
            );

            // Insert vehicles if provided
            if (isset($postData['vehicles']) && is_array($postData['vehicles'])) {
                foreach ($postData['vehicles'] as $vehicle) {
                    if (!empty($vehicle['make']) || !empty($vehicle['model'])) {
                        $this->db->insert(
                            "INSERT INTO customer_vehicles (customer_id, make, model, year, color, license_plate, created_at)
                             VALUES (?, ?, ?, ?, ?, ?, NOW())",
                            [
                                $customerId, $vehicle['make'], $vehicle['model'], $vehicle['year'],
                                $vehicle['color'], $vehicle['license_plate']
                            ]
                        );
                    }
                }
            }

            logActivity('customer_add', 'Added new customer: ' . $postData['first_name'] . ' ' . $postData['last_name'], $_SESSION['user_id']);

            $this->redirectWithSuccess(SITE_URL . 'customers', 'Customer added successfully.');

        } catch (Exception $e) {
            error_log("Add customer error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'customers/add', 'Error adding customer. Please try again.');
        }
    }

    // Show edit customer form
    public function edit() {
        $this->requirePermission('edit_customers');

        $customerId = (int)$_GET['id'];

        try {
            $customer = $this->db->getRow("SELECT * FROM customers WHERE id = ?", [$customerId]);

            if (!$customer) {
                $this->redirectWithError(SITE_URL . 'customers', 'Customer not found.');
            }

            // Get customer vehicles
            $vehicles = $this->db->getRows("SELECT * FROM customer_vehicles WHERE customer_id = ? ORDER BY id", [$customerId]);
            $customer['vehicles'] = $vehicles;

            $data = [
                'pageTitle' => 'Edit Customer',
                'customer' => $customer,
                'content' => $this->renderPartial('customer_form')
            ];

            $this->render('layout', $data);

        } catch (Exception $e) {
            error_log("Edit customer error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'customers', 'Error loading customer data.');
        }
    }

    // Process edit customer
    public function doEdit() {
        $this->requirePermission('edit_customers');
        $this->validateCSRF();

        $customerId = (int)$_GET['id'];

        try {
            $postData = $this->getPostData();

            // Validate required fields
            $errors = $this->validateRequired($postData, ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip']);
            if (!empty($errors)) {
                $this->redirectWithError(SITE_URL . 'customers/edit/' . $customerId, implode('<br>', $errors));
            }

            // Validate email format
            $emailError = $this->validateEmail($postData['email']);
            if ($emailError) {
                $this->redirectWithError(SITE_URL . 'customers/edit/' . $customerId, $emailError);
            }

            // Validate phone format
            $phoneError = $this->validatePhone($postData['phone']);
            if ($phoneError) {
                $this->redirectWithError(SITE_URL . 'customers/edit/' . $customerId, $phoneError);
            }

            // Check if email already exists (excluding current customer)
            $existingCustomer = $this->db->getRow("SELECT id FROM customers WHERE email = ? AND id != ?", [$postData['email'], $customerId]);
            if ($existingCustomer) {
                $this->redirectWithError(SITE_URL . 'customers/edit/' . $customerId, 'A customer with this email address already exists.');
            }

            // Clean phone numbers
            $postData['phone'] = preg_replace('/[^0-9]/', '', $postData['phone']);
            $postData['emergency_contact'] = preg_replace('/[^0-9]/', '', $postData['emergency_contact'] ?? '');

            // Update customer
            $this->db->update(
                "UPDATE customers SET
                 first_name = ?, last_name = ?, email = ?, phone = ?, emergency_contact = ?,
                 date_of_birth = ?, address = ?, address2 = ?, city = ?, state = ?, zip = ?,
                 is_vip = ?, status = ?, notes = ?, updated_at = NOW()
                 WHERE id = ?",
                [
                    $postData['first_name'], $postData['last_name'], $postData['email'],
                    $postData['phone'], $postData['emergency_contact'], $postData['date_of_birth'],
                    $postData['address'], $postData['address2'], $postData['city'],
                    $postData['state'], $postData['zip'], isset($postData['is_vip']) ? 1 : 0,
                    $postData['status'], $postData['notes'], $customerId
                ]
            );

            // Update vehicles - delete existing and insert new ones
            $this->db->query("DELETE FROM customer_vehicles WHERE customer_id = ?", [$customerId]);

            if (isset($postData['vehicles']) && is_array($postData['vehicles'])) {
                foreach ($postData['vehicles'] as $vehicle) {
                    if (!empty($vehicle['make']) || !empty($vehicle['model'])) {
                        $this->db->insert(
                            "INSERT INTO customer_vehicles (customer_id, make, model, year, color, license_plate, created_at)
                             VALUES (?, ?, ?, ?, ?, ?, NOW())",
                            [
                                $customerId, $vehicle['make'], $vehicle['model'], $vehicle['year'],
                                $vehicle['color'], $vehicle['license_plate']
                            ]
                        );
                    }
                }
            }

            logActivity('customer_edit', 'Updated customer: ' . $postData['first_name'] . ' ' . $postData['last_name'], $_SESSION['user_id']);

            $this->redirectWithSuccess(SITE_URL . 'customers', 'Customer updated successfully.');

        } catch (Exception $e) {
            error_log("Edit customer error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'customers/edit/' . $customerId, 'Error updating customer. Please try again.');
        }
    }

    // Delete customer
    public function delete() {
        $this->requirePermission('delete_customers');

        $customerId = (int)$_GET['id'];

        try {
            // Check if customer has active requests
            $activeRequests = $this->db->getValue(
                "SELECT COUNT(*) FROM service_requests WHERE customer_id = ? AND status IN ('pending', 'assigned', 'in_progress')",
                [$customerId]
            );

            if ($activeRequests > 0) {
                $this->redirectWithError(SITE_URL . 'customers', 'Cannot delete customer with active service requests.');
            }

            // Get customer info for logging
            $customer = $this->db->getRow("SELECT CONCAT(first_name, ' ', last_name) as name FROM customers WHERE id = ?", [$customerId]);

            // Delete customer (cascade will handle related records)
            $this->db->query("DELETE FROM customers WHERE id = ?", [$customerId]);

            logActivity('customer_delete', 'Deleted customer: ' . ($customer['name'] ?? 'Unknown'), $_SESSION['user_id']);

            $this->redirectWithSuccess(SITE_URL . 'customers', 'Customer deleted successfully.');

        } catch (Exception $e) {
            error_log("Delete customer error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'customers', 'Error deleting customer. Please try again.');
        }
    }

    // View customer details
    public function view() {
        $this->requirePermission('view_customers');

        $customerId = (int)$_GET['id'];

        try {
            $customer = $this->db->getRow("SELECT * FROM customers WHERE id = ?", [$customerId]);

            if (!$customer) {
                $this->redirectWithError(SITE_URL . 'customers', 'Customer not found.');
            }

            // Get customer vehicles
            $vehicles = $this->db->getRows("SELECT * FROM customer_vehicles WHERE customer_id = ? ORDER BY id", [$customerId]);

            // Get service history
            $serviceHistory = $this->db->getRows(
                "SELECT * FROM service_requests WHERE customer_id = ? ORDER BY created_at DESC LIMIT 20",
                [$customerId]
            );

            $data = [
                'pageTitle' => 'Customer Details',
                'customer' => $customer,
                'vehicles' => $vehicles,
                'serviceHistory' => $serviceHistory,
                'content' => $this->renderPartial('customer_details')
            ];

            $this->render('layout', $data);

        } catch (Exception $e) {
            error_log("View customer error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'customers', 'Error loading customer details.');
        }
    }
}
?>
