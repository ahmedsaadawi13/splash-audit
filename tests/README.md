# SplashAudit Test Suite

This directory contains the complete test suite for SplashAudit.

## Test Structure

```
tests/
├── bootstrap.php          # Test environment setup
├── TestCase.php          # Base test class
├── Unit/                 # Unit tests for individual components
│   ├── ModelTest.php    # Model functionality tests
│   └── ValidatorTest.php # Validation logic tests
├── Integration/          # Integration tests (future)
├── Security/            # Security-focused tests
│   └── SecurityTest.php # SQL injection, XSS, CSRF tests
└── API/                 # API endpoint tests
    └── ApiTest.php      # REST API functionality tests
```

## Running Tests

### Prerequisites

1. Install PHPUnit:
```bash
composer require --dev phpunit/phpunit
```

2. Create test database:
```bash
mysql -u root -p -e "CREATE DATABASE splash_audit_test"
mysql -u root -p splash_audit_test < database.sql
```

### Run All Tests

```bash
./vendor/bin/phpunit
```

### Run Specific Test Suite

```bash
# Unit tests only
./vendor/bin/phpunit --testsuite "Unit Tests"

# Security tests only
./vendor/bin/phpunit --testsuite "Security Tests"

# API tests only
./vendor/bin/phpunit --testsuite "API Tests"
```

### Run Specific Test File

```bash
./vendor/bin/phpunit tests/Unit/ModelTest.php
```

### Run with Code Coverage

```bash
./vendor/bin/phpunit --coverage-html coverage/
```

## Test Coverage

The test suite covers:

- ✅ **Model Layer**: CRUD operations, pagination, tenant isolation
- ✅ **Security**: SQL injection, XSS, CSRF, rate limiting
- ✅ **Validation**: Input validation, sanitization
- ✅ **API**: Authentication, rate limiting, JSON parsing
- ✅ **Authorization**: Role-based access control
- ✅ **Session**: Session management and security

## Writing New Tests

1. Extend `TestCase` class
2. Use `setUp()` for test preparation
3. Use `tearDown()` for cleanup (automatic database rollback)
4. Use helper methods:
   - `createTestTenant()` - Create test tenant
   - `createTestUser()` - Create test user
   - `actingAs()` - Authenticate as user
   - `assertArrayHasKeys()` - Check multiple array keys
   - `assertSqlInjectionPrevented()` - Check SQL safety
   - `assertXssPrevented()` - Check XSS safety

### Example Test

```php
<?php

require_once __DIR__ . '/../TestCase.php';

class ExampleTest extends TestCase {
    public function testExample() {
        $tenantId = $this->createTestTenant();
        $userId = $this->createTestUser($tenantId);
        $this->actingAs($userId, $tenantId);

        // Your test logic here
        $this->assertTrue(true);
    }
}
```

## Continuous Integration

Tests run automatically on every commit via GitHub Actions (if configured).

## Test Database

- Tests use a separate database: `splash_audit_test`
- Each test runs in a transaction that is rolled back
- Database state is clean for every test
- No test pollution between test cases

## Best Practices

1. **Isolation**: Each test should be independent
2. **Descriptive Names**: Use clear test method names
3. **Single Assertion**: Test one thing at a time
4. **Fast Tests**: Keep tests quick (<1 second each)
5. **Coverage**: Aim for >80% code coverage
6. **Security**: Always test security-critical code

## Troubleshooting

### Database Connection Failed

- Check `phpunit.xml` database credentials
- Ensure test database exists
- Verify MySQL is running

### Class Not Found

- Run `composer dump-autoload`
- Check bootstrap.php autoloader

### Tests Hanging

- Check for infinite loops
- Verify database transactions are rolled back
- Check for unclosed connections

## Contributing

When adding new features:

1. Write tests first (TDD)
2. Ensure all tests pass
3. Maintain >80% coverage
4. Add integration tests for new modules

## Support

For test-related issues, check:
- PHPUnit documentation: https://phpunit.de
- Test examples in `tests/` directory
- Project documentation
