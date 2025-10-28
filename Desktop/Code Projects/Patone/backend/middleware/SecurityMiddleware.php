<?php
/**
 * Roadside Assistance Admin Platform - Security Middleware
 * Handles rate limiting, IP blocking, and security checks
 */

class SecurityMiddleware {
    private $db;
    private static $instance = null;

    private function __construct() {
        // Lazy load database connection - only initialize when needed
        $this->db = null;
    }
    
    private function getDb() {
        if ($this->db === null) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Rate limiting for login attempts
     * Prevents brute force attacks by limiting requests per IP
     * 
     * @param string $action The action being rate limited (e.g., 'login', 'api')
     * @param int $maxAttempts Maximum allowed attempts
     * @param int $timeWindow Time window in seconds
     * @return bool True if allowed, false if rate limited
     */
    public function checkRateLimit($action, $maxAttempts = 5, $timeWindow = 300) {
        $ip = $this->getClientIP();
        
        // Create rate_limit_log table if it doesn't exist
        $this->ensureRateLimitTable();
        
        // Clean old entries
        $this->getDb()->query(
            "DELETE FROM rate_limit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$timeWindow]
        );
        
        // Count recent attempts
        $attempts = (int)$this->getDb()->getValue(
            "SELECT COUNT(*) FROM rate_limit_log 
             WHERE ip_address = ? AND action = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$ip, $action, $timeWindow]
        );
        
        if ($attempts >= $maxAttempts) {
            // Log the rate limit violation
            $this->logSecurityEvent('rate_limit_exceeded', [
                'ip' => $ip,
                'action' => $action,
                'attempts' => $attempts
            ]);
            return false;
        }
        
        // Record this attempt
        $this->getDb()->insert(
            "INSERT INTO rate_limit_log (ip_address, action, created_at) VALUES (?, ?, NOW())",
            [$ip, $action]
        );
        
        return true;
    }

