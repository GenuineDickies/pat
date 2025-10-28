<?php
/**
 * Roadside Assistance Admin Platform - Base Controller
 * Provides common functionality for all controllers
 */

class Controller {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Render view with layout
    protected function render($view, $data = []) {
        // Extract data for use in view
        extract($data);

        // If rendering layout itself, just include it directly
        if ($view === 'layout') {
            include FRONTEND_PATH . 'pages/layout.php';
            return;
        }

        // For other views, capture content then include layout
        ob_start();
        include FRONTEND_PATH . 'pages/' . $view . '.php';
        $content = ob_get_clean();

        // Include layout
        include FRONTEND_PATH . 'pages/layout.php';
    }

    // Render partial view (without layout)
    protected function renderPartial($view, $data = []) {
        extract($data);
        include FRONTEND_PATH . 'pages/' . $view . '.php';
    }

    // Redirect to URL
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }

    // Redirect with success message
    protected function redirectWithSuccess($url, $message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = 'success';
        $this->redirect($url);
    }

    // Redirect with error message
    protected function redirectWithError($url, $message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = 'error';
        $this->redirect($url);
    }

    // Check if user is logged in
    protected function requireLogin() {
        if (!isLoggedIn()) {
            $this->redirect(SITE_URL . 'login');
        }
        
        // Check session timeout
        $security = SecurityMiddleware::getInstance();
        if (!$security->checkSessionTimeout(SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
            $this->redirectWithError(SITE_URL . 'login', 'Your session has expired. Please login again.');
        }
    }

    // Check if user has permission
    protected function requirePermission($permission) {
        if (!hasPermission($permission)) {
            $this->redirectWithError(SITE_URL . 'dashboard', 'You do not have permission to access this page.');
        }
    }

    // Validate CSRF token
    protected function validateCSRF() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->redirectWithError($_SERVER['HTTP_REFERER'] ?? SITE_URL, 'Invalid request token.');
        }
    }

    // Generate CSRF token
    protected function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = generateRandomString(32);
        }
        return $_SESSION['csrf_token'];
    }

    // Get POST data with sanitization
    protected function getPostData() {
        return array_map('sanitize', $_POST);
    }

    // Get GET data with sanitization
    protected function getGetData() {
        return array_map('sanitize', $_GET);
    }

    // Upload file and return result
    protected function uploadFile($fileInputName, $destination = '') {
        if (!isset($_FILES[$fileInputName])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }

        // Use SecurityMiddleware for enhanced validation
        $security = SecurityMiddleware::getInstance();
        $validation = $security->validateFileUpload($_FILES[$fileInputName]);
        
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        $result = uploadFile($_FILES[$fileInputName], $destination);

        if ($result['success']) {
            logActivity('file_upload', 'File uploaded: ' . $result['file_name']);
        }

        return $result;
    }

    // Send JSON response
    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // Send error JSON response
    protected function jsonError($message, $statusCode = 400) {
        $this->jsonResponse(['success' => false, 'error' => $message], $statusCode);
    }

    // Send success JSON response
    protected function jsonSuccess($data = null, $message = 'Success') {
        $response = ['success' => true, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        $this->jsonResponse($response);
    }

    // Validate required fields
    protected function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        return $errors;
    }

    // Validate email format
    protected function validateEmail($email) {
        if (!isValidEmail($email)) {
            return 'Please enter a valid email address';
        }
        return null;
    }

    // Validate phone number
    protected function validatePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) !== 10) {
            return 'Please enter a valid 10-digit phone number';
        }
        return null;
    }

    // Get pagination parameters
    protected function getPaginationParams($defaultLimit = 25) {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(100, (int)($_GET['limit'] ?? $defaultLimit)));
        $offset = ($page - 1) * $limit;

        return ['page' => $page, 'limit' => $limit, 'offset' => $offset];
    }

    // Generate pagination HTML
    protected function generatePagination($currentPage, $totalPages, $baseUrl) {
        return generatePagination($currentPage, $totalPages, $baseUrl);
    }
}
?>
