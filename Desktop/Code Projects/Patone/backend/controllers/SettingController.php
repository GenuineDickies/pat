<?php
/**
 * Roadside Assistance Admin Platform - Setting Controller
 * Handles system settings and configuration
 */

require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Setting.php';
require_once BACKEND_PATH . 'models/User.php';
require_once BACKEND_PATH . 'models/Permission.php';
require_once BACKEND_PATH . 'models/ServiceType.php';

class SettingController extends Controller {
    private $settingModel;
    private $userModel;
    private $permissionModel;
    private $serviceTypeModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->requirePermission('manage_settings');
        $this->settingModel = new Setting();
        $this->userModel = new User();
        $this->permissionModel = new Permission();
        $this->serviceTypeModel = new ServiceType();
    }

    // Settings page
    public function index() {
        try {
            // Get current tab from query parameter
            $activeTab = $_GET['tab'] ?? 'general';

            $settings = $this->settingModel->getAll(false);
            $users = $this->userModel->getAll(100, 0);
            $roles = $this->permissionModel->getAllRolesWithPermissions();
            $serviceTypes = $this->serviceTypeModel->all();
            $permissionGroups = $this->permissionModel->getGroupedByCategory();

            $data = [
                'pageTitle' => 'System Settings',
                'settings' => $settings,
                'users' => $users['users'],
                'roles' => $roles,
                'serviceTypes' => $serviceTypes,
                'permissionGroups' => $permissionGroups,
                'activeTab' => $activeTab
            ];

            $this->render('settings', $data);

        } catch (Exception $e) {
            error_log("Settings index error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'dashboard', 'Error loading settings.');
        }
    }

    // Update settings
    public function update() {
        $this->validateCSRF();

        try {
            $data = $this->getPostData();

            // Remove CSRF token from data
            unset($data['csrf_token']);

            // Update each setting
            foreach ($data as $key => $value) {
                // Determine type based on value
                $type = 'string';
                if ($value === 'true' || $value === 'false') {
                    $type = 'boolean';
                    $value = $value === 'true';
                } elseif (is_numeric($value) && strpos($value, '.') === false) {
                    $type = 'integer';
                    $value = intval($value);
                }

                $this->settingModel->setValue($key, $value, $type);
            }

            logActivity('settings_updated', 'System settings updated');
            $this->redirectWithSuccess(SITE_URL . 'settings', 'Settings updated successfully.');

        } catch (Exception $e) {
            error_log("Settings update error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings', $e->getMessage());
        }
    }

    // Get setting value (API)
    public function getValue($key) {
        try {
            $value = $this->settingModel->getValue($key);

            if ($value === null) {
                $this->jsonError('Setting not found.', 404);
            }

            $this->jsonSuccess(['key' => $key, 'value' => $value]);

        } catch (Exception $e) {
            error_log("Setting get error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Set setting value (API)
    public function setValue() {
        try {
            $key = sanitize($_POST['key'] ?? '');
            $value = $_POST['value'] ?? '';
            $type = sanitize($_POST['type'] ?? 'string');

            if (empty($key)) {
                $this->jsonError('Setting key is required.');
            }

            $this->settingModel->setValue($key, $value, $type);

            logActivity('setting_updated', "Setting updated: $key");
            $this->jsonSuccess(['key' => $key, 'value' => $value], 'Setting updated successfully.');

        } catch (Exception $e) {
            error_log("Setting set error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // Delete setting (API)
    public function delete($key) {
        try {
            $this->settingModel->deleteByKey($key);

            logActivity('setting_deleted', "Setting deleted: $key");
            $this->jsonSuccess(['key' => $key], 'Setting deleted successfully.');

        } catch (Exception $e) {
            error_log("Setting delete error: " . $e->getMessage());
            $this->jsonError($e->getMessage());
        }
    }

    // ============================================
    // User Management Methods
    // ============================================

    // Add new user
    public function addUser() {
        $this->validateCSRF();

        try {
            $data = $this->getPostData();

            // Validate required fields
            $errors = $this->validateRequired($data, ['username', 'email', 'password', 'first_name', 'last_name', 'role']);
            
            if (!empty($errors)) {
                throw new Exception(implode(', ', $errors));
            }

            // Validate email
            $emailError = $this->validateEmail($data['email']);
            if ($emailError) {
                throw new Exception($emailError);
            }

            // Validate password strength
            if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
                throw new Exception('Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.');
            }

            // Create user
            $userId = $this->userModel->create($data);

            logActivity('user_created', "User created: {$data['username']} (ID: $userId)");
            $this->redirectWithSuccess(SITE_URL . 'settings?tab=users', 'User added successfully.');

        } catch (Exception $e) {
            error_log("User add error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings?tab=users', $e->getMessage());
        }
    }

    // Edit user
    public function editUser() {
        $this->validateCSRF();

        try {
            $userId = (int)($_POST['user_id'] ?? 0);
            
            if ($userId <= 0) {
                throw new Exception('Invalid user ID.');
            }

            // Prevent editing self if removing admin role
            if ($userId == $_SESSION['user_id'] && isset($_POST['role']) && $_POST['role'] !== 'admin') {
                throw new Exception('You cannot change your own admin role.');
            }

            $data = [];
            $allowedFields = ['username', 'email', 'first_name', 'last_name', 'role', 'status'];

            foreach ($allowedFields as $field) {
                if (isset($_POST[$field]) && !empty($_POST[$field])) {
                    $data[$field] = sanitize($_POST[$field]);
                }
            }

            // Validate email if provided
            if (isset($data['email'])) {
                $emailError = $this->validateEmail($data['email']);
                if ($emailError) {
                    throw new Exception($emailError);
                }
            }

            $this->userModel->update($userId, $data);

            logActivity('user_updated', "User updated: ID $userId");
            $this->redirectWithSuccess(SITE_URL . 'settings?tab=users', 'User updated successfully.');

        } catch (Exception $e) {
            error_log("User edit error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings?tab=users', $e->getMessage());
        }
    }

    // Delete user
    public function deleteUser($userId = null) {
        $this->requirePermission('manage_settings');

        try {
            $userId = $userId ?? (int)($_GET['id'] ?? 0);

            if ($userId <= 0) {
                throw new Exception('Invalid user ID.');
            }

            // Prevent self-deletion
            if ($userId == $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account.');
            }

            $user = $this->userModel->find($userId);
            if (!$user) {
                throw new Exception('User not found.');
            }

            $this->userModel->delete($userId);

            logActivity('user_deleted', "User deleted: {$user['username']} (ID: $userId)");
            $this->redirectWithSuccess(SITE_URL . 'settings?tab=users', 'User deleted successfully.');

        } catch (Exception $e) {
            error_log("User delete error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings?tab=users', $e->getMessage());
        }
    }

    // Change user password
    public function changePassword() {
        $this->validateCSRF();

        try {
            $userId = (int)($_POST['user_id'] ?? 0);
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($userId <= 0) {
                throw new Exception('Invalid user ID.');
            }

            if (empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('Password fields cannot be empty.');
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception('Passwords do not match.');
            }

            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                throw new Exception('Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.');
            }

            $this->userModel->updatePassword($userId, $newPassword);

            logActivity('password_changed', "Password changed for user ID: $userId");
            $this->redirectWithSuccess(SITE_URL . 'settings?tab=users', 'Password changed successfully.');

        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings?tab=users', $e->getMessage());
        }
    }

    // ============================================
    // Role and Permission Management
    // ============================================

    // Update role permissions
    public function updateRolePermissions() {
        $this->validateCSRF();

        try {
            $role = sanitize($_POST['role'] ?? '');
            $permissionIds = $_POST['permissions'] ?? [];

            if (empty($role)) {
                throw new Exception('Role is required.');
            }

            $validRoles = ['admin', 'manager', 'dispatcher', 'driver'];
            if (!in_array($role, $validRoles)) {
                throw new Exception('Invalid role.');
            }

            // Don't allow modifying admin role
            if ($role === 'admin') {
                throw new Exception('Admin role permissions cannot be modified.');
            }

            $this->permissionModel->syncRolePermissions($role, $permissionIds);

            logActivity('role_permissions_updated', "Permissions updated for role: $role");
            $this->redirectWithSuccess(SITE_URL . 'settings?tab=roles', 'Role permissions updated successfully.');

        } catch (Exception $e) {
            error_log("Role permission update error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings?tab=roles', $e->getMessage());
        }
    }

    // ============================================
    // Service Type Management
    // ============================================

    // Add service type
    public function addServiceType() {
        $this->validateCSRF();

        try {
            $data = $this->getPostData();

            $errors = $this->validateRequired($data, ['name', 'base_price', 'estimated_duration']);
            if (!empty($errors)) {
                throw new Exception(implode(', ', $errors));
            }

            $serviceData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'base_price' => (float)$data['base_price'],
                'estimated_duration' => (int)$data['estimated_duration'],
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'priority' => (int)($data['priority'] ?? 0),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $serviceId = $this->serviceTypeModel->create($serviceData);

            logActivity('service_type_added', "Service type added: {$data['name']} (ID: $serviceId)");
            $this->redirectWithSuccess(SITE_URL . 'settings?tab=services', 'Service type added successfully.');

        } catch (Exception $e) {
            error_log("Service type add error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings?tab=services', $e->getMessage());
        }
    }

    // Edit service type
    public function editServiceType() {
        $this->validateCSRF();

        try {
            $serviceId = (int)($_POST['service_id'] ?? 0);
            
            if ($serviceId <= 0) {
                throw new Exception('Invalid service type ID.');
            }

            $data = $this->getPostData();

            $serviceData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'base_price' => (float)$data['base_price'],
                'estimated_duration' => (int)$data['estimated_duration'],
                'is_active' => isset($data['is_active']) ? 1 : 0,
                'priority' => (int)($data['priority'] ?? 0),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->serviceTypeModel->update($serviceId, $serviceData);

            logActivity('service_type_updated', "Service type updated: ID $serviceId");
            $this->redirectWithSuccess(SITE_URL . 'settings?tab=services', 'Service type updated successfully.');

        } catch (Exception $e) {
            error_log("Service type edit error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings?tab=services', $e->getMessage());
        }
    }

    // Delete service type
    public function deleteServiceType($serviceId = null) {
        $this->requirePermission('manage_settings');

        try {
            $serviceId = $serviceId ?? (int)($_GET['id'] ?? 0);

            if ($serviceId <= 0) {
                throw new Exception('Invalid service type ID.');
            }

            $this->serviceTypeModel->delete($serviceId);

            logActivity('service_type_deleted', "Service type deleted: ID $serviceId");
            $this->redirectWithSuccess(SITE_URL . 'settings?tab=services', 'Service type deleted successfully.');

        } catch (Exception $e) {
            error_log("Service type delete error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings?tab=services', $e->getMessage());
        }
    }

    // ============================================
    // Settings Backup and Restore
    // ============================================

    // Export settings as JSON
    public function exportSettings() {
        try {
            $settings = $this->settingModel->getAll(false);

            // Prepare export data
            $exportData = [
                'exported_at' => date('Y-m-d H:i:s'),
                'exported_by' => $_SESSION['user_id'] ?? null,
                'settings' => $settings
            ];

            $json = json_encode($exportData, JSON_PRETTY_PRINT);

            // Send as download
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="settings_backup_' . date('Y-m-d_His') . '.json"');
            echo $json;
            exit;

        } catch (Exception $e) {
            error_log("Settings export error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings', 'Error exporting settings.');
        }
    }

    // Import settings from JSON
    public function importSettings() {
        $this->validateCSRF();

        try {
            if (!isset($_FILES['settings_file'])) {
                throw new Exception('No file uploaded.');
            }

            $file = $_FILES['settings_file'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('File upload error.');
            }

            $content = file_get_contents($file['tmp_name']);
            $importData = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON file.');
            }

            if (!isset($importData['settings'])) {
                throw new Exception('Invalid settings file format.');
            }

            // Import settings
            $this->settingModel->updateMultiple($importData['settings']);

            logActivity('settings_imported', 'Settings imported from backup file');
            $this->redirectWithSuccess(SITE_URL . 'settings', 'Settings imported successfully.');

        } catch (Exception $e) {
            error_log("Settings import error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'settings', $e->getMessage());
        }
    }
}
?>
