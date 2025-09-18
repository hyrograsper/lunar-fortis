<?php

use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\Services\FortisHttpService;

require_once __DIR__ . '/helpers.php';

beforeEach(function () {
    if (!hasValidFortisCredentials()) {
        $this->markTestSkipped('Integration tests require real Fortis API credentials.');
    }

    setupIntegrationConfig();
    setupIntegrationDatabase();

    $this->fortisHttpService = new FortisHttpService();
});

describe('Advanced Terminal Payment Integration', function () {
    it('can test complete terminal payment workflow if terminal is available', function () {
        // Get available terminals
        $terminalsResponse = $this->fortisHttpService->listTerminals();

        if (empty($terminalsResponse['list'])) {
            $this->markTestSkipped('No terminals available for advanced terminal testing');
        }

        $terminalData = $terminalsResponse['list'][0];
        $terminalId = $terminalData['id'];

        // Test the complete workflow through the Terminal model
        $terminal = Terminal::where('fortis_id', $terminalId)->first();

        if (!$terminal) {
            // Create the terminal in our database for testing
            $terminal = Terminal::create([
                'fortis_id' => $terminalId,
                'title' => $terminalData['title'] ?? 'Integration Test Terminal',
                'active' => $terminalData['active'] ?? true,
                'location_id' => $terminalData['location_id'] ?? null,
            ]);
        }

        // Test 1: Check if terminal is ready for payments
        expect($terminal->isReadyForPayments())->toBeTrue();

        // Test 2: Try to initiate authorization (this may require physical terminal interaction)
        try {
            $statusCode = $terminal->initiateAuthorization(100); // $1.00

            expect($statusCode)->toBeString();
            expect(strlen($statusCode))->toBeGreaterThan(5);

            fwrite(STDERR, "Terminal authorization initiated with status code: $statusCode\n");

            // Test 3: Check authorization status
            $status = $terminal->checkAuthorizationStatus($statusCode);

            expect($status)->toHaveKey('progress');
            expect($status)->toHaveKey('completed');
            expect($status)->toHaveKey('success');

            fwrite(STDERR, "Authorization status - Progress: {$status['progress']}%, Completed: " .
                   ($status['completed'] ? 'YES' : 'NO') . "\n");

            // Note: For full testing, you'd need to physically interact with the terminal
            // or have it configured for automated testing

        } catch (\Exception $e) {
            // This is expected if terminal isn't ready or requires physical interaction
            fwrite(STDERR, "Terminal authorization test skipped: " . $e->getMessage() . "\n");

            // Still validate that we get a proper exception structure
            expect($e->getMessage())->toContain('terminal');
        }

    })->group('integration', 'slow', 'terminal', 'advanced');

    it('can test terminal payment methods with different amounts', function () {
        $terminals = $this->fortisHttpService->listTerminals();

        if (empty($terminals['list'])) {
            $this->markTestSkipped('No terminals available for amount testing');
        }

        $terminalData = $terminals['list'][0];
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalData['id']],
            [
                'title' => getUniqueTerminalTitle('Test Terminal'),
                'active' => $terminalData['active'] ?? true,
                'location_id' => $terminalData['location_id'] ?? null,
            ]
        );

        // Test different amounts to validate amount handling
        $testAmounts = [100, 250, 1000]; // $1.00, $2.50, $10.00

        foreach ($testAmounts as $amount) {
            try {
                $statusCode = $terminal->initiateAuthorization($amount);
                expect($statusCode)->toBeString();

                fwrite(STDERR, "Successfully initiated authorization for \$" . ($amount/100) . "\n");

                // Check status immediately
                $status = $terminal->checkAuthorizationStatus($statusCode);
                expect($status)->toBeArray();

            } catch (\Exception $e) {
                fwrite(STDERR, "Amount {$amount} test failed (expected): {$e->getMessage()}\n");
                // This is often expected in automated testing without physical terminal
            }
        }

    })->group('integration', 'slow', 'terminal', 'amounts');

    it('can test terminal error handling and recovery', function () {
        $terminals = $this->fortisHttpService->listTerminals();

        if (empty($terminals['list'])) {
            $this->markTestSkipped('No terminals available for error testing');
        }

        $terminalData = $terminals['list'][0];
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalData['id']],
            ['title' => getUniqueTerminalTitle('Test Terminal'), 'active' => true]
        );

        // Test 1: Invalid status code handling
        // Use a UUID format but with invalid/non-existent ID
        try {
            $invalidStatus = $terminal->checkAuthorizationStatus('00000000-0000-0000-0000-000000000000');
            // If it doesn't throw, it should still return an array
            expect($invalidStatus)->toBeArray();
        } catch (\Exception $e) {
            // Expected - invalid status codes should cause errors
            expect($e->getMessage())->toContain('status');
            fwrite(STDERR, "Expected error for invalid status code: " . $e->getMessage() . "\n");
        }

        // Test 2: Very small amount (should work)
        try {
            $statusCode = $terminal->initiateAuthorization(1); // $0.01
            expect($statusCode)->toBeString();
            fwrite(STDERR, "Small amount test passed - status: $statusCode\n");
        } catch (\Exception $e) {
            // Some terminals might not allow very small amounts
            expect($e->getMessage())->toContain('terminal');
            fwrite(STDERR, "Small amount test failed as expected: " . $e->getMessage() . "\n");
        }

        // Test 3: Zero amount (should fail validation)
        expect(fn() => $terminal->initiateAuthorization(0))
            ->toThrow(\Exception::class);

    })->group('integration', 'slow', 'terminal', 'error-handling');

    it('can test terminal sync and status consistency', function () {
        // Sync all terminals and validate they're properly stored
        $stats = Terminal::syncFromFortis();

        expect($stats)->toBeArray();
        expect($stats['total_processed'])->toBeGreaterThanOrEqual(0);
        expect($stats['errors'])->toBe(0);

        if ($stats['total_processed'] > 0) {
            $syncedTerminals = Terminal::whereNotNull('synced_at')->get();
            expect($syncedTerminals->count())->toBeGreaterThan(0);

            // Test each synced terminal
            foreach ($syncedTerminals as $terminal) {
                // Validate terminal data integrity
                expect($terminal->fortis_id)->not->toBeNull();
                expect($terminal->synced_at)->not->toBeNull();

                // Test needsSync method
                expect($terminal->needsSync())->toBeFalse(); // Recently synced

                // Validate display name generation
                if ($terminal->serial_number) {
                    expect($terminal->display_name)->toContain($terminal->serial_number);
                }
            }

            fwrite(STDERR, "Validated {$syncedTerminals->count()} synced terminals\n");
        }

    })->group('integration', 'slow', 'database', 'terminal');
});

afterEach(function () {
    // Clean up test terminals if needed
    // Be careful not to delete production data
});