    /**
     * Validate CSRF token for state-changing requests
     * 
     * @return bool True if valid, false otherwise
     */
    public function validateCSRFToken() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            return true; // Only check POST, PUT, DELETE requests
        }

        if (!isset($_POST['csrf_token']) && !isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $this->logSecurityEvent('csrf_token_missing', ['uri' => $_SERVER['REQUEST_URI']]);
            return false;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'];
        
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            $this->logSecurityEvent('csrf_token_invalid', ['uri' => $_SERVER['REQUEST_URI']]);
            return false;
        }

        return true;
    }

    /**
     * Check if IP is blocked
     * 
     * @return bool True if blocked, false otherwise
     */
    public function isIPBlocked() {
        $ip = $this->getClientIP();
        
        $this->ensureBlockedIPsTable();
        
        $blocked = $this->getDb()->getValue(
            "SELECT COUNT(*) FROM blocked_ips 
             WHERE ip_address = ? AND (expires_at IS NULL OR expires_at > NOW())",
            [$ip]
        );
        
        if ($blocked > 0) {
            $this->logSecurityEvent('blocked_ip_access_attempt', ['ip' => $ip]);
            return true;
        }
        
        return false;
    }

    /**
     * Block an IP address
     * 
     * @param string $ip IP address to block
     * @param string $reason Reason for blocking
     * @param int $duration Duration in seconds (null for permanent)
     */
    public function blockIP($ip, $reason, $duration = null) {
        $this->ensureBlockedIPsTable();
        
        $expiresAt = $duration ? "DATE_ADD(NOW(), INTERVAL $duration SECOND)" : "NULL";
        
        $this->getDb()->query(
            "INSERT INTO blocked_ips (ip_address, reason, expires_at, created_at) 
             VALUES (?, ?, $expiresAt, NOW())
             ON DUPLICATE KEY UPDATE reason = ?, expires_at = $expiresAt, updated_at = NOW()",
            [$ip, $reason, $reason]
        );
        
        $this->logSecurityEvent('ip_blocked', ['ip' => $ip, 'reason' => $reason]);
    }

    /**
     * Sanitize output to prevent XSS
     * Enhanced version with additional security
     * 
     * @param mixed $data Data to sanitize
     * @param bool $allowHTML Whether to allow HTML (default: false)
     * @return mixed Sanitized data
     */
    public function sanitizeOutput($data, $allowHTML = false) {
        if (is_array($data)) {
            return array_map(function($item) use ($allowHTML) {
                return $this->sanitizeOutput($item, $allowHTML);
            }, $data);
        }
        
        if (!is_string($data)) {
            return $data;
        }
        
        if ($allowHTML) {
            // Strip dangerous tags but allow safe ones
            $allowed_tags = '<p><a><strong><em><ul><ol><li><br><span><div><h1><h2><h3><h4><h5><h6>';
            return strip_tags($data, $allowed_tags);
        }
        
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return array Array with 'valid' boolean and 'errors' array
     */
    public function validatePasswordStrength($password) {
        $errors = [];
        
        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        // Maximum length (prevent DoS)
        if (strlen($password) > 128) {
            $errors[] = 'Password must not exceed 128 characters';
        }
        
        // Require uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        // Require lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        // Require number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        // Require special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        // Check against common passwords
        if ($this->isCommonPassword($password)) {
            $errors[] = 'Password is too common. Please choose a stronger password';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if password is in common password list
     * 
     * @param string $password Password to check
     * @return bool True if common, false otherwise
     */
    private function isCommonPassword($password) {
        $commonPasswords = [
            'password', 'password123', '12345678', 'qwerty', 'abc123',
            'monkey', '1234567', 'letmein', 'trustno1', 'dragon',
            'baseball', 'iloveyou', 'master', 'sunshine', 'ashley',
            'bailey', 'passw0rd', 'shadow', '123123', '654321',
            'superman', 'qazwsx', 'michael', 'football', 'welcome',
            'admin', 'admin123', 'root', 'toor', 'pass', 'password1'
        ];
        
        $lowerPassword = strtolower($password);
        
        // Check exact matches
        if (in_array($lowerPassword, $commonPasswords)) {
            return true;
        }
        
        // Check if password starts with common word
        foreach ($commonPasswords as $common) {
            if (strpos($lowerPassword, $common) === 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validate file upload security
     * Enhanced version with additional checks
     * 
     * @param array $file File array from $_FILES
     * @param array $options Validation options
     * @return array Result with 'valid' boolean and 'errors' array
     */
    public function validateFileUpload($file, $options = []) {
        $errors = [];
        
        // Default options
        $maxSize = $options['max_size'] ?? MAX_FILE_SIZE;
        $allowedExtensions = $options['allowed_extensions'] ?? ALLOWED_EXTENSIONS;
        $allowedMimeTypes = $options['allowed_mime_types'] ?? null;
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error: ' . $this->getUploadErrorMessage($file['error']);
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size of ' . ($maxSize / 1024 / 1024) . 'MB';
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedExtensions);
        }
        
        // Check MIME type (more secure than extension check alone)
        if ($allowedMimeTypes && !in_array($file['type'], $allowedMimeTypes)) {
            $errors[] = 'Invalid file type';
        }
        
        // Verify file is actually uploaded (prevent local file inclusion)
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload';
        }
        
        // Check for executable content in images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            if ($this->containsExecutableContent($file['tmp_name'])) {
                $errors[] = 'File contains potentially malicious content';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if file contains executable content
     * 
     * @param string $filePath Path to file
     * @return bool True if suspicious, false otherwise
     */
    private function containsExecutableContent($filePath) {
        // Check if file exists and is readable
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return false; // If we can't read it, we can't validate it
        }
        
        $content = file_get_contents($filePath, false, null, 0, 1024);
        
        if ($content === false) {
            return false;
        }
        
        // Check for PHP tags
        if (stripos($content, '<?php') !== false || stripos($content, '<?=') !== false) {
            return true;
        }
        
        // Check for script tags
        if (stripos($content, '<script') !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * Get upload error message
     * 
     * @param int $errorCode Error code from file upload
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Get client IP address
     * Handles proxies and load balancers
     * 
     * @return string IP address
     */
    private function getClientIP() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        // Check for proxy headers (in order of preference)
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP'
        ];
        
        foreach ($headers as $header) {
            if (isset($_SERVER[$header]) && filter_var($_SERVER[$header], FILTER_VALIDATE_IP)) {
                $ip = $_SERVER[$header];
                break;
            }
        }
        
        // If X-Forwarded-For contains multiple IPs, get the first one
        if ($ip && strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }
        
        return $ip;
    }

    /**
     * Log security event
     * 
     * @param string $event Event type
     * @param array $data Event data
     */
    private function logSecurityEvent($event, $data = []) {
        try {
            $this->ensureSecurityLogTable();
            
            $userId = $_SESSION['user_id'] ?? null;
            $ip = $this->getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            
            $eventData = array_merge([
                'user_agent' => $userAgent,
                'uri' => $uri
            ], $data);
            
            $this->getDb()->insert(
                "INSERT INTO security_log (user_id, event_type, event_data, ip_address, created_at) 
                 VALUES (?, ?, ?, ?, NOW())",
                [$userId, $event, json_encode($eventData), $ip]
            );
        } catch (Exception $e) {
            // Silently fail if database is not available
            // This prevents security logging from breaking the application
            error_log("Security log error: " . $e->getMessage());
        }
    }

    /**
     * Ensure rate_limit_log table exists
     */
    private function ensureRateLimitTable() {
        try {
            $this->getDb()->query("
                CREATE TABLE IF NOT EXISTS rate_limit_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    action VARCHAR(50) NOT NULL,
                    created_at DATETIME NOT NULL,
                    INDEX idx_ip_action_time (ip_address, action, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Failed to ensure rate_limit_log table: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ensure blocked_ips table exists
     */
    private function ensureBlockedIPsTable() {
        try {
            $this->getDb()->query("
                CREATE TABLE IF NOT EXISTS blocked_ips (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL UNIQUE,
                    reason TEXT NOT NULL,
                    expires_at DATETIME NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NULL,
                    INDEX idx_ip_expires (ip_address, expires_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Failed to ensure blocked_ips table: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ensure security_log table exists
     */
    private function ensureSecurityLogTable() {
        try {
            $this->getDb()->query("
                CREATE TABLE IF NOT EXISTS security_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    event_type VARCHAR(50) NOT NULL,
                    event_data TEXT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    created_at DATETIME NOT NULL,
                    INDEX idx_event_time (event_type, created_at),
                    INDEX idx_user (user_id),
                    INDEX idx_ip (ip_address)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log("Failed to ensure security_log table: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check session timeout
     * 
     * @param int $timeout Timeout in seconds (default: 30 minutes)
     * @return bool True if session is still valid, false if timed out
     */
    public function checkSessionTimeout($timeout = 1800) {
        if (!isset($_SESSION['login_time'])) {
            return true;
        }
        
        $elapsed = time() - $_SESSION['login_time'];
        
        if ($elapsed > $timeout) {
            // Session expired - log to PHP error log instead of database
            // to avoid dependency on database connection
            error_log("Session timeout: elapsed {$elapsed} seconds for user " . ($_SESSION['user_id'] ?? 'unknown'));
            return false;
        }
        
        // Update activity time
        $_SESSION['last_activity'] = time();
        
        return true;
    }

    /**
     * Regenerate session ID to prevent session fixation
     */
    public function regenerateSession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Check if headers have been sent
            if (!headers_sent()) {
                session_regenerate_id(true);
                
                // Update CSRF token as well
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
        }
    }
}
?>
