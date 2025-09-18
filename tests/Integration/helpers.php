<?php

/**
 * Integration Test Helpers
 */

/**
 * Get a unique test identifier for parallel test isolation
 */
function getTestIdentifier(): string
{
    // Use process ID and timestamp for uniqueness
    $processId = getmypid();
    $timestamp = hrtime(true); // High-resolution timestamp

    return "test_{$processId}_{$timestamp}";
}

/**
 * Get a unique terminal title for parallel testing
 */
function getUniqueTerminalTitle(string $baseName = 'Test Terminal'): string
{
    return $baseName.' - '.getTestIdentifier();
}

/**
 * Add random delay to prevent parallel test conflicts
 */
function addParallelTestDelay(int $minMs = 100, int $maxMs = 1000): void
{
    $delay = mt_rand($minMs, $maxMs) * 1000; // Convert to microseconds
    usleep($delay);
}

/**
 * Retry function for handling parallel test conflicts
 */
function retryOnConflict(callable $callback, int $maxRetries = 3, int $delayMs = 500): mixed
{
    $attempt = 0;

    while ($attempt < $maxRetries) {
        try {
            return $callback();
        } catch (\Exception $e) {
            $attempt++;

            // Check if this is a resource conflict error
            if (str_contains($e->getMessage(), '422') ||
                str_contains($e->getMessage(), 'conflict') ||
                str_contains($e->getMessage(), 'busy')) {

                if ($attempt >= $maxRetries) {
                    throw $e;
                }

                // Add exponential backoff delay
                $delay = $delayMs * pow(2, $attempt - 1);
                usleep($delay * 1000);

                continue;
            }

            // Re-throw non-conflict errors immediately
            throw $e;
        }
    }

    throw new \Exception('Retry logic failed unexpectedly');
}

/**
 * Check if we have valid Fortis credentials for integration testing
 */
function hasValidFortisCredentials(): bool
{
    $requiredEnvVars = [
        'FORTIS_INTEGRATION_USER_ID',
        'FORTIS_INTEGRATION_USER_API_KEY',
        'FORTIS_INTEGRATION_DEVELOPER_ID',
        'FORTIS_INTEGRATION_LOCATION_ID',
        'FORTIS_INTEGRATION_PRODUCT_TRANSACTION_ID',
    ];

    foreach ($requiredEnvVars as $envVar) {
        if (empty(env($envVar))) {
            return false;
        }
    }

    return true;
}

/**
 * Set up real Fortis configuration for integration testing
 */
function setupIntegrationConfig(): void
{
    config()->set('lunar-fortis.environment', env('FORTIS_INTEGRATION_ENVIRONMENT', 'sandbox'));
    config()->set('lunar-fortis.debug', env('FORTIS_INTEGRATION_DEBUG', true));
    config()->set('services.fortis', [
        'userId' => env('FORTIS_INTEGRATION_USER_ID'),
        'userApiKey' => env('FORTIS_INTEGRATION_USER_API_KEY'),
        'developerId' => env('FORTIS_INTEGRATION_DEVELOPER_ID'),
        'locationId' => env('FORTIS_INTEGRATION_LOCATION_ID'),
        'productTransactionId' => env('FORTIS_INTEGRATION_PRODUCT_TRANSACTION_ID'),
        'terminalProductTransactionId' => env('FORTIS_INTEGRATION_TERMINAL_PRODUCT_TRANSACTION_ID'),
    ]);
}

/**
 * Set up database for terminal integration tests
 */
function setupIntegrationDatabase(): void
{
    // Create basic tables needed for integration tests
    if (! app('db')->getSchemaBuilder()->hasTable('fortis_terminals')) {
        app('db')->getSchemaBuilder()->create('fortis_terminals', function ($table) {
            $table->id();
            $table->string('fortis_id')->nullable();
            $table->string('location_id')->nullable();
            $table->string('title')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('terminal_application_id')->nullable();
            $table->string('terminal_manufacturer_code')->nullable();
            $table->string('default_product_transaction_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('fortis_created_at')->nullable();
            $table->timestamp('fortis_modified_at')->nullable();
            $table->string('created_user_id')->nullable();
            $table->string('modified_user_id')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->json('fortis_data')->nullable();
            $table->timestamps();
        });
    }
}

/**
 * Check if test card configuration is available
 */
function hasTestCardConfiguration(): bool
{
    $testCardVars = [
        'FORTIS_TEST_CARD_NUMBER',
        'FORTIS_TEST_CARD_MONTH',
        'FORTIS_TEST_CARD_YEAR',
        'FORTIS_TEST_CARD_CVV',
    ];

    foreach ($testCardVars as $var) {
        if (empty(env($var))) {
            return false;
        }
    }

    return true;
}
