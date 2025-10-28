# Patone v1.0 Test Suite

This directory contains tests for the Patone roadside assistance platform.

## Running Tests

### Basic Test Suite
Tests core functionality including database connectivity and model operations:

```bash
php BasicTest.php
```

### Expected Output
```
=================================
Patone v1.0 Basic Test Suite
=================================

✓ PASS: Database connection successful
✓ PASS: Customer model loaded successfully (Total customers: X)
✓ PASS: Driver model loaded successfully (Total drivers: X)
✓ PASS: ServiceType model loaded successfully (Active types: X)
✓ PASS: ServiceRequest model loaded successfully (Total requests: X)
✓ PASS: User model loaded successfully (Total users: X)
✓ PASS: Setting model loaded successfully (Public settings: X)

=================================
Test Results Summary
=================================
Total Tests: 7
Passed: 7
Failed: 0
Success Rate: 100.00%
=================================

All tests passed successfully!
```

## Test Coverage

### Current Tests
- ✅ Database connectivity
- ✅ Model instantiation
- ✅ Statistics retrieval
- ✅ Basic CRUD operations

### Future Tests (Recommended)
- Controller endpoint testing
- API response validation
- Authentication and authorization
- Input validation and sanitization
- Performance benchmarks
- Integration tests
- End-to-end workflow tests

## Prerequisites

Before running tests, ensure:
1. Database is created and configured
2. Schema migration has been run
3. Config.php has correct database credentials
4. Web server has proper file permissions

## Continuous Integration

For CI/CD pipelines, add this to your workflow:

```bash
# Install dependencies
composer install

# Run tests
cd tests
php BasicTest.php

# Check exit code
if [ $? -eq 0 ]; then
    echo "Tests passed"
else
    echo "Tests failed"
    exit 1
fi
```

## Writing New Tests

Follow the existing pattern in `BasicTest.php`:

```php
private function testYourFeature() {
    try {
        // Your test logic here
        $result = doSomething();
        
        if ($result === expectedValue) {
            $this->pass("Your feature test passed");
        } else {
            $this->fail("Your feature test failed");
        }
    } catch (Exception $e) {
        $this->fail("Your feature test error: " . $e->getMessage());
    }
}
```

## Troubleshooting

### Database Connection Fails
- Verify database credentials in `config.php`
- Ensure MySQL server is running
- Check database user permissions

### Model Tests Fail
- Run database migration first
- Verify tables exist in database
- Check for missing dependencies

### All Tests Fail
- Check PHP error logs
- Verify file permissions
- Ensure all required PHP extensions are installed
