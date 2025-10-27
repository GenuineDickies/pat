<?php
/**
 * Roadside Assistance Admin Platform - Helper Functions
 * Common utility functions used throughout the application
 */

// Sanitize input data
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email address
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Generate random string
function generateRandomString($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Hash password securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Format phone number
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 10) {
        return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    }
    return $phone;
}

// Format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

// Format date for display
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Format date and time for display
function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

// Calculate distance between two coordinates (Haversine formula)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 3959; // miles

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;

    return $distance;
}

// Generate slug from string
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has permission
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    return isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions']);
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

// Get flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Log activity
function logActivity($action, $description, $userId = null) {
    $db = Database::getInstance();
    $userId = $userId ?: ($_SESSION['user_id'] ?? null);

    $db->insert("INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())",
        [$userId, $action, $description, $_SERVER['REMOTE_ADDR']]);
}

// Validate file upload
function validateFileUpload($file, $maxSize = MAX_FILE_SIZE, $allowedTypes = ALLOWED_EXTENSIONS) {
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error occurred.';
        return $errors;
    }

    if ($file['size'] > $maxSize) {
        $errors[] = 'File size exceeds maximum allowed size.';
    }

    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) {
        $errors[] = 'File type not allowed.';
    }

    return $errors;
}

// Upload file
function uploadFile($file, $destination) {
    $errors = validateFileUpload($file);

    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }

    $fileName = generateRandomString() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $fullPath = UPLOAD_PATH . $destination . '/' . $fileName;

    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        return ['success' => true, 'file_name' => $fileName, 'full_path' => $fullPath];
    } else {
        return ['success' => false, 'errors' => ['Failed to move uploaded file.']];
    }
}

// Get request status badge class
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'pending':
            return 'badge-warning';
        case 'assigned':
            return 'badge-info';
        case 'in_progress':
            return 'badge-primary';
        case 'completed':
            return 'badge-success';
        case 'cancelled':
            return 'badge-secondary';
        default:
            return 'badge-light';
    }
}

// Get priority badge class
function getPriorityBadgeClass($priority) {
    switch (strtolower($priority)) {
        case 'low':
            return 'badge-light';
        case 'medium':
            return 'badge-warning';
        case 'high':
            return 'badge-danger';
        case 'urgent':
            return 'badge-dark';
        default:
            return 'badge-light';
    }
}

// Generate pagination links
function generatePagination($currentPage, $totalPages, $baseUrl) {
    $pagination = '<nav><ul class="pagination justify-content-center">';

    // Previous button
    if ($currentPage > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage - 1) . '">&laquo;</a></li>';
    }

    // Page numbers
    for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
        if ($i == $currentPage) {
            $pagination .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
        }
    }

    // Next button
    if ($currentPage < $totalPages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($currentPage + 1) . '">&raquo;</a></li>';
    }

    $pagination .= '</ul></nav>';
    return $pagination;
}

// Format phone for display (alias for consistency in views)
// This alias exists for consistency with other format* functions used in views
function formatPhone($phone) {
    return formatPhoneNumber($phone);
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateRandomString(32);
    }
    return $_SESSION['csrf_token'];
}

// Truncate text with ellipsis
function truncateText($text, $maxLength = 100) {
    if (strlen($text) <= $maxLength) {
        return $text;
    }
    return substr($text, 0, $maxLength - 3) . '...';
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user'
    ];
}

// Check if user has any of the given permissions
function hasAnyPermission($permissions) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!is_array($permissions)) {
        $permissions = [$permissions];
    }
    
    foreach ($permissions as $permission) {
        if (hasPermission($permission)) {
            return true;
        }
    }
    
    return false;
}

// Get setting value helper with caching
function getSetting($key, $default = null) {
    static $settingModel = null;
    static $cache = [];
    
    // Check cache first
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    
    try {
        // Initialize model once
        if ($settingModel === null) {
            require_once BACKEND_PATH . 'models/Setting.php';
            $settingModel = new Setting();
        }
        
        $value = $settingModel->getValue($key, $default);
        $cache[$key] = $value; // Cache the result
        return $value;
    } catch (Exception $e) {
        error_log("Error getting setting $key: " . $e->getMessage());
        return $default;
    }
}
?>
