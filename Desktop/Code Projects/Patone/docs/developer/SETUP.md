# Developer Setup Guide

This guide will help you set up a local development environment for Patone.

## Prerequisites

### Required Software

- **PHP**: Version 7.4 or higher
  - Check version: `php -v`
  - Download: https://www.php.net/downloads

- **MySQL**: Version 5.7 or higher (or MariaDB 10.2+)
  - Check version: `mysql --version`
  - Download: https://dev.mysql.com/downloads/

- **Web Server**: Apache or Nginx
  - Apache: https://httpd.apache.org/
  - Nginx: https://nginx.org/

- **Python**: Version 3.8 or higher (for analytics scripts)
  - Check version: `python3 --version`
  - Download: https://www.python.org/downloads/

- **Git**: For version control
  - Check version: `git --version`
  - Download: https://git-scm.com/

### Required PHP Extensions

Ensure these PHP extensions are installed and enabled:

```bash
# Check installed extensions
php -m | grep -E 'mysqli|pdo|session|json|curl|openssl|fileinfo|zip'
```

Required extensions:
- `mysqli` - MySQL database connectivity
- `pdo` and `pdo_mysql` - PDO database abstraction
- `session` - Session management
- `json` - JSON encoding/decoding
- `curl` - HTTP requests
- `openssl` - Encryption and secure connections
- `fileinfo` - File type detection
- `zip` - Archive handling

### Optional but Recommended

- **Composer**: PHP dependency manager
  - Download: https://getcomposer.org/

- **Node.js & npm**: For frontend build tools (if adding)
  - Download: https://nodejs.org/

- **VSCode** or **PHPStorm**: IDE with PHP support

## Initial Setup

### 1. Clone the Repository

```bash
# Clone the repository
git clone https://github.com/GenuineDickies/pat.git
cd pat/Desktop/Code\ Projects/Patone

# Or if already cloned
cd /path/to/pat/Desktop/Code\ Projects/Patone
```

### 2. Configure Database

#### Create Database

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE roadside_assistance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create database user (recommended for development)
CREATE USER 'patone_dev'@'localhost' IDENTIFIED BY 'dev_password';
GRANT ALL PRIVILEGES ON roadside_assistance.* TO 'patone_dev'@'localhost';
FLUSH PRIVILEGES;

# Exit MySQL
EXIT;
```

#### Configure Application

Copy and edit the configuration file:

```bash
# Edit config.php with your database credentials
nano config.php
```

Update these lines:

```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'patone_dev');
define('DB_PASS', 'dev_password');
define('DB_NAME', 'roadside_assistance');

// For development, enable error reporting
define('DEBUG_MODE', true);
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### 3. Run Database Migration

```bash
# Navigate to migrations directory
cd database/migrations

# Run initial setup
php 001_initial_setup.php

# You should see output confirming table creation
```

### 4. Set File Permissions

```bash
# From project root
cd /path/to/pat/Desktop/Code\ Projects/Patone

# Create necessary directories
mkdir -p uploads logs

# Set permissions
chmod 755 uploads logs
chmod 644 config.php

# For Apache/Nginx, ensure web server can read files
sudo chown -R www-data:www-data . # Linux
# or
sudo chown -R _www:_www . # macOS
```

### 5. Python Environment Setup

```bash
# Create virtual environment
python3 -m venv venv

# Activate virtual environment
source venv/bin/activate  # Linux/macOS
# or
venv\Scripts\activate  # Windows

# Install dependencies
pip install -r python/requirements.txt
```

### 6. Web Server Configuration

#### Apache Configuration

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName patone.local
    DocumentRoot "/path/to/pat/Desktop/Code Projects/Patone"
    
    <Directory "/path/to/pat/Desktop/Code Projects/Patone">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "/var/log/apache2/patone-error.log"
    CustomLog "/var/log/apache2/patone-access.log" combined
</VirtualHost>
```

Enable required modules:

```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

Add to `/etc/hosts`:

```
127.0.0.1  patone.local
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name patone.local;
    root /path/to/pat/Desktop/Code Projects/Patone;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Restart Nginx:

```bash
sudo systemctl restart nginx
sudo systemctl restart php7.4-fpm
```

### 7. Verify Installation

Access the application:

```
http://patone.local
# or
http://localhost/path-to-patone
```

Default login credentials:
- **Username**: `admin`
- **Password**: `admin123`

**⚠️ IMPORTANT**: Change the default password immediately after first login!

### 8. Run Tests

```bash
cd tests
php BasicTest.php
```

Expected output:

```
=================================
Patone v1.0 Basic Test Suite
=================================

✓ PASS: Database connection successful
✓ PASS: Customer model loaded successfully
✓ PASS: Driver model loaded successfully
...

All tests passed successfully!
```

## Development Workflow

### Daily Development

1. **Start your web server and database**

```bash
# Start MySQL
sudo systemctl start mysql

# Start Apache
sudo systemctl start apache2
# or Nginx
sudo systemctl start nginx
```

2. **Activate Python environment** (if using Python scripts)

```bash
source venv/bin/activate
```

3. **Work on your feature**

```bash
# Create a feature branch
git checkout -b feature/your-feature-name

