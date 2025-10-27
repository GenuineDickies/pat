<?php
/**
 * Roadside Assistance Admin Platform - Setting Controller
 * Handles system settings and configuration
 */

require_once BACKEND_PATH . 'config/database.php';
require_once BACKEND_PATH . 'models/Setting.php';

class SettingController extends Controller {
    private $settingModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->requirePermission('manage_settings');
        $this->settingModel = new Setting();
    }

    // Settings page
    public function index() {
        try {
            $settings = $this->settingModel->getAll(false);

            $data = [
                'pageTitle' => 'System Settings',
                'settings' => $settings
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
}
?>
