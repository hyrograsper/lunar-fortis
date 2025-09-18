# Integration Tests

These integration tests verify that the LunarFortis package works correctly with the actual Fortis API. They are separate from unit tests and require real Fortis API credentials to run.

## Prerequisites

To run integration tests, you need:

1. A Fortis sandbox or production account
2. Valid API credentials
3. Optionally, configured terminals for terminal-related tests

## Configuration

Set the following environment variables with your real Fortis API credentials:

```bash
# Required for all integration tests
FORTIS_INTEGRATION_USER_ID=your-user-id
FORTIS_INTEGRATION_USER_API_KEY=your-api-key
FORTIS_INTEGRATION_DEVELOPER_ID=your-developer-id
FORTIS_INTEGRATION_LOCATION_ID=your-location-id
FORTIS_INTEGRATION_PRODUCT_TRANSACTION_ID=your-product-transaction-id

# Optional - for terminal-specific tests
FORTIS_INTEGRATION_TERMINAL_PRODUCT_TRANSACTION_ID=your-terminal-product-id

# Optional - defaults to 'sandbox'
FORTIS_INTEGRATION_ENVIRONMENT=sandbox

# Optional - defaults to true
FORTIS_INTEGRATION_DEBUG=true

# Optional - for payment processing tests (if direct API methods are implemented)
FORTIS_TEST_CARD_NUMBER=4111111111111111
FORTIS_TEST_CARD_MONTH=12
FORTIS_TEST_CARD_YEAR=2027
FORTIS_TEST_CARD_CVV=999
```

## Running Integration Tests

### Run All Integration Tests
```bash
vendor/bin/pest --group=integration
```

### Run Only Fast Integration Tests (excludes slow API calls)
```bash
vendor/bin/pest --group=integration --exclude-group=slow
```

### Run Only Terminal Integration Tests
```bash
vendor/bin/pest --group=terminal
```

### Run Only Payment Processing Tests
```bash
vendor/bin/pest --group=payment
```

### Run Only Database Integration Tests
```bash
vendor/bin/pest --group=database
```

### Run Advanced Terminal Tests
```bash
vendor/bin/pest --group=advanced
```

### Run Integration Tests with Verbose Output
```bash
vendor/bin/pest --group=integration --verbose
```

## Test Groups

Integration tests are organized into groups:

- **integration**: All integration tests
- **slow**: Tests that make real API calls (may take time)
- **terminal**: Tests requiring terminal setup
- **database**: Tests that require database operations
- **payment**: Tests that process actual payments (requires test cards)
- **advanced**: Advanced terminal workflow tests
- **elements**: Tests for Fortis Elements payment flow
- **error-handling**: Tests for error scenarios and edge cases

## Test Behavior

### Automatic Skipping
If integration credentials are not provided, tests will automatically skip with an informative message:

```
Integration tests require real Fortis API credentials. Set FORTIS_INTEGRATION_* environment variables to run these tests.
```

### Error Handling
Integration tests are designed to:
- Handle API rate limits gracefully
- Skip tests when required resources (like terminals) are not available
- Provide clear error messages for debugging

## Available Integration Tests

### FortisHttpServiceIntegrationTest
Tests the core HTTP service functionality:
- Client token creation
- Terminal listing and retrieval
- Transaction intention creation and retrieval
- Error handling

### TerminalSyncIntegrationTest
Tests terminal synchronization functionality:
- Bulk terminal sync from Fortis API
- Single terminal sync
- Sync status checking
- Database persistence

## Development Tips

### Creating New Integration Tests
1. Extend `IntegrationTestCase` instead of the regular `TestCase`
2. Use `$this->skipIfNoCredentials()` to skip when credentials are missing
3. Add appropriate test groups: `->group('integration', 'slow')`
4. Handle cases where API resources might not be available

### Environment-Specific Considerations
- **Sandbox**: Safe for testing, may have limited data
- **Production**: Use with extreme caution, only for critical validation

### Best Practices
- Use small transaction amounts (e.g., $1.00) for testing
- Clean up test data when possible
- Be mindful of API rate limits
- Group related assertions together

## Troubleshooting

### Common Issues

**Tests are skipped:**
- Verify all required `FORTIS_INTEGRATION_*` environment variables are set
- Check that credentials are valid and have proper permissions

**API errors:**
- Verify the `FORTIS_INTEGRATION_ENVIRONMENT` setting matches your credentials
- Check that your Fortis account has the necessary permissions
- Ensure location and product IDs are correct

**Terminal tests fail:**
- Verify your location has terminals configured
- Check terminal permissions in your Fortis account

**Rate limiting:**
- Add delays between tests if needed
- Consider running tests with `--slow` option

### Getting Help
If integration tests fail consistently:
1. Check your Fortis dashboard for API logs
2. Verify account permissions and settings
3. Contact Fortis support if API issues persist
