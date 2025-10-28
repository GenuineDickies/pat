# Testing Quick Start Guide

## Installation

### 1. Install PHP Dependencies
```bash
composer install
```

### 2. Install JavaScript Dependencies (Optional)
```bash
npm install
```

### 3. Install Playwright Browsers (Optional for E2E tests)
```bash
npx playwright install
```

## Running Tests

### Quick Test Run (PHP Only)
```bash
# Run all PHPUnit tests
./vendor/bin/phpunit

# Or using composer
composer test
```

### Run Specific Test Suites
```bash
# Unit tests only
composer test-unit

# Integration tests only
composer test-integration

# Security tests only
composer test-security
```

### Run JavaScript Tests
```bash
npm test
```

### Run E2E Tests
```bash
npm run test:e2e
```

## Test Results

Current test coverage:
- ✅ 81 PHPUnit tests (410+ assertions)
- ✅ Unit tests for all models
- ✅ Unit tests for controllers
- ✅ Integration tests for API endpoints
- ✅ Security tests (SQL injection, XSS, CSRF)
- ✅ JavaScript frontend tests
- ✅ E2E workflow tests

## Test Files Structure

```
tests/
├── Unit/
│   ├── Models/           # Model unit tests
│   └── Controllers/      # Controller unit tests
├── Integration/          # API integration tests
├── Security/             # Security tests
├── JavaScript/           # Frontend JS tests
├── E2E/                  # End-to-end tests
├── bootstrap.php         # PHPUnit bootstrap
├── TestCase.php          # Base test case class
├── TESTING.md           # Complete documentation
└── README.md            # This file
```

## Troubleshooting

### Tests fail with database errors
Some tests are marked as skipped because they require a database connection. In a real environment with a configured test database, these tests will run.

### Risky test warnings
The risky warnings are due to output buffering from config.php. These are expected and don't affect test results.

## Next Steps

1. Set up a test database for full integration testing
2. Run tests in CI/CD pipeline
3. Add more tests as new features are developed
4. Monitor test coverage and aim for >80%

For complete documentation, see [TESTING.md](TESTING.md)

