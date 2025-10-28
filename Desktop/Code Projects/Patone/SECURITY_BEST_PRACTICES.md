# Patone v1.0 Security Best Practices

## Overview
This document outlines security best practices for developers working on the Patone application.

## Table of Contents
1. [General Security Principles](#general-security-principles)
2. [Authentication & Authorization](#authentication--authorization)
3. [Input Validation](#input-validation)
4. [Output Encoding](#output-encoding)
5. [Database Security](#database-security)
6. [File Operations](#file-operations)
7. [Error Handling](#error-handling)
8. [Logging](#logging)
9. [API Security](#api-security)
10. [Code Review Checklist](#code-review-checklist)

---

## General Security Principles

### Defense in Depth
Implement multiple layers of security controls:

```php
// Example: Multiple layers of validation
public function updateProfile($userId, $data) {
    // Layer 1: Authentication check
    $this->requireLogin();
    
    // Layer 2: Authorization check
    if ($_SESSION['user_id'] != $userId) {
        throw new Exception('Unauthorized');
    }
    
    // Layer 3: CSRF validation
    $this->validateCSRF();
    
    // Layer 4: Input validation
    $this->validateRequired($data, ['email', 'name']);
    
    // Layer 5: Data sanitization
    $data = $this->getPostData();
    
    // Now safe to process
    // ...
}
```

### Principle of Least Privilege
Grant minimum necessary permissions:

```php
// BAD: Using root database user
define('DB_USER', 'root');

// GOOD: Using limited privilege user
define('DB_USER', 'app_user');
// GRANT SELECT, INSERT, UPDATE, DELETE ON database.* TO 'app_user'@'localhost';
```

### Fail Securely
When errors occur, fail to a safe state:

```php
// BAD: Exposing error details
try {
    $user = $this->authenticate($username, $password);
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// GOOD: Generic error message
try {
    $user = $this->authenticate($username, $password);
} catch (Exception $e) {
    error_log("Authentication error: " . $e->getMessage());
    $this->redirectWithError('/login', 'Authentication failed');
}
```

---

## Authentication & Authorization

### Always Use Prepared Statements

```php
// BAD: SQL injection vulnerability
$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);

// GOOD: Prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

### Secure Password Handling

```php
// BAD: Plain text or weak hashing
$password = md5($password);

// GOOD: Use bcrypt via password_hash
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Verification
if (password_verify($inputPassword, $hashedPassword)) {
    // Password correct
}
```

### Check Authentication on Every Request

```php
class Controller {
    protected function requireLogin() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
        }
        
        // Also check session timeout
        $security = SecurityMiddleware::getInstance();
        if (!$security->checkSessionTimeout(SESSION_TIMEOUT)) {
            session_destroy();
            $this->redirectWithError('/login', 'Session expired');
        }
    }
}
```

### Implement Proper Authorization Checks

```php
// BAD: Only checking if user is logged in
public function deleteUser($id) {
    if (!isLoggedIn()) {
        die('Unauthorized');
    }
    $this->userModel->delete($id);
}

// GOOD: Checking specific permissions
public function deleteUser($id) {
    $this->requireLogin();
    $this->requirePermission('delete_users');
    
    // Additional check: Can't delete own account
    if ($id == $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }
    
    $this->userModel->delete($id);
}
```

---

## Input Validation

### Validate All User Input

```php
// Always validate input data
public function createUser($data) {
    // Check required fields
    $errors = $this->validateRequired($data, ['username', 'email', 'password']);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'errors' => ['Invalid email format']];
    }
    
    // Validate password strength
    $security = SecurityMiddleware::getInstance();
    $passwordValidation = $security->validatePasswordStrength($data['password']);
    if (!$passwordValidation['valid']) {
        return ['success' => false, 'errors' => $passwordValidation['errors']];
    }
    
    // Proceed with user creation
    // ...
}
```

### Whitelist, Don't Blacklist

```php
// BAD: Blacklist approach
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
    die('Invalid filename');
}

// GOOD: Whitelist approach
$allowedExtensions = ['jpg', 'png', 'pdf'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    die('File type not allowed');
}
```

### Validate Data Types

```php
// BAD: No type validation
$userId = $_GET['id'];
$user = $this->userModel->find($userId);

// GOOD: Type validation
$userId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($userId === false) {
    die('Invalid user ID');
}
$user = $this->userModel->find($userId);
```

---

## Output Encoding

### Context-Aware Output Encoding

```php
// HTML context
<div class="user-comment">
    <?php echo escapeHtml($comment); ?>
</div>

// JavaScript context
<script>
var userData = <?php echo escapeJs($userData); ?>;
</script>

// URL context
<a href="<?php echo escapeUrl($redirectUrl); ?>">Continue</a>

// HTML attribute context
<input type="text" value="<?php echo escapeHtml($value); ?>">
```

### Never Trust User Data

```php
// BAD: Directly outputting user data
echo $_GET['name'];

// GOOD: Always escape
echo escapeHtml($_GET['name']);
```

### Be Careful with Rich Text

```php
// If you must allow HTML, use a whitelist library
// Install: composer require ezyang/htmlpurifier
require_once 'htmlpurifier/library/HTMLPurifier.auto.php';

$config = HTMLPurifier_Config::createDefault();
$purifier = new HTMLPurifier($config);
$cleanHtml = $purifier->purify($dirtyHtml);
```

---

## Database Security

### Use Prepared Statements Always

```php
// GOOD: Prepared statement
$this->db->query(
    "INSERT INTO users (username, email) VALUES (?, ?)",
    [$username, $email]
);

// GOOD: Named parameters (if your DB class supports it)
$this->db->query(
    "INSERT INTO users (username, email) VALUES (:username, :email)",
    ['username' => $username, 'email' => $email]
);

// BAD: String concatenation (NEVER DO THIS)
$this->db->query(
    "INSERT INTO users (username, email) VALUES ('$username', '$email')"
);
```

### Limit Database Permissions

```sql
-- Create user with minimum necessary privileges
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_password';

-- Grant only needed privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON patone.* TO 'app_user'@'localhost';

-- Don't grant these unless absolutely necessary:
-- GRANT CREATE, DROP, ALTER ON patone.* TO 'app_user'@'localhost';
```

### Use Transactions for Related Operations

```php
try {
    $this->db->beginTransaction();
    
    $orderId = $this->orderModel->create($orderData);
    $this->inventoryModel->decreaseStock($productId, $quantity);
    $this->paymentModel->processPayment($paymentData);
    
    $this->db->commit();
} catch (Exception $e) {
    $this->db->rollback();
    throw $e;
}
```

---

## File Operations

### Validate File Uploads

```php
public function handleFileUpload($file) {
    $security = SecurityMiddleware::getInstance();
    
    // Validate file
    $validation = $security->validateFileUpload($file, [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_extensions' => ['jpg', 'png', 'pdf'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'application/pdf'
        ]
    ]);
    
    if (!$validation['valid']) {
        return ['success' => false, 'errors' => $validation['errors']];
    }
    
    // Generate safe filename
    $safeFilename = generateRandomString(32) . '.' . 
                    pathinfo($file['name'], PATHINFO_EXTENSION);
    
    // Move to secure location
    $destination = UPLOAD_PATH . $safeFilename;
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $safeFilename];
    }
    
    return ['success' => false, 'errors' => ['Upload failed']];
}
```

### Secure File Access

```php
// BAD: Direct file access
$filename = $_GET['file'];
readfile("uploads/$filename");

// GOOD: Validate and restrict access
public function downloadFile($fileId) {
    $this->requireLogin();
    
    // Get file record from database
    $file = $this->fileModel->find($fileId);
    if (!$file) {
        http_response_code(404);
        die('File not found');
    }
    
    // Check permissions
    if ($file['user_id'] != $_SESSION['user_id']) {
        $this->requirePermission('view_all_files');
    }
    
    // Serve file
    $filepath = UPLOAD_PATH . $file['filename'];
    if (!file_exists($filepath)) {
        http_response_code(404);
        die('File not found');
    }
    
    header('Content-Type: ' . $file['mime_type']);
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    readfile($filepath);
}
```

### Set Proper File Permissions

```bash
# Files should be readable by web server but not writable
chmod 644 file.php

# Directories should be accessible
chmod 755 directory/

# Sensitive files (config, etc.)
chmod 600 config.php

# Upload directories
chmod 755 uploads/
```

---

## Error Handling

### Don't Expose Sensitive Information

```php
// BAD: Exposing stack traces
try {
    $result = $this->processPayment($data);
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}

// GOOD: Log details, show generic message
try {
    $result = $this->processPayment($data);
} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    error_log($e->getTraceAsString());
    return ['success' => false, 'error' => 'Payment processing failed'];
}
```

### Production Error Configuration

```php
// config.php - Production settings
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . 'php_errors.log');
```

---

## Logging

### Log Security-Relevant Events

```php
// Login attempts
logActivity('login_attempt', "User attempted login: $username");