# Make your changes
# ...

# Test your changes
php tests/BasicTest.php
```

4. **Commit your changes**

```bash
git add .
git commit -m "Description of changes"
git push origin feature/your-feature-name
```

### Code Organization

```
patone/
├── backend/           # Backend PHP code
│   ├── config/       # Configuration files
│   ├── controllers/  # Request handlers
│   └── models/       # Data models
├── frontend/         # Frontend views
│   └── pages/       # Page templates
├── assets/          # Static assets (CSS, JS, images)
├── database/        # Database related files
│   ├── migrations/  # Database migrations
│   └── schema.sql   # Database schema
├── python/          # Python analytics scripts
├── uploads/         # File uploads directory
├── logs/            # Application logs
├── tests/           # Test files
└── docs/            # Documentation
```

### Making Changes

#### Adding a New Feature

1. **Create appropriate files**
   - Model in `backend/models/`
   - Controller in `backend/controllers/`
   - View in `frontend/pages/`

2. **Follow existing patterns**
   - Extend base `Model` class for models
   - Extend base `Controller` class for controllers
   - Use existing layout system for views

3. **Add route in controller**
   ```php
   public function yourRoute() {
       // Your logic here
   }
   ```

4. **Register route in Router.php** if needed

#### Database Changes

1. **Create migration file**
   ```bash
   cd database/migrations
   nano 002_your_migration.php
   ```

2. **Follow migration pattern**
   ```php
   <?php
   require_once '../../config.php';
   require_once '../../backend/config/database.php';
   
   // Your migration code
   ```

3. **Run migration**
   ```bash
   php 002_your_migration.php
   ```

### Testing

#### Unit Testing

```bash
# Run all tests
cd tests
php BasicTest.php
```

#### Manual Testing

1. Test through browser at `http://patone.local`
2. Check logs for errors: `tail -f logs/error.log`
3. Check database for data integrity

#### API Testing

Use tools like:
- **Postman**: https://www.postman.com/
- **cURL**: Command-line HTTP client
- **HTTPie**: User-friendly command-line HTTP client

Example:
```bash
curl -X GET http://patone.local/api/customers \
  -H "Content-Type: application/json" \
  -b "PHPSESSID=your-session-id"
```

### Debugging

#### Enable Debug Mode

In `config.php`:

```php
define('DEBUG_MODE', true);
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

#### Check Logs

```bash
# Application logs
tail -f logs/error.log

# Apache logs
tail -f /var/log/apache2/patone-error.log

# PHP logs
tail -f /var/log/php/error.log
```

#### Database Queries

Add to your code:

```php
// Log queries
$db = Database::getInstance();
$db->enableQueryLog();

// View logged queries
print_r($db->getQueryLog());
```

### Code Style

Follow PSR-12 coding standards:

- Use 4 spaces for indentation
- Opening braces on same line for methods
- One class per file
- Meaningful variable and method names
- Add PHPDoc comments

Example:

```php
<?php
/**
 * Customer Model
 * Handles customer data operations
 */
class Customer extends Model {
    /**
     * Get customer by ID
     * 
     * @param int $id Customer ID
     * @return array|null Customer data or null if not found
     */
    public function getById($id) {
        // Implementation
    }
}
```

## Troubleshooting

### Database Connection Fails

**Error**: "Could not connect to database"

**Solutions**:
1. Check MySQL is running: `sudo systemctl status mysql`
2. Verify credentials in `config.php`
3. Ensure database exists: `mysql -u root -p -e "SHOW DATABASES;"`
4. Check user permissions

### Permission Denied Errors

**Error**: "Permission denied when writing to uploads/"

**Solutions**:
```bash
chmod 755 uploads logs
sudo chown -R www-data:www-data uploads logs
```

### .htaccess Not Working

**Error**: "404 Not Found" on routes

**Solutions**:
1. Enable mod_rewrite: `sudo a2enmod rewrite`
2. Check Apache config: `AllowOverride All`
3. Restart Apache: `sudo systemctl restart apache2`

### PHP Extensions Missing

**Error**: "Call to undefined function mysqli_connect"

**Solutions**:
```bash
# Ubuntu/Debian
sudo apt-get install php-mysqli php-pdo-mysql

# Restart web server
sudo systemctl restart apache2
```

### Python Scripts Not Working

**Error**: "ModuleNotFoundError"

**Solutions**:
```bash
# Activate virtual environment
source venv/bin/activate

# Reinstall requirements
pip install -r python/requirements.txt
```

## Next Steps

- Read [Code Style Guide](./CODE_STYLE.md)
- Review [Contributing Guidelines](./CONTRIBUTING.md)
- Check [Architecture Overview](../architecture/README.md)
- Explore [API Documentation](../api/README.md)

## Getting Help

- Check [FAQ](../FAQ.md)
- Review [Troubleshooting Guide](../TROUBLESHOOTING.md)
- Open an issue on GitHub
- Contact the development team

---

**Happy coding! 🚀**
