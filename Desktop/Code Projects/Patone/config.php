<?php
/**
 * Roadside Assistance Admin Platform - Configuration
 * Main configuration file for database, site settings, and security
 */

// Error reporting for development (change to 0 for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'roadside_assistance');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'Roadside Assistance Admin');
define('SITE_URL', 'http://localhost:8000/');
define('SITE_EMAIL', 'admin@roadsideassistance.com');

// Timezone
date_default_timezone_set('America/New_York');

// Application Paths (must be defined before other paths that use them)
define('ROOT_PATH', __DIR__ . '/');
define('BACKEND_PATH', ROOT_PATH . 'backend/');
define('FRONTEND_PATH', ROOT_PATH . 'frontend/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('LOGS_PATH', ROOT_PATH . 'logs/');

// File Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');

// Security Configuration
define('ENCRYPTION_KEY', 'your-32-character-encryption-key-here');
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 15 * 60); // 15 minutes

// Include helper functions
require_once INCLUDES_PATH . 'functions.php';

// Include Database class
require_once BACKEND_PATH . 'config/database.php';

// Set headers for security
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Auto-load classes (simple autoloader)
spl_autoload_register(function($className) {
    // Try models directory
    $classFile = BACKEND_PATH . 'models/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
        return;
    }
    
    // Try controllers directory
    $classFile = BACKEND_PATH . 'controllers/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
        return;
    }
    
    // Try config directory
    $classFile = BACKEND_PATH . 'config/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});
?>
