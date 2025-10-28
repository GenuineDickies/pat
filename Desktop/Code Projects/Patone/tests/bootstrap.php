<?php
/**
 * PHPUnit Bootstrap File
 * Sets up the testing environment
 */

// Define testing environment
define('APP_ENV', 'testing');

// Load configuration
require_once __DIR__ . '/../config.php';

// Override database configuration for testing
if (getenv('DB_NAME')) {
    define('TEST_DB_NAME', getenv('DB_NAME'));
} else {
    define('TEST_DB_NAME', 'roadside_assistance_test');
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Suppress headers already sent warnings in tests
@ini_set('session.use_cookies', 0);
