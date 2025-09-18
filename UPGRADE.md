# Upgrade Guide

This guide covers breaking changes and migration steps when upgrading lunar-fortis.

## From SDK to REST API (v1.x to v2.x)

### Overview

Version 2.0 migrates from the problematic Fortis PHP SDK to direct REST API calls using Laravel's HTTP facade. This provides better reliability, error handling, and debugging capabilities.

### Breaking Changes

#### 1. SDK Dependency Removed

**Before:**
```json
{
    "require": {
        "hyrograsper/fortis-php-sdk": "v1.1.1"
    }
}
```

**After:**
```json
{
    "require": {
        "illuminate/http": "^10.0 || ^11.0 || ^12.0"
    }
}
```

#### 2. Terminal Payment Methods Renamed

All terminal payment methods have been updated to reflect the new auth-only flow:

| Old Method | New Method | Purpose |
|------------|------------|---------|
| `processPayment()` | `processCompletePayment()` | Complete payment (auth + capture) |
| `processPayment()` | `authorizePayment()` | Authorization only |
| `initiatePayment()` | `initiateAuthorization()` | Start auth, return status code |
| `checkPaymentStatus()` | `checkAuthorizationStatus()` | Check auth status by code |
| `waitForPayment()` | `waitForAuthorization()` | Wait for auth completion |

**Migration Example:**

```php
// OLD
$terminal = Terminal::find(1);
$result = $terminal->processPayment(1099, [
    'order_number' => 'ORD-12345'
]);

// NEW - Complete Payment
$result = $terminal->processCompletePayment(1099, [
    'order_number' => 'ORD-12345'
]);

// NEW - Authorization Only
$authResult = $terminal->authorizePayment(1099, [
    'order_number' => 'ORD-12345'
]);
if ($authResult['success']) {
    $transactionId = $authResult['transaction_id'];
    $captureResult = $terminal->captureTransaction($transactionId, 1099);
}
```

#### 3. LunarFortis Class Changes

| Old Method | New Method | Purpose |
|------------|------------|---------|
| `chargeTerminalCreditCard()` | `authorizeTerminalCreditCard()` | Terminal authorization |
| `processTerminalCreditCard()` | `processTerminalCreditCardAuth()` | Complete terminal auth workflow |

**Migration Example:**

```php
// OLD
$fortis = app(LunarFortis::class);
$result = $fortis->chargeTerminalCreditCard('terminal_123', 1099);

// NEW
$result = $fortis->authorizeTerminalCreditCard('terminal_123', 1099);
```

#### 4. FortisErrorHelper Removed

The `FortisErrorHelper` class has been completely removed as it's no longer needed with direct HTTP calls.

**Before:**
```php
use Hyrograsper\LunarFortis\Helpers\FortisErrorHelper;

$errors = FortisErrorHelper::parseApiException($exception);
```

**After:**
Error handling is now built into the HTTP service and payment types. Exceptions contain detailed error messages directly.

#### 5. Configuration Changes

Add new terminal-specific policy configuration:

```php
// config/lunar-fortis.php
return [
    'policy' => 'automatic',           // Default policy for regular payments
    'terminal_policy' => 'automatic',  // Policy specifically for terminal payments
    // ...
];
```

### New Features

#### 1. Enhanced HTTP Configuration & Retry System

- **Comprehensive HTTP Retry Logic**: Support for multiple status codes (429, 502, 503, 504)
- **Exponential Backoff**: Configurable backoff strategies with max delay caps
- **Flexible Timeout Configuration**: Adjustable request timeouts per environment
- **Network Resilience**: Connection error recovery and rate limiting handling
- **Environment-Specific Tuning**: Production-ready defaults with development overrides

**New Environment Variables:**
```env
# HTTP Configuration (all optional with sensible defaults)
FORTIS_HTTP_TIMEOUT=30                      # Request timeout in seconds
FORTIS_HTTP_RETRY_ATTEMPTS=3                # Number of retry attempts
FORTIS_HTTP_RETRY_DELAY=1000                # Delay between retries (ms)
FORTIS_HTTP_RETRY_CONNECTION=true           # Retry on connection errors
FORTIS_HTTP_RETRY_STATUS_CODES="429,502,503,504"  # HTTP status codes to retry on
FORTIS_HTTP_RETRY_EXPONENTIAL=false         # Use exponential backoff
FORTIS_HTTP_RETRY_MAX_DELAY=10000          # Max delay with exponential backoff (ms)
```

#### 2. Enhanced Error Handling

- Automatic retry logic for network errors and multiple HTTP status codes
- Detailed error logging with request/response data
- Better exception messages with Fortis API error details

#### 3. Configurable Payment Policies

Terminal payments now support configurable capture policies:

```php
// Automatic capture (default)
'terminal_policy' => 'automatic'

// Manual capture
'terminal_policy' => 'manual'
```

#### 4. Improved Filament Interface

The terminal management interface now includes:
- Payment flow selection (Complete vs Authorization Only)
- Better error messages and status feedback
- Enhanced test payment functionality

#### 5. New Capture Methods

```php
// New methods for manual capture workflow
$terminal->captureTransaction($transactionId, $amount);
$fortis->captureTerminalTransaction($transactionId, $amount);
```

### Migration Steps

1. **Update Method Calls**
   - Replace all `processPayment()` calls with `processCompletePayment()` or `authorizePayment()`
   - Replace `initiatePayment()` with `initiateAuthorization()`
   - Replace `checkPaymentStatus()` with `checkAuthorizationStatus()`
   - Replace `waitForPayment()` with `waitForAuthorization()`

2. **Remove FortisErrorHelper References**
   - Delete any imports of `FortisErrorHelper`
   - Replace error handling with standard exception handling

3. **Update Configuration**
   - Add `terminal_policy` configuration if you want different behavior for terminal vs regular payments

4. **Test Payment Flows**
   - Test both authorization-only and complete payment flows
   - Verify capture functionality works correctly
   - Test error handling and retry logic

### Compatibility Notes

- **No backward compatibility** - all deprecated methods have been removed
- Payment flow behavior may change if you were relying on direct sale transactions
- Error messages and exception types have changed
- Configuration structure remains the same except for new `terminal_policy` option

### Testing Your Migration

```php
// Test complete payment flow
$terminal = Terminal::where('active', true)->first();
$result = $terminal->processCompletePayment(100, [
    'description' => 'Migration test'
]);

// Test authorization + capture flow
$authResult = $terminal->authorizePayment(100, [
    'description' => 'Auth test'
]);

if ($authResult['success']) {
    $captureResult = $terminal->captureTransaction(
        $authResult['transaction_id'],
        100
    );
}
```

### Getting Help

If you encounter issues during migration:

1. Check the logs for detailed error messages
2. Verify your Fortis API credentials and configuration
3. Test with small amounts first
4. Review the [README.md](README.md) for updated examples
5. Report issues at https://github.com/hyrograsper/lunar-fortis/issues
