# LunarFortis Testing Guide

This guide explains how to run the comprehensive test suite for the LunarFortis package, including parallel testing optimizations.

## Test Structure

### Unit Tests (`tests/Unit/`)
- Fast-running tests that don't require external dependencies
- Mock-based testing for isolated component testing
- No API calls or database operations

### Integration Tests (`tests/Integration/`)
- Real API testing with Fortis sandbox environment
- Requires valid Fortis credentials in `.env.testing`
- Tests complete payment workflows

## Running Tests

### Quick Commands

```bash
# Run all tests (parallel by default)
composer test

# Run all tests in parallel with 8 processes
composer test-parallel

# Run only unit tests (fast)
composer test-unit

# Run only integration tests (sequential)
composer test-integration

# Run integration tests in parallel (recommended)
composer test-integration-parallel
```

### Manual Commands

```bash
# Sequential execution
vendor/bin/pest

# Parallel execution (auto-detects cores)
vendor/bin/pest --parallel

# Parallel with specific process count
vendor/bin/pest --parallel --processes=8

# Integration tests only
vendor/bin/pest tests/Integration/

# Unit tests only
vendor/bin/pest tests/Unit/
```

## Parallel Testing Optimizations

### Performance Benefits

| Execution Type | Duration | Processes | Performance Gain |
|----------------|----------|-----------|------------------|
| Sequential     | ~97s     | 1         | Baseline         |
| Parallel (12)  | ~61s     | 12        | 37% faster       |
| Parallel (8)   | ~53s     | 8         | 45% faster ⭐    |
| Parallel (6)   | ~55s     | 6         | 43% faster       |

**Optimal Configuration:** 8 processes provide the best performance on most systems.

### Parallel Testing Features

1. **Automatic Resource Isolation**
   - Unique test identifiers per process
   - Conflict detection and retry logic
   - Random delays to prevent race conditions

2. **Robust Error Handling**
   - Exponential backoff for API conflicts (422 errors)
   - Automatic retries for terminal busy states
   - Process-specific terminal titles

3. **Optimized Configuration**
   - Process count optimized for typical hardware
   - Test tokens enabled for isolation
   - Proper cleanup between tests

## Integration Test Setup

### Environment Configuration

Create a `.env.testing` file with your Fortis sandbox credentials:

```env
# Required for all integration tests
FORTIS_INTEGRATION_USER_ID=your_user_id
FORTIS_INTEGRATION_USER_API_KEY=your_api_key
FORTIS_INTEGRATION_DEVELOPER_ID=your_developer_id
FORTIS_INTEGRATION_LOCATION_ID=your_location_id
FORTIS_INTEGRATION_PRODUCT_TRANSACTION_ID=your_product_transaction_id

# Optional - for terminal-specific tests
FORTIS_INTEGRATION_TERMINAL_PRODUCT_TRANSACTION_ID=your_terminal_product_id

# Optional - defaults
FORTIS_INTEGRATION_ENVIRONMENT=sandbox
FORTIS_INTEGRATION_DEBUG=true
```

### Test Coverage

#### Payment Processing Tests
- ✅ Authorization and Capture workflows
- ✅ Refund processing (partial and full)
- ✅ Decline scenario handling
- ✅ Timeout and error handling
- ✅ Amount validation and edge cases
- ✅ API error responses

#### Verification Tests
- ✅ AVS (Address Verification System)
- ✅ CVV (Card Verification Value)
- ✅ Enum code validation
- ✅ Response simulation

#### Terminal Tests
- ✅ Terminal sync and status management
- ✅ Authorization status monitoring
- ✅ Error handling and recovery
- ✅ Multi-amount testing scenarios

#### Elements Integration Tests
- ✅ Client token generation
- ✅ Transaction intention creation
- ✅ API authentication validation

## Test Groups

Use test groups to run specific subsets:

```bash
# Payment processing tests
vendor/bin/pest --group=payment

# AVS verification tests
vendor/bin/pest --group=avs-verification

# CVV verification tests
vendor/bin/pest --group=cvv-verification

# Error handling tests
vendor/bin/pest --group=error-handling

# Fast tests (unit + enum validation)
vendor/bin/pest --group=fast

# Slow tests (full integration workflows)
vendor/bin/pest --group=slow
```

## Troubleshooting

### Common Issues

1. **Integration tests skipped**
   - Check `.env.testing` file exists and has correct credentials
   - Verify Fortis sandbox access

2. **422 Errors in parallel tests**
   - Terminal resource conflicts are handled automatically with retries
   - Reduce process count if issues persist: `--processes=4`

3. **Timeout errors**
   - Some tests require physical terminal interaction in certain configurations
   - Tests will skip automatically if terminals are not available

### Debug Options

```bash
# Verbose output
vendor/bin/pest -v

# Show test progress
vendor/bin/pest --verbose

# Coverage report
composer test-coverage
```

## Configuration Files

- `pest.xml` - Main Pest configuration with parallel settings
- `phpunit.xml.dist` - PHPUnit configuration
- `tests/Pest.php` - Test bootstrapping and parallel optimization
- `tests/Integration/helpers.php` - Parallel testing utilities

## Best Practices

1. **Use parallel testing for development** - Significantly faster feedback
2. **Run sequential tests for debugging** - Easier to trace issues
3. **Monitor resource usage** - Reduce processes if system becomes overloaded
4. **Keep credentials secure** - Use separate sandbox credentials for testing
5. **Test in CI/CD** - Parallel tests work well in automated environments