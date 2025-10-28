<?php
/**
 * Roadside Assistance Admin Platform - Authentication Controller
 * Handles user login, logout, and session management
 */

class AuthController extends Controller {
    public function __construct() {
        parent::__construct();
        // Don't require login for auth methods
    }

    // Show login form
    public function login() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            $this->redirect(SITE_URL . 'dashboard');
        }

        // Capture login form content
        ob_start();
        include FRONTEND_PATH . 'pages/login.php';
        $content = ob_get_clean();

        $data = [
            'pageTitle' => 'Login',
            'content' => $content
        ];

        $this->render('layout', $data);
    }

    // Process login
    public function doLogin() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            $this->redirect(SITE_URL . 'dashboard');
        }

        // Check rate limiting before processing
        $security = SecurityMiddleware::getInstance();
        if (!$security->checkRateLimit('login', RATE_LIMIT_LOGIN_ATTEMPTS, RATE_LIMIT_LOGIN_WINDOW)) {
            $this->redirectWithError(SITE_URL . 'login', 'Too many login attempts. Please try again later.');
        }

        // Validate CSRF token
        $this->validateCSRF();

        // Get and sanitize form data
        $postData = $this->getPostData();

        // Validate required fields
        $errors = $this->validateRequired($postData, ['email', 'password']);
        if (!empty($errors)) {
            $this->redirectWithError(SITE_URL . 'login', 'Please fill in all required fields');
        }

        // Validate email format
        $emailError = $this->validateEmail($postData['email']);
        if ($emailError) {
            $this->redirectWithError(SITE_URL . 'login', $emailError);
        }

        // Check login attempts
        $this->checkLoginAttempts($postData['email']);

        try {
            // Get user from database
            $user = $this->db->getRow(
                "SELECT id, email, password, first_name, last_name, role, status
                 FROM users WHERE email = ?",
                [$postData['email']]
            );

            if (!$user) {
                $this->recordFailedLogin($postData['email']);
                $this->redirectWithError(SITE_URL . 'login', 'Invalid email or password');
            }

            // Check if account is active
            if ($user['status'] !== 'active') {
                $this->redirectWithError(SITE_URL . 'login', 'Your account has been deactivated. Please contact an administrator.');
            }

            // Verify password
            if (!verifyPassword($postData['password'], $user['password'])) {
                $this->recordFailedLogin($postData['email']);
                $this->redirectWithError(SITE_URL . 'login', 'Invalid email or password');
            }

            // Login successful - regenerate session ID to prevent session fixation
            $security->regenerateSession();
            
            $this->loginUser($user, isset($postData['remember']));

            // Clear failed login attempts
            $this->clearFailedLogins($postData['email']);

            // Log successful login
            logActivity('login', 'User logged in successfully', $user['id']);

            $this->redirectWithSuccess(SITE_URL . 'dashboard', 'Welcome back, ' . $user['first_name'] . '!');

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $this->redirectWithError(SITE_URL . 'login', 'An error occurred during login. Please try again.');
        }
    }

    // Logout user
    public function logout() {
        if (isLoggedIn()) {
            $userId = $_SESSION['user_id'];
            $userName = $_SESSION['user_name'];

            logActivity('logout', 'User logged out', $userId);
        }

        // Clear session
        session_unset();
        session_destroy();

        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        $this->redirectWithSuccess(SITE_URL . 'login', 'You have been logged out successfully.');
    }

    // Login user and set session
    private function loginUser($user, $remember = false) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Set permissions based on role
        $_SESSION['permissions'] = $this->getUserPermissions($user['role']);

        // Handle remember me
        if ($remember) {
            $token = generateRandomString(64);
            $this->db->query(
                "INSERT INTO user_sessions (user_id, token, expires_at, created_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), NOW())
                 ON DUPLICATE KEY UPDATE token = ?, expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY)",
                [$user['id'], $token, $token]
            );

            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        }
    }

    // Get user permissions based on role
    private function getUserPermissions($role) {
        $permissions = [];

        switch ($role) {
            case 'admin':
                $permissions = [
                    'view_dashboard', 'view_customers', 'add_customers', 'edit_customers', 'delete_customers',
                    'view_requests', 'add_requests', 'edit_requests', 'delete_requests',
                    'view_drivers', 'add_drivers', 'edit_drivers', 'delete_drivers',
                    'view_reports', 'generate_reports', 'view_settings', 'edit_settings'
                ];
                break;

            case 'manager':
                $permissions = [
                    'view_dashboard', 'view_customers', 'add_customers', 'edit_customers',
                    'view_requests', 'add_requests', 'edit_requests',
                    'view_drivers', 'add_drivers', 'edit_drivers',
                    'view_reports', 'generate_reports'
                ];
                break;

            case 'dispatcher':
                $permissions = [
                    'view_dashboard', 'view_customers', 'view_requests', 'add_requests', 'edit_requests',
                    'view_drivers', 'view_reports'
                ];
                break;

            case 'driver':
                $permissions = [
                    'view_dashboard', 'view_requests', 'edit_requests'
                ];
                break;

            default:
                $permissions = ['view_dashboard'];
        }

        return $permissions;
    }

    // Check login attempts and lockout if necessary
    private function checkLoginAttempts($email) {
        $attempts = $this->getFailedLoginAttempts($email);

        if ($attempts >= LOGIN_ATTEMPTS) {
            $this->redirectWithError(SITE_URL . 'login', 'Too many failed login attempts. Please try again in 15 minutes.');
        }
    }

    // Get failed login attempts count
    private function getFailedLoginAttempts($email) {
        $count = $this->db->getValue(
            "SELECT COUNT(*) FROM login_attempts
             WHERE email = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL " . LOCKOUT_TIME . " SECOND)",
            [$email]
        );

        return (int)$count;
    }

    // Record failed login attempt
    private function recordFailedLogin($email) {
        $this->db->query(
            "INSERT INTO login_attempts (email, ip_address, attempted_at)
             VALUES (?, ?, NOW())",
            [$email, $_SERVER['REMOTE_ADDR']]
        );
    }

    // Clear failed login attempts
    private function clearFailedLogins($email) {
        $this->db->query(
            "DELETE FROM login_attempts WHERE email = ?",
            [$email]
        );
    }

    // Check remember me token on page load
    public function checkRememberMe() {
        if (isLoggedIn() || !isset($_COOKIE['remember_token'])) {
            return;
        }

        $token = $_COOKIE['remember_token'];

        $session = $this->db->getRow(
            "SELECT user_id, expires_at FROM user_sessions
             WHERE token = ? AND expires_at > NOW() AND active = 1",
            [$token]
        );

        if ($session) {
            $user = $this->db->getRow(
                "SELECT id, email, password, first_name, last_name, role, status
                 FROM users WHERE id = ?",
                [$session['user_id']]
            );

            if ($user && $user['status'] === 'active') {
                $this->loginUser($user, true);
            } else {
                // Invalid token, clear it
                setcookie('remember_token', '', time() - 3600, '/');
            }
        } else {
            // Invalid token, clear it
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }
}
?>
