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

describe('Terminal Authorization and Capture Integration', function () {
    it('can complete a full authorize and capture workflow', function () {
        // Get available terminals
        $terminalsResponse = $this->fortisHttpService->listTerminals();

        if (empty($terminalsResponse['list'])) {
            $this->markTestSkipped('No terminals available for authorize/capture testing');
        }

        $terminalData = $terminalsResponse['list'][0];
        $terminalId = $terminalData['id'];

        // Create/get the terminal model with unique title for parallel testing
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalId],
            [
                'title' => getUniqueTerminalTitle('Auth/Capture Test Terminal'),
                'active' => $terminalData['active'] ?? true,
                'location_id' => $terminalData['location_id'] ?? null,
            ]
        );

        expect($terminal->isReadyForPayments())->toBeTrue();

        // Step 1: Initiate Authorization
        fwrite(STDERR, "=== Starting Authorization/Capture Test ===\n");

        // Add small delay to prevent parallel conflicts
        addParallelTestDelay();

        $testAmount = 150; // $1.50
        $statusCode = retryOnConflict(function() use ($terminal, $testAmount) {
            return $terminal->initiateAuthorization($testAmount);
        });

        expect($statusCode)->toBeString();
        expect(strlen($statusCode))->toBeGreaterThan(10); // Should be a UUID

        fwrite(STDERR, "Authorization initiated with status code: $statusCode\n");

        // Step 2: Wait for authorization to complete
        fwrite(STDERR, "Waiting for authorization to complete...\n");

        $authResult = $terminal->waitForAuthorization($statusCode, 60, 3); // 60 second timeout, 3 second polling

        expect($authResult)->toBeArray();
        expect($authResult)->toHaveKey('completed');
        expect($authResult)->toHaveKey('success');
        expect($authResult)->toHaveKey('progress');

        fwrite(STDERR, "Authorization result - Progress: {$authResult['progress']}%, Completed: " .
               ($authResult['completed'] ? 'YES' : 'NO') . ", Success: " .
               ($authResult['success'] ? 'YES' : 'NO') . "\n");

        if (!$authResult['completed']) {
            $this->markTestSkipped('Authorization did not complete within timeout - may require physical terminal interaction');
        }

        if (!$authResult['success']) {
            $this->markTestSkipped('Authorization was not successful - may have been declined or cancelled');
        }

        // Step 3: Get transaction ID from the completed authorization
        expect($authResult)->toHaveKey('transaction_id');
        $transactionId = $authResult['transaction_id'];

        expect($transactionId)->toBeString();
        expect(strlen($transactionId))->toBeGreaterThan(10);

        fwrite(STDERR, "Authorization successful! Transaction ID: $transactionId\n");

        // Step 4: Capture the authorized transaction
        fwrite(STDERR, "Capturing transaction for \$" . ($testAmount/100) . "...\n");

        $captureResult = $terminal->captureTransaction($transactionId, $testAmount, [
            'order_number' => 'TEST-AUTH-CAPTURE-' . time(),
            'description' => 'Integration test auth/capture'
        ]);

        expect($captureResult)->toBeArray();
        expect($captureResult)->toHaveKey('data');

        $captureData = $captureResult['data'];
        expect($captureData)->toHaveKey('id');
        expect($captureData)->toHaveKey('status_code');

        $capturedTransactionId = $captureData['id'];
        $statusCode = $captureData['status_code'];

        fwrite(STDERR, "Capture successful! Captured Transaction ID: $capturedTransactionId, Status Code: $statusCode\n");

        // Verify the status code indicates success
        // Status code 101 = Approved, 102 = Auth Only, etc.
        expect($statusCode)->toBeIn([101, 102]); // Should be approved

        fwrite(STDERR, "=== Authorization/Capture Test Completed Successfully ===\n");

    })->group('integration', 'slow', 'terminal', 'auth-capture'); // Full authorization/capture flow

    it('can handle authorization timeout scenarios', function () {
        $terminals = $this->fortisHttpService->listTerminals();

        if (empty($terminals['list'])) {
            $this->markTestSkipped('No terminals available for timeout testing');
        }

        $terminalData = $terminals['list'][0];
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalData['id']],
            ['title' => getUniqueTerminalTitle('Timeout Test Terminal'), 'active' => true]
        );

        // Initiate authorization with retry logic
        addParallelTestDelay();
        $statusCode = retryOnConflict(function() use ($terminal) {
            return $terminal->initiateAuthorization(100); // $1.00
        });
        expect($statusCode)->toBeString();

        fwrite(STDERR, "Testing short timeout scenario with status: $statusCode\n");

        // Use a very short timeout to test timeout handling
        $result = $terminal->waitForAuthorization($statusCode, 5, 1); // 5 second timeout

        expect($result)->toBeArray();
        expect($result)->toHaveKey('timed_out');

        if ($result['timed_out']) {
            fwrite(STDERR, "Timeout test successful - transaction timed out as expected\n");
            expect($result['timed_out'])->toBeTrue();
        } else {
            fwrite(STDERR, "Transaction completed quickly - timeout test not applicable\n");
            expect($result['completed'])->toBeTrue();
        }

    })->group('integration', 'slow', 'terminal', 'timeout');

    it('can check authorization status during processing', function () {
        $terminals = $this->fortisHttpService->listTerminals();

        if (empty($terminals['list'])) {
            $this->markTestSkipped('No terminals available for status check testing');
        }

        $terminalData = $terminals['list'][0];
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalData['id']],
            ['title' => getUniqueTerminalTitle('Status Check Terminal'), 'active' => true]
        );

        // Initiate authorization with retry logic
        addParallelTestDelay();
        $statusCode = retryOnConflict(function() use ($terminal) {
            return $terminal->initiateAuthorization(200); // $2.00
        });
        expect($statusCode)->toBeString();

        fwrite(STDERR, "Checking authorization status progression for: $statusCode\n");

        // Check status multiple times to see progression
        $maxChecks = 5;
        $checkCount = 0;

        do {
            $checkCount++;
            $status = $terminal->checkAuthorizationStatus($statusCode);

            expect($status)->toBeArray();
            expect($status)->toHaveKey('progress');
            expect($status)->toHaveKey('completed');

            $progress = $status['progress'] ?? 0;
            $completed = $status['completed'] ?? false;

            fwrite(STDERR, "Status check #$checkCount - Progress: $progress%, Completed: " .
                   ($completed ? 'YES' : 'NO') . "\n");

            if ($completed) {
                break;
            }

            if ($checkCount < $maxChecks) {
                sleep(2); // Wait 2 seconds between checks
            }

        } while ($checkCount < $maxChecks && !$completed);

        // Final verification
        expect($status['progress'])->toBeGreaterThanOrEqual(0);
        expect($status['progress'])->toBeLessThanOrEqual(100);

        fwrite(STDERR, "Status check test completed\n");

    })->group('integration', 'slow', 'terminal', 'status-check');
});

afterEach(function () {
    // Clean up - in a real environment you might want to void uncaptured authorizations
    // But for testing, we'll let them expire naturally
});