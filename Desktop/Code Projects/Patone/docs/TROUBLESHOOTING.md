# Troubleshooting Guide

Common issues and their solutions for the Patone platform.

## Table of Contents

- [Installation Issues](#installation-issues)
- [Login and Authentication](#login-and-authentication)
- [Database Issues](#database-issues)
- [Performance Issues](#performance-issues)
- [API Issues](#api-issues)
- [File Upload Issues](#file-upload-issues)
- [Email and Notifications](#email-and-notifications)
- [GPS and Location Tracking](#gps-and-location-tracking)
- [Browser and Display Issues](#browser-and-display-issues)
- [Server and Hosting](#server-and-hosting)

## Installation Issues

### Problem: Cannot Connect to Database

**Symptoms:**
- Error: "Could not connect to database"
- Application shows database connection error
- White screen or 500 error

**Solutions:**

1. **Check MySQL is running**
   ```bash
   sudo systemctl status mysql
   # Start if not running
   sudo systemctl start mysql
   ```

2. **Verify database credentials in config.php**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'roadside_assistance');
   ```

3. **Test database connection manually**
   ```bash
   mysql -u your_username -p roadside_assistance
   ```

4. **Check database user permissions**
   ```sql
   SHOW GRANTS FOR 'your_username'@'localhost';
   ```

5. **Verify database exists**
   ```bash
   mysql -u root -p -e "SHOW DATABASES;"
   ```

### Problem: 500 Internal Server Error

**Symptoms:**
- White screen with "500 Internal Server Error"
- No detailed error message

**Solutions:**

1. **Check PHP error logs**
   ```bash
   # Ubuntu/Debian
   sudo tail -f /var/log/apache2/error.log
   # or for Nginx
   sudo tail -f /var/log/nginx/error.log
   ```

2. **Enable detailed error reporting** (development only)
   Add to config.php:
   ```php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
   ```

3. **Check file permissions**
   ```bash
   # Files should be 644
   find /var/www/patone -type f -exec chmod 644 {} \;
   # Directories should be 755
   find /var/www/patone -type d -exec chmod 755 {} \;
   # uploads and logs should be writable
   chmod 775 /var/www/patone/uploads
   chmod 775 /var/www/patone/logs
   ```

4. **Verify .htaccess exists and is correct**
   ```apache
   RewriteEngine On
   RewriteBase /
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
   ```

5. **Check mod_rewrite is enabled**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

### Problem: Database Migration Fails

**Symptoms:**
- Error when running migration script
- Tables not created
- Foreign key constraint errors

**Solutions:**

1. **Run migration manually**
   ```bash
   cd database/migrations
   php 001_initial_setup.php
   ```

2. **Check for existing tables**
   ```bash
   mysql -u root -p roadside_assistance -e "SHOW TABLES;"
   ```

3. **Drop and recreate database if needed** (WARNING: This deletes all data)
   ```bash
   mysql -u root -p -e "DROP DATABASE IF EXISTS roadside_assistance;"
   mysql -u root -p -e "CREATE DATABASE roadside_assistance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

4. **Import schema directly**
   ```bash
   mysql -u root -p roadside_assistance < database/schema.sql
   ```

## Login and Authentication

### Problem: Cannot Login with Default Credentials

**Symptoms:**
- "Invalid username or password" error
- Default admin credentials don't work

**Solutions:**

1. **Verify database was properly initialized**
   ```bash
   mysql -u root -p roadside_assistance -e "SELECT * FROM users;"
   ```

2. **Reset admin password**
   ```php
   <?php
   require_once 'config.php';
   require_once 'backend/config/database.php';
   
   $db = Database::getInstance();
   $password = password_hash('admin123', PASSWORD_BCRYPT);
   $db->query("UPDATE users SET password = ? WHERE username = 'admin'", [$password]);
   echo "Password reset successfully\n";
   ```

3. **Create admin user manually**
   ```sql
   INSERT INTO users (username, email, password, first_name, last_name, role, status)
   VALUES ('admin', 'admin@example.com', '$2y$10$...', 'Admin', 'User', 'admin', 'active');
   ```

### Problem: Session Expires Too Quickly

**Symptoms:**
- Logged out unexpectedly
- Have to login frequently

**Solutions:**

1. **Increase session lifetime in php.ini**
   ```ini
   session.gc_maxlifetime = 86400  ; 24 hours
   session.cookie_lifetime = 86400
   ```

2. **Configure in config.php**
   ```php
   ini_set('session.gc_maxlifetime', 86400);
   ini_set('session.cookie_lifetime', 86400);
   ```

3. **Restart web server**
   ```bash
   sudo systemctl restart apache2  # or nginx + php-fpm
   ```

### Problem: "Remember Me" Not Working

**Symptoms:**
- Remember me checkbox doesn't keep user logged in
- Still have to login on every visit

**Solutions:**

1. **Check browser cookie settings**
   - Ensure cookies are enabled
   - Check browser is not in private/incognito mode
   - Clear browser cookies and try again

2. **Verify cookie domain in config**
   ```php
   session_set_cookie_params([
       'lifetime' => 2592000,  // 30 days
       'path' => '/',
       'domain' => $_SERVER['HTTP_HOST'],
       'secure' => true,  // Only if using HTTPS
       'httponly' => true,
       'samesite' => 'Lax'
   ]);
   ```

## Database Issues

### Problem: Database Queries Running Slow

**Symptoms:**
- Application is slow
- Pages take long to load
- Timeout errors

**Solutions:**

1. **Check database server load**
   ```bash
   mysqladmin -u root -p processlist
   ```

2. **Optimize tables**
   ```sql
   OPTIMIZE TABLE users, customers, drivers, service_requests;
   ```

3. **Add missing indexes**
   ```sql
   -- Example: Add index if missing
   CREATE INDEX idx_status ON service_requests(status);
   CREATE INDEX idx_created_at ON service_requests(created_at);
   ```

4. **Check slow query log**
   ```bash
   mysql -u root -p -e "SHOW VARIABLES LIKE 'slow_query_log%';"
   ```

5. **Increase MySQL resources** (in /etc/mysql/my.cnf)
   ```ini
   innodb_buffer_pool_size = 2G
   max_connections = 200
   ```

### Problem: Database Connection Limit Reached

**Symptoms:**
- Error: "Too many connections"
- Some users can't access application
- Intermittent connection failures

**Solutions:**

1. **Check current connections**
   ```sql
   SHOW PROCESSLIST;
   SHOW STATUS LIKE 'Threads_connected';
   ```

2. **Kill idle connections**
   ```sql
   -- Find idle connections
   SELECT * FROM information_schema.processlist WHERE command = 'Sleep';
   -- Kill specific connection
   KILL [process_id];
   ```

3. **Increase max connections**
   ```bash
   sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
   ```
   Add/modify:
   ```ini
   max_connections = 200
   ```
   Restart MySQL:
   ```bash
   sudo systemctl restart mysql
   ```

4. **Implement connection pooling** in application

## Performance Issues

### Problem: Application Running Slow

**Symptoms:**
- Pages load slowly
- Long response times
- Timeouts

**Solutions:**

1. **Enable PHP OPcache**
   ```bash
   sudo nano /etc/php/7.4/apache2/php.ini
   ```
   Set:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.max_accelerated_files=10000
   ```

2. **Check server resources**
   ```bash
   htop  # CPU and memory usage
   df -h  # Disk space
   iotop  # Disk I/O
   ```

3. **Optimize images and assets**
   - Compress images
   - Minify CSS/JavaScript
   - Enable browser caching

4. **Add database indexes**
   - Review frequently queried columns
   - Add indexes where appropriate

5. **Implement caching**
   - Install Redis or Memcached
   - Cache frequently accessed data
   - Cache database queries

### Problem: High Memory Usage

**Symptoms:**
- Server running out of memory
- OOM (Out of Memory) errors
- System becomes unresponsive

**Solutions:**

1. **Check memory usage**
   ```bash
   free -h
   top
   ```

2. **Reduce PHP memory limit**
   ```ini
   memory_limit = 128M  # Lower if needed
   ```

3. **Optimize MySQL memory usage**
   ```ini
   innodb_buffer_pool_size = 512M  # Reduce if needed
   ```

4. **Add swap space**
   ```bash
   sudo fallocate -l 2G /swapfile
   sudo chmod 600 /swapfile
   sudo mkswap /swapfile
   sudo swapon /swapfile
   ```

5. **Upgrade server** if resources are insufficient

## API Issues

### Problem: API Returns 401 Unauthorized

**Symptoms:**
- API calls fail with 401 error
- "Unauthorized" error message

**Solutions:**

1. **Verify user is logged in**
   - Login through web interface first
   - Ensure session cookie is included

2. **Check session cookie**
   ```bash
   curl -v http://localhost/api/customers \
     -H "Cookie: PHPSESSID=your-session-id"
   ```

3. **Verify authentication in API controller**
   - Check `isLoggedIn()` function
   - Ensure session is started

### Problem: API Returns Unexpected Data

**Symptoms:**
- Wrong data format
- Missing fields
- Null values

**Solutions:**

1. **Check API endpoint path**
   - Verify correct URL
   - Check for typos

2. **Review request parameters**
   - Verify required parameters included
   - Check parameter names and values

3. **Enable API debugging**
   ```php
   // In ApiController
   error_log('API Request: ' . print_r($_REQUEST, true));
   error_log('API Response: ' . print_r($response, true));
   ```

4. **Check database for data**
   ```sql
   SELECT * FROM customers LIMIT 5;
   ```

### Problem: CORS Errors

**Symptoms:**
- Browser console shows CORS error
- "Access-Control-Allow-Origin" error
- API works in curl but not browser

**Solutions:**

1. **Add CORS headers to API controller**
   ```php
   header('Access-Control-Allow-Origin: https://your-domain.com');
   header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
   header('Access-Control-Allow-Headers: Content-Type');
   header('Access-Control-Allow-Credentials: true');
   ```

2. **Configure CORS in Apache** (.htaccess)
   ```apache
   <IfModule mod_headers.c>
       Header set Access-Control-Allow-Origin "https://your-domain.com"
       Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE"
   </IfModule>
   ```

3. **Configure CORS in Nginx**
   ```nginx
   add_header 'Access-Control-Allow-Origin' 'https://your-domain.com' always;
   add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE' always;
   ```

## File Upload Issues

### Problem: File Uploads Fail

**Symptoms:**
- "Failed to upload file" error
- Upload button doesn't work
- Files not appearing in uploads directory

**Solutions:**

1. **Check directory permissions**
   ```bash
   ls -la uploads/
   # Should show: drwxrwxr-x www-data www-data
   
   sudo chmod 775 uploads/
   sudo chown www-data:www-data uploads/
   ```

2. **Check PHP upload limits**
   ```bash
   sudo nano /etc/php/7.4/apache2/php.ini
   ```
   Increase:
   ```ini
   upload_max_filesize = 64M
   post_max_size = 64M
   max_file_uploads = 20
   ```

3. **Restart web server**
   ```bash
   sudo systemctl restart apache2
   ```

4. **Check disk space**
   ```bash
   df -h
   ```

### Problem: Uploaded Files Not Displaying

**Symptoms:**
- Files upload but can't be viewed
- 404 error when accessing uploaded files
- Broken image icons

**Solutions:**

1. **Verify files exist**
   ```bash
   ls -la uploads/
   ```

2. **Check file permissions**
   ```bash
   # Files should be readable
   sudo chmod 644 uploads/*
   ```

3. **Verify upload path in code**
   - Check BASE_URL setting
   - Verify file path construction

4. **Check .htaccess doesn't block uploads directory**
   ```apache
   # uploads/.htaccess should allow access
   Options -Indexes
   ```

## Email and Notifications

### Problem: Emails Not Sending

**Symptoms:**
- No emails received
- Error when trying to send email
- Email notifications not working

**Solutions:**

1. **Check SMTP configuration** (if using external SMTP)
   ```python
   # In python/config.py
   EMAIL_CONFIG = {
       'smtp_server': 'smtp.gmail.com',
       'smtp_port': 587,
       'smtp_username': 'your-email@gmail.com',
       'smtp_password': 'your-app-password',
       'from_email': 'your-email@gmail.com'
   }
   ```

2. **Test email sending**
   ```bash
   # Test with Python script
   python3 python/test_email.py
   ```

3. **Check firewall** allows outbound SMTP
   ```bash
   # Test SMTP connection
   telnet smtp.gmail.com 587
   ```

4. **Check spam folder** for received emails

5. **Enable less secure apps** (Gmail)
   - For Gmail, use App Passwords
   - Or enable "Less secure app access"

### Problem: SMS Notifications Not Working

**Symptoms:**
- SMS not received
- Twilio errors
- Invalid phone numbers

**Solutions:**

1. **Verify Twilio credentials** (python/config.py)
   ```python
   SMS_CONFIG = {
       'account_sid': 'your_account_sid',
       'auth_token': 'your_auth_token',
       'from_number': '+1234567890'
   }
   ```

2. **Check phone number format**
   - Must include country code
   - Example: +1234567890

3. **Verify Twilio account** has credits

4. **Check Twilio logs** in dashboard

## GPS and Location Tracking

### Problem: Driver Location Not Updating

**Symptoms:**
- Driver location shows as null
- Location outdated
- Map not showing driver position

**Solutions:**

1. **Verify GPS tracking enabled** in settings
   ```sql
   SELECT * FROM settings WHERE `key` = 'enable_gps_tracking';
   ```

2. **Check driver mobile app**
   - Location permissions granted
   - GPS enabled on device
   - App has network connection

3. **Verify API endpoint working**
   ```bash
   curl -X PUT http://localhost/api/drivers/1/location \
     -H "Content-Type: application/json" \
     -d '{"latitude": 37.7749, "longitude": -122.4194}'
   ```

4. **Check database for location updates**
   ```sql
   SELECT id, first_name, last_name, current_latitude, 
          current_longitude, last_location_update 
   FROM drivers 
   WHERE id = 1;
   ```

### Problem: Distance Calculations Incorrect

**Symptoms:**
- Wrong distances shown
- Nearest driver calculation wrong
- Route optimization not working

**Solutions:**

1. **Verify coordinates are valid**
   - Latitude: -90 to 90
   - Longitude: -180 to 180

2. **Check distance calculation formula**
   ```php
   // Haversine formula implementation
   // Verify implementation is correct
   ```

3. **Use known coordinates for testing**
   ```php
   // Test with known distance
   // New York to Los Angeles = ~3,944 km
   ```

## Browser and Display Issues

### Problem: Layout Broken or Misaligned

**Symptoms:**
- Page layout looks wrong
- Elements overlapping
- Responsive design not working

**Solutions:**

1. **Clear browser cache**
   - Ctrl+Shift+Delete (Chrome/Firefox)
   - Clear cached images and files

2. **Check browser console for errors**
   - F12 to open developer tools
   - Look for JavaScript errors
   - Check network tab for failed resources

3. **Verify CSS files loading**
   ```bash
   curl -I http://localhost/assets/css/style.css
   # Should return 200 OK
   ```

4. **Check for CSS syntax errors**

5. **Test in different browser**

### Problem: JavaScript Errors

**Symptoms:**
- Features not working
- Forms not submitting
- Console shows errors

**Solutions:**

1. **Open browser console** (F12)
   - Check for error messages
   - Note the line numbers

2. **Check JavaScript file loading**
   ```html
   <!-- Verify script tags -->
   <script src="/assets/js/main.js"></script>
   ```

3. **Clear cache and hard reload**
   - Ctrl+Shift+R (Windows/Linux)
   - Cmd+Shift+R (Mac)

4. **Check for conflicting libraries**

## Server and Hosting

### Problem: Server Running Out of Disk Space

**Symptoms:**
- Error: "No space left on device"
- Can't write files
- Database errors

**Solutions:**

1. **Check disk usage**
   ```bash
   df -h
   du -sh /var/www/patone/* | sort -h
   ```

2. **Clean up old logs**
   ```bash
   # Remove old rotated logs
   sudo find /var/log -name "*.gz" -type f -mtime +30 -delete
   
   # Clear application logs
   sudo rm /var/www/patone/logs/*.log
   ```

3. **Remove old backups**
   ```bash
   # Keep only last 30 days
   find /backups -name "*.sql.gz" -type f -mtime +30 -delete
   ```

4. **Clean package cache**
   ```bash
   # Ubuntu/Debian
   sudo apt clean
   sudo apt autoremove
   ```

### Problem: High CPU Usage

**Symptoms:**
- Server very slow
- High load average
- Unresponsive pages

**Solutions:**

1. **Identify resource-heavy processes**
   ```bash
   top
   # Press Shift+P to sort by CPU
   ```

2. **Check for runaway processes**
   ```bash
   ps aux | grep php
   ps aux | grep mysql
   ```

3. **Review slow database queries**
   ```sql
   SHOW FULL PROCESSLIST;
   ```

4. **Optimize application code**
   - Add caching
   - Optimize database queries
   - Reduce external API calls

5. **Consider load balancing** if traffic is high

## Getting Additional Help

### Gathering Information for Support

When requesting help, provide:

1. **Error messages** (exact text)
2. **Steps to reproduce** the issue
3. **Environment details**:
   ```bash
   php -v
   mysql --version
   apache2 -v  # or nginx -v
   cat /etc/os-release
   ```

4. **Relevant log entries**:
   ```bash
   sudo tail -50 /var/log/apache2/error.log
   sudo tail -50 /var/www/patone/logs/error.log
   ```

5. **Recent changes** made to the system

### Support Resources

- **Documentation**: Check all relevant docs
- **GitHub Issues**: Search existing issues
- **Community Forums**: Ask in community
- **Professional Support**: Contact support team

### Enabling Debug Mode (Development Only)

In config.php:
```php
define('DEBUG_MODE', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

**WARNING:** Never enable debug mode in production!

---

## Quick Reference

### Common Commands

```bash
# Restart web server
sudo systemctl restart apache2  # or nginx + php-fpm

# Restart MySQL
sudo systemctl restart mysql

# Check service status
sudo systemctl status apache2
sudo systemctl status mysql

# View logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/www/patone/logs/error.log

# Check disk space
df -h

# Check memory
free -h

# Check processes
htop
```

### Emergency Recovery

```bash
# Restore database from backup
mysql -u root -p roadside_assistance < /backups/db_backup.sql

# Restore files from backup
cd /var/www
sudo tar -xzf /backups/files_backup.tar.gz

# Reset file permissions
sudo chown -R www-data:www-data /var/www/patone
sudo find /var/www/patone -type d -exec chmod 755 {} \;
sudo find /var/www/patone -type f -exec chmod 644 {} \;
```

---

**Still having issues?** Contact support with detailed information about your problem.