// Permission denials
logActivity('permission_denied', "User $userId attempted unauthorized action");

// Data modifications
logActivity('user_updated', "User $userId modified user $targetId");

// File operations
logActivity('file_download', "User $userId downloaded file $fileId");
```

### Don't Log Sensitive Data

```php
// BAD: Logging passwords
error_log("Login failed for $username with password $password");

// GOOD: Log without sensitive data
error_log("Login failed for user: $username");

// BAD: Logging full credit card numbers
logActivity('payment', "Payment with card $cardNumber");

// GOOD: Log masked data
$maskedCard = '****-****-****-' . substr($cardNumber, -4);
logActivity('payment', "Payment with card $maskedCard");
```

---

## API Security

### Authenticate API Requests

```php
class ApiController extends Controller {
    public function __construct() {
        parent::__construct();
        
        // Require authentication for all API endpoints
        if (!isLoggedIn()) {
            $this->jsonError('Authentication required', 401);
        }
        
        // Check rate limiting
        $security = SecurityMiddleware::getInstance();
        if (!$security->checkRateLimit('api', RATE_LIMIT_API_ATTEMPTS, RATE_LIMIT_API_WINDOW)) {
            $this->jsonError('Rate limit exceeded', 429);
        }
    }
}
```

### Validate API Input

```php
public function createResource() {
    // Validate JSON input
    $data = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $this->jsonError('Invalid JSON', 400);
    }
    
    // Validate required fields
    $errors = $this->validateRequired($data, ['name', 'type']);
    if (!empty($errors)) {
        $this->jsonError(implode(', ', $errors), 400);
    }
    
    // Process request
    // ...
}
```

### Set Appropriate CORS Headers

```php
// Be specific with allowed origins
header('Access-Control-Allow-Origin: https://yourdomain.com');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// DON'T do this in production:
// header('Access-Control-Allow-Origin: *');
```

---

## Code Review Checklist

### Security Checklist for Every Feature

- [ ] All user input is validated
- [ ] All output is properly escaped for context
- [ ] SQL queries use prepared statements
- [ ] Authentication and authorization checks are in place
- [ ] CSRF tokens are validated for state-changing requests
- [ ] Rate limiting is applied to sensitive endpoints
- [ ] File uploads are validated and sanitized
- [ ] Sensitive data is not logged
- [ ] Error messages don't expose sensitive information
- [ ] Session management is secure
- [ ] Passwords are hashed with bcrypt
- [ ] Security headers are set
- [ ] HTTPS is enforced in production
- [ ] Third-party dependencies are up to date
- [ ] Code follows principle of least privilege

### Before Deploying to Production

- [ ] Change all default credentials
- [ ] Update encryption keys
- [ ] Set `display_errors` to 0
- [ ] Enable HTTPS enforcement
- [ ] Review and test all security configurations
- [ ] Scan for vulnerabilities (OWASP ZAP, etc.)
- [ ] Review security logs
- [ ] Test backup and recovery procedures
- [ ] Document security configurations
- [ ] Set up monitoring and alerts

---

## Common Vulnerabilities to Avoid

### 1. SQL Injection
**Never** concatenate user input into SQL queries. Always use prepared statements.

### 2. Cross-Site Scripting (XSS)
Always escape output based on context (HTML, JavaScript, URL).

### 3. Cross-Site Request Forgery (CSRF)
Include and validate CSRF tokens on all state-changing requests.

### 4. Insecure Direct Object References
Validate that users have permission to access requested resources.

### 5. Security Misconfiguration
- Keep software updated
- Remove default accounts
- Disable directory listing
- Remove debug features in production

### 6. Sensitive Data Exposure
- Encrypt sensitive data at rest and in transit
- Use HTTPS everywhere
- Don't log sensitive information

### 7. Broken Authentication
- Implement strong password policies
- Use multi-factor authentication when possible
- Implement account lockout
- Use secure session management

### 8. Insecure Deserialization
Avoid deserializing untrusted data. If necessary, validate strictly.

### 9. Using Components with Known Vulnerabilities
Regularly update dependencies and scan for known vulnerabilities.

### 10. Insufficient Logging & Monitoring
Log security events and monitor for suspicious activity.

---

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [Secure Coding Guidelines](https://wiki.sei.cmu.edu/confluence/display/seccode)

---

**Remember**: Security is not a one-time task but an ongoing process. Stay informed about new vulnerabilities and best practices.

---

**Last Updated**: 2024-10-28  
**Version**: 1.0.0
