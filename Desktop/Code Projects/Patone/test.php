<?php
// Test PHP configuration and server setup
echo "<h1>Server Environment Test</h1>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $conn = new mysqli('localhost', 'root', '', 'test');
    if ($conn->connect_error) {
        echo "<p style='color: red;'>Database connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>Database connection successful!</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: orange;'>Database test skipped or failed: " . $e->getMessage() . "</p>";
}

// Check required PHP extensions
echo "<h2>PHP Extensions</h2>";
$extensions = ['mysqli', 'session', 'fileinfo', 'openssl', 'curl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✓ $ext - Loaded</p>";
    } else {
        echo "<p style='color: red;'>✗ $ext - Missing</p>";
    }
}

// Check file permissions
echo "<h2>File Permissions Test</h2>";
$test_dirs = ['uploads', 'logs', 'database'];
foreach ($test_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p style='color: green;'>✓ $dir - Writable</p>";
        } else {
            echo "<p style='color: orange;'>⚠ $dir - Not writable</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ $dir - Directory not found</p>";
    }
}
?>
