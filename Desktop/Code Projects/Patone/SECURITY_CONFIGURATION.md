# Patone v1.0 Security Configuration Guide

## Overview
This guide provides detailed information about configuring and using the security features implemented in Patone v1.0.

## Table of Contents
1. [Password Policy](#password-policy)
2. [Session Security](#session-security)
3. [Rate Limiting](#rate-limiting)
4. [CSRF Protection](#csrf-protection)
5. [XSS Protection](#xss-protection)
6. [File Upload Security](#file-upload-security)
7. [Security Headers](#security-headers)
8. [IP Blocking](#ip-blocking)
9. [Security Logging](#security-logging)
10. [Production Deployment](#production-deployment)

---

## Password Policy

### Configuration
Located in `config.php`:

```php
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_REQUIRE_UPPERCASE', true);
define('PASSWORD_REQUIRE_LOWERCASE', true);
define('PASSWORD_REQUIRE_NUMBER', true);
define('PASSWORD_REQUIRE_SPECIAL', true);
```

### Requirements
- Minimum 8 characters (recommended: 12+)
- At least one uppercase letter (A-Z)
- At least one lowercase letter (a-z)
- At least one number (0-9)
- At least one special character (!@#$%^&*, etc.)
- Maximum 128 characters (DoS prevention)
- Not in common password list

### Usage in Code

```php
// Using SecurityMiddleware
$security = SecurityMiddleware::getInstance();
$validation = $security->validatePasswordStrength($password);

if (!$validation['valid']) {
    // Show errors
    foreach ($validation['errors'] as $error) {
        echo $error . "\n";
    }
}

// Using helper function
$validation = validatePasswordStrength($password);
```

### Common Password Detection
The system checks against a list of common passwords and patterns:
- Exact matches (e.g., "password", "123456")
- Common word prefixes (e.g., "password123")

To add custom patterns, edit the `isCommonPassword()` method in `SecurityMiddleware.php`.

---

## Session Security

### Configuration
Located in `config.php`:

```php
define('SESSION_TIMEOUT', 30 * 60); // 30 minutes
define('SESSION_REGENERATE_INTERVAL', 5 * 60); // 5 minutes
```

### Features

#### 1. Session Timeout
Automatically logs out users after inactivity:

```php
$security = SecurityMiddleware::getInstance();
if (!$security->checkSessionTimeout(SESSION_TIMEOUT)) {
    // Session expired - redirect to login
    session_destroy();
    header('Location: /login');
    exit();
}
```

#### 2. Session Regeneration
Prevents session fixation attacks:

```php
$security = SecurityMiddleware::getInstance();
$security->regenerateSession();
```

Automatically called:
- After successful login
- Periodically based on SESSION_REGENERATE_INTERVAL

#### 3. Secure Session Configuration
In `config.php`:

```php
// Start session with secure settings
session_start([
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_secure' => true, // Enable in production with HTTPS
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
    'use_only_cookies' => true
]);
```

### Best Practices
1. Always use HTTPS in production
2. Set `cookie_secure` to `true` when using HTTPS
3. Implement session timeout checks on all protected pages
4. Regenerate session ID after authentication
5. Clear session data completely on logout

---

## Rate Limiting

### Configuration
Located in `config.php`:

```php
// Login rate limiting
define('RATE_LIMIT_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_LOGIN_WINDOW', 300); // 5 minutes

// API rate limiting
define('RATE_LIMIT_API_ATTEMPTS', 100);
define('RATE_LIMIT_API_WINDOW', 60); // 1 minute
```

### Usage

```php
$security = SecurityMiddleware::getInstance();

// Check rate limit before processing request
if (!$security->checkRateLimit('login', RATE_LIMIT_LOGIN_ATTEMPTS, RATE_LIMIT_LOGIN_WINDOW)) {
    die('Too many attempts. Please try again later.');
}

// For API endpoints
if (!$security->checkRateLimit('api', RATE_LIMIT_API_ATTEMPTS, RATE_LIMIT_API_WINDOW)) {
    http_response_code(429);
    die('Rate limit exceeded');
}
```

### How It Works
1. Tracks requests by IP address and action type
2. Stores attempts in `rate_limit_log` table
3. Automatically cleans old entries
4. Returns `false` if limit exceeded
5. Logs rate limit violations to security log

### Custom Rate Limits
Create custom rate limits for different actions:

```php
// Password reset: 3 attempts per 10 minutes
$security->checkRateLimit('password_reset', 3, 600);

// Contact form: 10 submissions per hour
$security->checkRateLimit('contact_form', 10, 3600);

// Search queries: 60 per minute
$security->checkRateLimit('search', 60, 60);
```

---

## CSRF Protection

### Automatic Token Generation
CSRF tokens are automatically generated in `config.php`:

```php
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

### Adding to Forms

```php
<form method="POST" action="/submit">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <!-- Form fields -->
    <button type="submit">Submit</button>
</form>
```

### Validation in Controllers

```php
class MyController extends Controller {
    public function processForm() {
        // Validate CSRF token
        $this->validateCSRF();
        
        // Process form data
        // ...
    }
}
```

### AJAX Requests

```javascript
// Include CSRF token in AJAX requests
$.ajax({
    url: '/api/endpoint',
    method: 'POST',
    headers: {
        'X-CSRF-Token': '<?php echo generateCSRFToken(); ?>'
    },
    data: formData,
    success: function(response) {
        // Handle response
    }
});
```

The middleware checks both `$_POST['csrf_token']` and `$_SERVER['HTTP_X_CSRF_TOKEN']`.

---

## XSS Protection

### Output Escaping Functions

```php
// For HTML context
<?php echo escapeHtml($userInput); ?>

// For JavaScript context
<script>
var data = <?php echo escapeJs($userData); ?>;
</script>

// For URL context
<a href="<?php echo escapeUrl($url); ?>">Link</a>
```

### Context-Aware Escaping

1. **HTML Context**: Use `escapeHtml()` or `htmlspecialchars()`
   ```php
   <div><?php echo escapeHtml($comment); ?></div>
   ```

2. **JavaScript Context**: Use `escapeJs()` or `json_encode()`
   ```php
   <script>
   var username = <?php echo escapeJs($username); ?>;
   </script>
   ```

3. **URL Context**: Use `escapeUrl()`
   ```php
   <a href="<?php echo escapeUrl($redirect); ?>">Go</a>
   ```

4. **CSS Context**: Avoid user input in CSS, use inline styles with escaped values
   ```php
   <div style="color: <?php echo escapeHtml($color); ?>"></div>
   ```

### Input Sanitization

```php
// Sanitize input data
$cleanData = sanitize($_POST);

// Or use controller method
$postData = $this->getPostData(); // Already sanitized
```

---

## File Upload Security

### Configuration

```php
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
```

### Validation

```php
$security = SecurityMiddleware::getInstance();

$validation = $security->validateFileUpload($_FILES['document'], [
    'max_size' => 10 * 1024 * 1024, // 10MB
    'allowed_extensions' => ['pdf', 'doc', 'docx'],
    'allowed_mime_types' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ]
]);

if (!$validation['valid']) {
    foreach ($validation['errors'] as $error) {
        echo $error . "\n";
    }
    exit;
}

// Process upload
$result = uploadFile($_FILES['document'], 'documents');
```

### Security Checks Performed

1. **File Size**: Validates against max size limit
2. **File Extension**: Checks against whitelist
3. **MIME Type**: Validates content type (if specified)
4. **Upload Verification**: Ensures file was actually uploaded
5. **Content Scanning**: Checks for PHP tags and script tags in files
6. **Safe Naming**: Generates random filenames to prevent conflicts

### Best Practices

1. Always validate file uploads server-side
2. Store uploaded files outside the web root when possible
3. Use random filenames to prevent overwrites
4. Scan uploaded files with antivirus when possible
5. Limit file types to only what's necessary
6. Set appropriate file permissions (644 for files, 755 for directories)

---

## Security Headers

### Configuration
Headers are automatically set in `config.php`:

```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
```

### Content Security Policy (CSP)

```php
define('CSP_ENABLED', true);
```

The default CSP configuration:

```php
$csp = "default-src 'self'; ";
$csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; ";
$csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
$csp .= "font-src 'self' https://fonts.gstatic.com; ";
$csp .= "img-src 'self' data: https:; ";
$csp .= "connect-src 'self'; ";
$csp .= "frame-ancestors 'none';";
```

### HTTPS Enforcement

```php
define('FORCE_HTTPS', false); // Set to true in production
```

When enabled, automatically redirects HTTP to HTTPS.

### Header Explanations

- **X-Content-Type-Options**: Prevents MIME-type sniffing
- **X-Frame-Options**: Prevents clickjacking attacks
- **X-XSS-Protection**: Enables browser XSS filtering
- **Referrer-Policy**: Controls referrer information
- **Permissions-Policy**: Restricts browser features
- **Content-Security-Policy**: Controls resource loading

---

## IP Blocking

### Check if IP is Blocked

```php
$security = SecurityMiddleware::getInstance();

if ($security->isIPBlocked()) {
    http_response_code(403);
    die('Access denied');
}
```

### Block an IP Address

```php
$security = SecurityMiddleware::getInstance();

// Temporary block (1 hour)
$security->blockIP('192.168.1.100', 'Too many failed login attempts', 3600);

// Permanent block
$security->blockIP('192.168.1.100', 'Malicious activity', null);
```

### Database Table
IP blocks are stored in the `blocked_ips` table:

```sql
CREATE TABLE blocked_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    reason TEXT NOT NULL,
    expires_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL
);
```

---

## Security Logging

### Automatic Logging
The following events are automatically logged:

- Rate limit violations
- CSRF token failures
- Blocked IP access attempts
- Session timeouts
- File uploads

### View Security Logs

```sql
SELECT * FROM security_log 
WHERE event_type = 'rate_limit_exceeded'
ORDER BY created_at DESC 
LIMIT 100;
```

### Log Structure

```sql
CREATE TABLE security_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_data TEXT NULL,
    ip_address VARCHAR(45) NOT NULL,
    created_at DATETIME NOT NULL
);
```

---

## Production Deployment

### Pre-Deployment Checklist

1. **Update Configuration**
   ```php
   // In config.php
   define('FORCE_HTTPS', true);
   define('CSP_ENABLED', true);
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **Change Default Credentials**
   - Update default admin password
   - Change database passwords
   - Generate new encryption keys

3. **File Permissions**
   ```bash
   chmod 644 config.php
   chmod 755 uploads/
   chmod 755 logs/
   chmod 600 .env # if using environment variables
   ```

4. **SSL/TLS Certificate**
   - Install valid SSL certificate
   - Test HTTPS configuration
   - Verify certificate chain

5. **Database Security**
   - Use strong database passwords
   - Restrict database user permissions
   - Enable binary logging
   - Set up automated backups

6. **Update .htaccess**
   ```apache
   # Force HTTPS
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### Post-Deployment Verification

1. Test login with rate limiting
2. Verify CSRF protection on forms
3. Test file upload restrictions
4. Check security headers with online tools
5. Scan with OWASP ZAP or similar
6. Review security logs for anomalies

### Monitoring

1. **Regular Security Audits**
   - Weekly review of security logs
   - Monthly password policy review
   - Quarterly penetration testing

2. **Automated Monitoring**
   - Set up alerts for rate limit violations
   - Monitor failed login attempts
   - Track unusual IP access patterns

3. **Incident Response**
   - Document security incidents
   - Implement IP blocks for threats
   - Update security measures based on findings

---

## Troubleshooting

### Common Issues

1. **Rate Limiting Too Strict**
   - Adjust RATE_LIMIT_* values in config.php
   - Clear old entries from rate_limit_log table

2. **CSRF Token Failures**
   - Ensure session is started before form display
   - Check session cookie settings
   - Verify token is included in form

3. **Session Timeout Issues**
   - Adjust SESSION_TIMEOUT value
   - Implement AJAX keepalive for long operations
   - Check server session garbage collection settings

4. **File Upload Errors**
   - Check PHP upload_max_filesize and post_max_size
   - Verify directory permissions
   - Review ALLOWED_EXTENSIONS configuration

---

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [Content Security Policy](https://content-security-policy.com/)
- [Mozilla Web Security](https://infosec.mozilla.org/guidelines/web_security)

---

**Last Updated**: 2024-10-28  
**Version**: 1.0.0
