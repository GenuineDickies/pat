<?php
/**
 * Roadside Assistance Admin Platform - Setup Script
 * Initial setup and configuration for the application
 */

// Include configuration
require_once 'config.php';

// Check if setup is needed
$setupNeeded = !is_file('database/setup_complete.txt');

if (!$setupNeeded && !isset($_GET['force'])) {
    header('Location: login');
    exit;
}

$errors = [];
$success = [];

// Handle setup form submission
if (isset($_POST['setup'])) {
    try {
        // Validate inputs
        if (empty($_POST['admin_email']) || empty($_POST['admin_password'])) {
            $errors[] = 'Admin email and password are required';
        } elseif (!isValidEmail($_POST['admin_email'])) {
            $errors[] = 'Please enter a valid email address';
        } elseif (strlen($_POST['admin_password']) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
        } else {
            // Database setup
            $db = Database::getInstance();

            // Check database connection
            if (!$db) {
                throw new Exception('Database connection failed');
            }

            // Create tables
            $sql = file_get_contents('database/schema.sql');
            if (!$sql) {
                throw new Exception('Could not read database schema file');
            }

            // Split and execute SQL statements
            $statements = explode(';', $sql);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    $db->query($statement);
                }
            }

            // Create admin user
            $hashedPassword = hashPassword($_POST['admin_password']);
            $db->insert(
                "INSERT INTO users (first_name, last_name, email, password, role, status, created_at)
                 VALUES (?, ?, ?, ?, 'admin', 'active', NOW())",
                [$_POST['admin_first_name'], $_POST['admin_last_name'], $_POST['admin_email'], $hashedPassword]
            );

            // Mark setup as complete
            file_put_contents('database/setup_complete.txt', date('Y-m-d H:i:s'));

            $success[] = 'Setup completed successfully! You can now log in with your admin credentials.';
            $setupNeeded = false;
        }

    } catch (Exception $e) {
        $errors[] = 'Setup failed: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roadside Assistance Admin - Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 500px;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .setup-header h1 {
            color: #333;
            font-weight: 600;
        }
        .setup-header p {
            color: #666;
        }
        .alert {
            border-radius: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="setup-container">
                    <div class="setup-header">
                        <h1><i class="bi bi-tools text-primary"></i> System Setup</h1>
                        <p>Initialize your Roadside Assistance Admin Platform</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6>Setup Errors:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <h6>Success!</h6>
                            <ul class="mb-0">
                                <?php foreach ($success as $message): ?>
                                    <li><?php echo htmlspecialchars($message); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="mt-3">
                                <a href="login" class="btn btn-primary">Continue to Login</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($setupNeeded && empty($success)): ?>
                        <div class="alert alert-info">
                            <p><strong>Database Setup Required:</strong> Please complete the setup form below to initialize your system.</p>
                        </div>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="admin_first_name" class="form-label">Admin First Name</label>
                                <input type="text" class="form-control" id="admin_first_name" name="admin_first_name"
                                       value="<?php echo isset($_POST['admin_first_name']) ? htmlspecialchars($_POST['admin_first_name']) : ''; ?>"
                                       required maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label for="admin_last_name" class="form-label">Admin Last Name</label>
                                <input type="text" class="form-control" id="admin_last_name" name="admin_last_name"
                                       value="<?php echo isset($_POST['admin_last_name']) ? htmlspecialchars($_POST['admin_last_name']) : ''; ?>"
                                       required maxlength="50">
                            </div>

                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Admin Email</label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email"
                                       value="<?php echo isset($_POST['admin_email']) ? htmlspecialchars($_POST['admin_email']) : ''; ?>"
                                       required maxlength="100">
                            </div>

                            <div class="mb-3">
                                <label for="admin_password" class="form-label">Admin Password</label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password"
                                       required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                                <div class="form-text">Must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                       required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="setup_database" name="setup_database" checked>
                                    <label class="form-check-label" for="setup_database">
                                        Create database tables and initial data
                                    </label>
                                </div>
                            </div>

                            <button type="submit" name="setup" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle"></i> Complete Setup
                            </button>
                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                This setup will create the admin user and initialize the database.
                                Make sure your database configuration is correct before proceeding.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('admin_password').value;
            const confirmPassword = this.value;

            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('admin_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }

            if (password.length < <?php echo PASSWORD_MIN_LENGTH; ?>) {
                e.preventDefault();
                alert('Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long');
                return false;
            }
        });
    </script>
</body>
</html>
