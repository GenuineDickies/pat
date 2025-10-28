# Developer Setup Guide

This guide explains how to set up a local development environment for the Patone Roadside Assistance platform.

## Prerequisites

### Required Software
- **PHP 7.4+** (project requirement from `composer.json`)
- **Composer** for PHP dependency management
- **MySQL 5.7+ or MariaDB 10.3+** for the database
- **Node.js 16+ and npm** for JavaScript testing and tools
- **Git** for version control

### Required PHP Extensions
Ensure these PHP extensions are installed:
```bash
php -m | grep -E "(pdo|pdo_mysql|mysqli|mbstring|openssl|json|curl|zip)"
```

## Step-by-Step Setup

### 1. Clone the Repository
```bash
git clone https://github.com/GenuineDickies/pat.git
cd pat/Desktop/Code\ Projects/Patone
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Database Setup

#### Create the Database
```bash
# Connect to MySQL
mysql -u root -p

# Create the database
CREATE DATABASE roadside_assistance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE roadside_assistance_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

#### Import the Schema
```bash
# Import main database schema
mysql -u root -p roadside_assistance < database/schema.sql

# Import test database schema
mysql -u root -p roadside_assistance_test < database/schema.sql
```

### 4. Configuration

The `config.php` file contains the main configuration. Key settings to verify:

```php
// Database Configuration (lines 23-26 in config.php)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Update with your MySQL user
define('DB_PASS', '');              // Update with your MySQL password
define('DB_NAME', 'roadside_assistance');
```

**Security Note:** Change the `ENCRYPTION_KEY` in `config.php` before production use.

### 5. Install Node.js Dependencies (for testing)
```bash
npm install
```

### 6. Verify Setup - Run Tests

#### PHP Tests (PHPUnit)
```bash
# Run all tests
composer test
# Or directly with PHPUnit
vendor/bin/phpunit

# Run specific test suites
composer test-unit        # Unit tests only
composer test-integration # Integration tests only
composer test-security    # Security tests only
```

#### JavaScript Tests (Jest)
```bash
npm test                  # Run once
npm run test:watch       # Watch mode
npm run test:coverage    # With coverage report
```

#### End-to-End Tests (Playwright)
```bash
npm run test:e2e         # Headless E2E tests
npm run test:e2e:ui      # Interactive E2E tests
```

## Development Workflow

### Running the Application Locally
```bash
# Start PHP development server
php -S localhost:8000 -t . index.php

# Application will be available at http://localhost:8000
```

### File Structure Overview
- `backend/` - PHP controllers, models (PSR-4 autoloaded as `Patone\` namespace)
- `frontend/` - Frontend pages and JavaScript
- `assets/` - Compiled CSS, JS, images
- `database/` - Schema, migrations, sample data
- `tests/` - All test files (Unit, Integration, Security, E2E, JavaScript)
- `logs/` - Application logs
- `uploads/` - File uploads directory

## Development Tools

### Code Quality & Documentation
```bash
# Install PHPStan for static analysis (recommended)
composer require --dev phpstan/phpstan

# Run static analysis
vendor/bin/phpstan analyse backend --level=7

# Install phpDocumentor for API docs
composer require --dev phpdocumentor/phpdocumentor

# Generate PHP API documentation
vendor/bin/phpdoc -d backend -t docs/phpdoc
```

### Debugging & Logs
- Application logs: `logs/` directory
- PHP errors: Check `error_log` in project root or PHP-FPM logs
- Debug mode: Already enabled in `config.php` (set `display_errors` to 0 for production)

## Common Issues & Solutions

### Database Connection Errors
```bash
# Test MySQL connection
mysql -u root -p -e "SELECT VERSION();"

# Check if database exists
mysql -u root -p -e "SHOW DATABASES;" | grep roadside
```

### Permission Issues
```bash
# Ensure proper permissions for logs and uploads
chmod 755 logs/ uploads/
chmod 644 config.php
```

### Missing PHP Extensions
```bash
# Ubuntu/Debian
sudo apt-get install php-pdo php-mysql php-mbstring php-curl php-zip php-json

# macOS with Homebrew
brew install php
```

## Docker Alternative (Optional)

For a containerized development environment, see the Docker Compose example in `deployment-guide.md`.

## Next Steps

1. **Explore the API**: Use the OpenAPI spec at `documentation/docs/openapi.yaml`
2. **Run all tests** to ensure everything works: `composer test && npm test`
3. **Check code architecture**: Read `documentation/docs/code-architecture.md`
4. **Review security**: See `SECURITY.md` and run security tests
