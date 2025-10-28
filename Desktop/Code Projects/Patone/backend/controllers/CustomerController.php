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

    // Export customers to CSV
    public function export() {
        $this->requirePermission('view_customers');

        try {
            $search = sanitize($_GET['search'] ?? '');
            $status = sanitize($_GET['status'] ?? '');
            $state = sanitize($_GET['state'] ?? '');
            $ids = isset($_GET['ids']) ? array_map('intval', explode(',', $_GET['ids'])) : [];

            // Build query
            $whereConditions = [];
            $params = [];

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $whereConditions[] = "c.id IN ($placeholders)";
                $params = array_merge($params, $ids);
            } else {
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
            }

            $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

            // Get customers
            $customers = $this->db->getRows(
                "SELECT c.*, 
                    (SELECT COUNT(*) FROM service_requests WHERE customer_id = c.id) as total_requests
                 FROM customers c
                 $whereClause
                 ORDER BY c.created_at DESC",
                $params
            );

            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=customers_export_' . date('Y-m-d_His') . '.csv');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Open output stream
            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Add CSV headers
            fputcsv($output, [
                'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Emergency Contact',
                'Date of Birth', 'Address', 'Address 2', 'City', 'State', 'ZIP',
                'VIP', 'Status', 'Total Requests', 'Notes', 'Created Date'
            ]);

            // Add customer data
            foreach ($customers as $customer) {
                fputcsv($output, [
                    $customer['id'],
                    $customer['first_name'],
                    $customer['last_name'],
                    $customer['email'],
                    formatPhoneNumber($customer['phone']),
                    $customer['emergency_contact'] ? formatPhoneNumber($customer['emergency_contact']) : '',
                    $customer['date_of_birth'] ?? '',
                    $customer['address'],
                    $customer['address2'] ?? '',
                    $customer['city'],
                    $customer['state'],
                    $customer['zip'],
                    $customer['is_vip'] ? 'Yes' : 'No',
                    $customer['status'],
                    $customer['total_requests'] ?? 0,
                    $customer['notes'] ?? '',
                    $customer['created_at']
                ]);
            }

            fclose($output);
            logActivity('customer_export', 'Exported ' . count($customers) . ' customers', $_SESSION['user_id']);
            exit;

        } catch (Exception $e) {
            error_log("Export customers error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'customers', 'Error exporting customers. Please try again.');
        }
    }

    // Import customers from CSV
    public function import() {
        $this->requirePermission('add_customers');
        $this->validateCSRF();

        try {
            if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
                $this->redirectWithError(SITE_URL . 'customers', 'Please select a valid CSV file.');
            }

            $file = $_FILES['import_file'];
            $skipDuplicates = isset($_POST['skip_duplicates']);

            // Validate file type
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($fileExt !== 'csv') {
                $this->redirectWithError(SITE_URL . 'customers', 'Only CSV files are allowed.');
            }

            // Open CSV file
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                $this->redirectWithError(SITE_URL . 'customers', 'Unable to read CSV file.');
            }

            // Read header row
            $headers = fgetcsv($handle);
            if (!$headers) {
                fclose($handle);
                $this->redirectWithError(SITE_URL . 'customers', 'CSV file is empty or invalid.');
            }

            // Normalize headers (convert to lowercase and replace spaces with underscores)
            $headers = array_map(function($h) {
                return strtolower(trim(str_replace(' ', '_', $h)));
            }, $headers);

            $imported = 0;
            $skipped = 0;
            $errors = 0;

            // Process each row
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) !== count($headers)) {
                    $errors++;
                    continue;
                }

                $data = array_combine($headers, $row);

                // Validate required fields
                if (empty($data['first_name']) || empty($data['last_name']) || 
                    empty($data['email']) || empty($data['phone']) ||
                    empty($data['address']) || empty($data['city']) || 
                    empty($data['state']) || empty($data['zip'])) {
                    $errors++;
                    continue;
                }

                // Check for duplicate email
                if ($skipDuplicates) {
                    $existing = $this->db->getRow("SELECT id FROM customers WHERE email = ?", [$data['email']]);
                    if ($existing) {
                        $skipped++;
                        continue;
                    }
                }

                // Prepare data for insertion
                $customerData = [
                    'first_name' => sanitize($data['first_name']),
                    'last_name' => sanitize($data['last_name']),
                    'email' => sanitize($data['email']),
                    'phone' => preg_replace('/[^0-9]/', '', $data['phone']),
                    'emergency_contact' => isset($data['emergency_contact']) ? preg_replace('/[^0-9]/', '', $data['emergency_contact']) : '',
                    'date_of_birth' => isset($data['date_of_birth']) && !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                    'address' => sanitize($data['address']),
                    'address2' => isset($data['address_2']) ? sanitize($data['address_2']) : '',
                    'city' => sanitize($data['city']),
                    'state' => sanitize($data['state']),
                    'zip' => sanitize($data['zip']),
                    'is_vip' => (isset($data['vip']) && strtolower($data['vip']) === 'yes') ? 1 : 0,
                    'status' => isset($data['status']) ? sanitize($data['status']) : 'active',
                    'notes' => isset($data['notes']) ? sanitize($data['notes']) : ''
                ];

                try {
                    $this->db->insert(
                        "INSERT INTO customers
                         (first_name, last_name, email, phone, emergency_contact, date_of_birth,
                          address, address2, city, state, zip, is_vip, status, notes, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                        [
                            $customerData['first_name'], $customerData['last_name'], $customerData['email'],
                            $customerData['phone'], $customerData['emergency_contact'], $customerData['date_of_birth'],
                            $customerData['address'], $customerData['address2'], $customerData['city'],
                            $customerData['state'], $customerData['zip'], $customerData['is_vip'],
                            $customerData['status'], $customerData['notes']
                        ]
                    );
                    $imported++;
                } catch (Exception $e) {
                    error_log("Import error for customer " . $data['email'] . ": " . $e->getMessage());
                    $errors++;
                }
            }

            fclose($handle);

            logActivity('customer_import', "Imported $imported customers (Skipped: $skipped, Errors: $errors)", $_SESSION['user_id']);

            $message = "Import completed: $imported customers imported";
            if ($skipped > 0) {
                $message .= ", $skipped skipped";
            }
            if ($errors > 0) {
                $message .= ", $errors errors";
            }

            $this->redirectWithSuccess(SITE_URL . 'customers', $message);

        } catch (Exception $e) {
            error_log("Import customers error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'customers', 'Error importing customers. Please try again.');
        }
    }

    // Get customer tags (for segmentation)
    public function getTags() {
        $this->requirePermission('view_customers');

        try {
            $tags = $this->db->getRows(
                "SELECT t.*, COUNT(ct.customer_id) as customer_count
                 FROM customer_tags t
                 LEFT JOIN customer_tag_assignments ct ON t.id = ct.tag_id
                 GROUP BY t.id
                 ORDER BY t.name"
            );

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'tags' => $tags]);
            exit;

        } catch (Exception $e) {
            error_log("Get tags error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}
?>
