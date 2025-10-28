<?php
/**
 * Settings and Configuration Management Tests
 * Tests for Permission model, SettingController, and related functionality
 */

// Load configuration
require_once __DIR__ . '/../config.php';
require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Model.php';
require_once BACKEND_PATH . 'models/Permission.php';
require_once BACKEND_PATH . 'models/Setting.php';
require_once BACKEND_PATH . 'models/User.php';
require_once BACKEND_PATH . 'models/ServiceType.php';

class SettingsTest {
    private $results = [];
    private $passed = 0;
    private $failed = 0;
    private $db;
    private $permissionModel;
    private $settingModel;
    private $userModel;
    private $serviceTypeModel;

    public function run() {
        echo "===========================================\n";
        echo "Settings Management Test Suite\n";
        echo "===========================================\n\n";

        try {
            $this->db = Database::getInstance();
            $this->permissionModel = new Permission();
            $this->settingModel = new Setting();
            $this->userModel = new User();
            $this->serviceTypeModel = new ServiceType();

            // Run tests
            $this->testPermissionModel();
            $this->testSettingModel();
            $this->testRolePermissions();
            $this->testSettingsBackupRestore();
            $this->testUserManagement();
            $this->testServiceTypeManagement();

            // Display results
            $this->displayResults();

        } catch (Exception $e) {
            echo "Fatal error during test setup: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function testPermissionModel() {
        echo "\n--- Testing Permission Model ---\n";

        try {
            // Test: Get all permissions
            $permissions = $this->permissionModel->getAllPermissions();
            if (is_array($permissions)) {
                $this->pass("Get all permissions (found " . count($permissions) . " permissions)");
            } else {
                $this->fail("Get all permissions returned invalid data");
            }

            // Test: Get permissions for a role
            $adminPerms = $this->permissionModel->getKeysForRole('admin');
            if (is_array($adminPerms) && count($adminPerms) > 0) {
                $this->pass("Get permissions for admin role (found " . count($adminPerms) . " permissions)");
            } else {
                $this->fail("Get permissions for admin role failed");
            }

            // Test: Get grouped permissions
            $grouped = $this->permissionModel->getGroupedByCategory();
            if (is_array($grouped) && count($grouped) > 0) {
                $this->pass("Get grouped permissions (found " . count($grouped) . " categories)");
            } else {
                $this->fail("Get grouped permissions failed");
            }

            // Test: Get all roles with permissions
            $rolesWithPerms = $this->permissionModel->getAllRolesWithPermissions();
            if (is_array($rolesWithPerms) && isset($rolesWithPerms['admin'])) {
                $this->pass("Get all roles with permissions");
            } else {
                $this->fail("Get all roles with permissions failed");
            }

        } catch (Exception $e) {
            $this->fail("Permission model test error: " . $e->getMessage());
        }
    }

    private function testSettingModel() {
        echo "\n--- Testing Setting Model ---\n";

        try {
            // Test: Get all settings
            $settings = $this->settingModel->getAll(false);
            if (is_array($settings) && count($settings) > 0) {
                $this->pass("Get all settings (found " . count($settings) . " settings)");
            } else {
                $this->fail("Get all settings returned no data");
            }

            // Test: Get specific setting value
            $siteName = $this->settingModel->getValue('site_name');
            if (!empty($siteName)) {
                $this->pass("Get specific setting value: site_name = '$siteName'");
            } else {
                $this->fail("Get specific setting value failed");
            }

            // Test: Set and get setting value
            $testKey = 'test_setting_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);
            $this->settingModel->setValue($testKey, $testValue, 'string', 'Test setting');
            $retrievedValue = $this->settingModel->getValue($testKey);
            
            if ($retrievedValue === $testValue) {
                $this->pass("Set and retrieve setting value");
                // Clean up
                $this->settingModel->deleteByKey($testKey);
            } else {
                $this->fail("Set and retrieve setting value failed");
            }

            // Test: Boolean setting type casting
            $boolKey = 'test_bool_' . time();
            $this->settingModel->setValue($boolKey, true, 'boolean');
            $boolValue = $this->settingModel->getValue($boolKey);
            
            if ($boolValue === true) {
                $this->pass("Boolean setting type casting");
                $this->settingModel->deleteByKey($boolKey);
            } else {
                $this->fail("Boolean setting type casting failed");
            }

            // Test: Integer setting type casting
            $intKey = 'test_int_' . time();
            $this->settingModel->setValue($intKey, 42, 'integer');
            $intValue = $this->settingModel->getValue($intKey);
            
            if ($intValue === 42) {
                $this->pass("Integer setting type casting");
                $this->settingModel->deleteByKey($intKey);
            } else {
                $this->fail("Integer setting type casting failed");
            }

        } catch (Exception $e) {
            $this->fail("Setting model test error: " . $e->getMessage());
        }
    }

    private function testRolePermissions() {
        echo "\n--- Testing Role Permission Management ---\n";

        try {
            // Test: Check if role has specific permission
            $hasManageSettings = $this->permissionModel->roleHasPermission('admin', 'manage_settings');
            if ($hasManageSettings) {
                $this->pass("Admin role has 'manage_settings' permission");
            } else {
                $this->fail("Admin role should have 'manage_settings' permission");
            }

            // Test: Check permission hierarchy
            $driverPerms = $this->permissionModel->getKeysForRole('driver');
            $dispatcherPerms = $this->permissionModel->getKeysForRole('dispatcher');
            $managerPerms = $this->permissionModel->getKeysForRole('manager');
            
            if (count($driverPerms) < count($dispatcherPerms) && 
                count($dispatcherPerms) < count($managerPerms)) {
                $this->pass("Permission hierarchy is correct (driver < dispatcher < manager)");
            } else {
                $this->fail("Permission hierarchy validation failed");
            }

            // Test: Verify essential permissions exist
            $essentialPerms = ['view_dashboard', 'view_requests', 'manage_settings'];
            $allPerms = $this->permissionModel->getAllPermissions();
            $permKeys = array_column($allPerms, 'permission_key');
            
            $hasAllEssential = true;
            foreach ($essentialPerms as $perm) {
                if (!in_array($perm, $permKeys)) {
                    $hasAllEssential = false;
                    break;
                }
            }
            
            if ($hasAllEssential) {
                $this->pass("All essential permissions exist");
            } else {
                $this->fail("Some essential permissions are missing");
            }

        } catch (Exception $e) {
            $this->fail("Role permission test error: " . $e->getMessage());
        }
    }

    private function testSettingsBackupRestore() {
        echo "\n--- Testing Settings Backup/Restore ---\n";

        try {
            // Test: Get all settings for backup
            $allSettings = $this->settingModel->getAll(false);
            $originalCount = count($allSettings);
            
            // Create test settings for backup
            $testSettings = [
                'backup_test_1' => ['value' => 'test1', 'type' => 'string'],
                'backup_test_2' => ['value' => 123, 'type' => 'integer'],
                'backup_test_3' => ['value' => true, 'type' => 'boolean']
            ];
            
            $this->settingModel->updateMultiple($testSettings);
            
            // Verify settings were created
            $updatedSettings = $this->settingModel->getAll(false);
            if (count($updatedSettings) >= $originalCount + 3) {
                $this->pass("Multiple settings update (backup preparation)");
            } else {
                $this->fail("Multiple settings update failed");
            }
            
            // Clean up test settings
            foreach (array_keys($testSettings) as $key) {
                $this->settingModel->deleteByKey($key);
            }
            
            $finalSettings = $this->settingModel->getAll(false);
            if (count($finalSettings) === $originalCount) {
                $this->pass("Settings cleanup after backup test");
            } else {
                $this->fail("Settings cleanup failed");
            }

        } catch (Exception $e) {
            $this->fail("Backup/restore test error: " . $e->getMessage());
        }
    }

    private function testUserManagement() {
        echo "\n--- Testing User Management ---\n";

        try {
            // Test: Get user stats
            $stats = $this->userModel->getStats();
            if (is_array($stats) && isset($stats['total']) && isset($stats['by_role'])) {
                $this->pass("Get user statistics");
            } else {
                $this->fail("Get user statistics failed");
            }

            // Test: Get all users
            $users = $this->userModel->getAll(10, 0);
            if (is_array($users) && isset($users['users']) && isset($users['total'])) {
                $this->pass("Get all users (found " . $users['total'] . " users)");
            } else {
                $this->fail("Get all users failed");
            }

            // Test: Password hashing
            $testPassword = 'TestPassword123!';
            $hashedPassword = password_hash($testPassword, PASSWORD_BCRYPT);
            $verified = password_verify($testPassword, $hashedPassword);
            
            if ($verified) {
                $this->pass("Password hashing and verification");
            } else {
                $this->fail("Password verification failed");
            }

        } catch (Exception $e) {
            $this->fail("User management test error: " . $e->getMessage());
        }
    }

    private function testServiceTypeManagement() {
        echo "\n--- Testing Service Type Management ---\n";

        try {
            // Test: Get all service types
            $services = $this->serviceTypeModel->all();
            if (is_array($services)) {
                $this->pass("Get all service types (found " . count($services) . " types)");
            } else {
                $this->fail("Get all service types failed");
            }

            // Test: Create and delete service type
            $testService = [
                'name' => 'Test Service ' . time(),
                'description' => 'Test description',
                'base_price' => 99.99,
                'estimated_duration' => 45,
                'is_active' => 1,
                'priority' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $serviceId = $this->serviceTypeModel->create($testService);
            
            if ($serviceId) {
                $this->pass("Create test service type");
                
                // Clean up
                $deleted = $this->serviceTypeModel->delete($serviceId);
                if ($deleted) {
                    $this->pass("Delete test service type");
                } else {
                    $this->fail("Delete test service type failed");
                }
            } else {
                $this->fail("Create test service type failed");
            }

        } catch (Exception $e) {
            $this->fail("Service type management test error: " . $e->getMessage());
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
        echo "\n===========================================\n";
        echo "Test Results Summary\n";
        echo "===========================================\n";
        $total = $this->passed + $this->failed;
        echo "Total Tests: $total\n";
        echo "Passed: " . $this->passed . "\n";
        echo "Failed: " . $this->failed . "\n";
        
        if ($total > 0) {
            echo "Success Rate: " . round(($this->passed / $total) * 100, 2) . "%\n";
        } else {
            echo "Success Rate: N/A (no tests run)\n";
        }
        
        echo "===========================================\n\n";

        if ($this->failed > 0) {
            echo "Some tests failed. Please review the errors above.\n";
            exit(1);
        } else {
            echo "All tests passed successfully!\n";
            exit(0);
        }
    }
}

// Run tests
$test = new SettingsTest();
if (php_sapi_name() === 'cli') {
    $test->run();
} else {
    echo "<html><body><pre>";
    $test->run();
    echo "</pre></body></html>";
}
?>
