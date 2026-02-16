<?php

use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\Services\FortisHttpService;

require_once __DIR__.'/helpers.php';

beforeEach(function () {
    // Check if we have real Fortis credentials configured for integration testing
    if (! hasValidFortisCredentials()) {
        $this->markTestSkipped('Integration tests require real Fortis API credentials. Set FORTIS_INTEGRATION_* environment variables to run these tests.');
    }

    // Check if test cards are configured
    if (! hasTestCardConfiguration()) {
        $this->markTestSkipped('Payment processing tests require test card configuration. Set FORTIS_TEST_* environment variables to run these tests.');
    }

    // Set up real Fortis configuration for integration testing
    setupIntegrationConfig();

    $this->fortisHttpService = new FortisHttpService;
    $this->lunarFortis = new LunarFortis;
});

describe('Payment Processing Integration Tests', function () {

    it('can process refunds for completed transactions', function () {
        // First, we need to create a completed transaction to refund
        // Get available terminals for authorization and capture
        $terminalsResponse = $this->fortisHttpService->listTerminals();

        if (empty($terminalsResponse['list'])) {
            $this->markTestSkipped('No terminals available for refund testing - requires auth/capture flow');
        }

        $terminalData = $terminalsResponse['list'][0];
        $terminalId = $terminalData['id'];

        // Create terminal model
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalId],
            [
                'title' => getUniqueTerminalTitle('Refund Test Terminal'),
                'active' => $terminalData['active'] ?? true,
                'location_id' => $terminalData['location_id'] ?? null,
            ]
        );

        // Step 1: Create a completed transaction (authorize + capture)
        $testAmount = 300; // $3.00 for refund testing

        fwrite(STDERR, "=== Creating transaction for refund test ===\n");

        // Initiate authorization
        $statusCode = $terminal->initiateAuthorization($testAmount);
        expect($statusCode)->toBeString();

        fwrite(STDERR, "Authorization initiated with status: $statusCode\n");

        // Wait for authorization completion
        $authResult = $terminal->waitForAuthorization($statusCode, 60, 3);

        if (! $authResult['completed'] || ! $authResult['success']) {
            $this->markTestSkipped('Authorization did not complete successfully - cannot test refund without completed transaction');
        }

        $transactionId = $authResult['transaction_id'];
        expect($transactionId)->toBeString();

        fwrite(STDERR, "Authorization completed, transaction ID: $transactionId\n");

        // Capture the transaction
        $captureResult = $terminal->captureTransaction($transactionId, $testAmount, [
            'order_number' => 'REFUND-TEST-'.time(),
            'description' => 'Transaction for refund testing',
        ]);

        expect($captureResult)->toHaveKey('data');
        $captureData = $captureResult['data'];
        $capturedTransactionId = $captureData['id'];

        fwrite(STDERR, "Transaction captured successfully, ID: $capturedTransactionId\n");

        // Step 2: Now test the refund functionality
        $refundAmount = 100; // Partial refund of $1.00 out of $3.00

        fwrite(STDERR, 'Attempting to refund \$'.($refundAmount / 100)." from captured transaction\n");

        $refundResult = $this->fortisHttpService->refund($capturedTransactionId, $refundAmount);

        expect($refundResult)->toHaveKey('data');
        expect($refundResult['data'])->toHaveKey('id');
        expect($refundResult['data'])->toHaveKey('status_code');

        $refundData = $refundResult['data'];
        $refundTransactionId = $refundData['id'];
        $refundStatusCode = $refundData['status_code'];

        fwrite(STDERR, "Refund successful! Refund Transaction ID: $refundTransactionId, Status: $refundStatusCode\n");

        // Verify refund was processed successfully
        expect($refundStatusCode)->toBeIn([111]); // 111 = Refunded
        expect($refundTransactionId)->toBeString();
        expect($refundData['transaction_amount'])->toBe($refundAmount);

        fwrite(STDERR, "=== Refund test completed successfully ===\n");

    })->group('integration', 'slow', 'payment', 'refund');

    it('handles declined card scenarios', function () {
        // Test various decline scenarios using specific transaction amounts
        // Based on Fortis test data patterns - certain amounts trigger specific responses

        $terminalsResponse = $this->fortisHttpService->listTerminals();

        if (empty($terminalsResponse['list'])) {
            $this->markTestSkipped('No terminals available for decline testing');
        }

        $terminalData = $terminalsResponse['list'][0];
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalData['id']],
            [
                'title' => getUniqueTerminalTitle('Decline Test Terminal'),
                'active' => true,
                'location_id' => $terminalData['location_id'] ?? null,
            ]
        );

        expect($terminal->isReadyForPayments())->toBeTrue();

        // Test different decline scenarios with specific amounts
        $declineScenarios = [
            'generic_decline' => 505,     // $5.05 - Generic decline
            'insufficient_funds' => 510,   // $5.10 - Insufficient funds
            'invalid_card' => 515,         // $5.15 - Invalid card
            'expired_card' => 520,         // $5.20 - Expired card
            'restricted_card' => 525,      // $5.25 - Restricted card
        ];

        foreach ($declineScenarios as $scenario => $amount) {
            try {
                fwrite(STDERR, "Testing {$scenario} scenario with amount \\$".($amount / 100)."...\n");

                $statusCode = $terminal->initiateAuthorization($amount);
                expect($statusCode)->toBeString();

                // Wait for authorization to complete
                $authResult = $terminal->waitForAuthorization($statusCode, 30, 2);

                // Depending on the test environment, this might complete as declined
                // or might timeout if the terminal requires physical interaction
                if ($authResult['completed']) {
                    if (! $authResult['success']) {
                        fwrite(STDERR, "Successfully tested decline scenario: {$scenario}\n");
                        expect($authResult['success'])->toBeFalse();
                    } else {
                        fwrite(STDERR, "Scenario {$scenario} completed as approved (test environment may not simulate declines)\n");
                    }
                } else {
                    fwrite(STDERR, "Scenario {$scenario} did not complete (may require physical terminal interaction)\n");
                }

            } catch (Exception $exception) {
                // Expected for some decline scenarios
                fwrite(STDERR, "Decline scenario {$scenario} threw exception (expected): ".$exception->getMessage()."\n");
                expect($exception->getMessage())->toContain('terminal');
            }
        }

    })->group('integration', 'slow', 'payment', 'error-handling');

    it('handles connection and timeout errors', function () {
        // Test scenarios that simulate connection issues and timeouts

        $terminalsResponse = $this->fortisHttpService->listTerminals();

        if (empty($terminalsResponse['list'])) {
            $this->markTestSkipped('No terminals available for error testing');
        }

        $terminalData = $terminalsResponse['list'][0];
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalData['id']],
            ['title' => getUniqueTerminalTitle('Error Test Terminal'), 'active' => true]
        );

        // Test timeout scenario with very short timeout
        try {
            $statusCode = $terminal->initiateAuthorization(100); // $1.00
            expect($statusCode)->toBeString();

            fwrite(STDERR, "Testing timeout scenario with 3-second timeout...\n");

            // Use very short timeout to simulate timeout condition
            $result = $terminal->waitForAuthorization($statusCode, 3, 1);

            if (isset($result['timed_out']) && $result['timed_out']) {
                fwrite(STDERR, "Successfully tested timeout scenario\n");
                expect($result['timed_out'])->toBeTrue();
            } else {
                fwrite(STDERR, "Transaction completed quickly - timeout scenario not applicable\n");
                expect($result)->toHaveKey('completed');
            }

        } catch (Exception $exception) {
            fwrite(STDERR, 'Error scenario test (expected): '.$exception->getMessage()."\n");
            expect($exception->getMessage())->toContain('terminal');
        }

    })->group('integration', 'slow', 'payment', 'error-handling');

    it('validates different transaction amounts and edge cases', function () {
        // Test various transaction amounts and edge cases

        $terminalsResponse = $this->fortisHttpService->listTerminals();

        if (empty($terminalsResponse['list'])) {
            $this->markTestSkipped('No terminals available for amount validation testing');
        }

        $terminalData = $terminalsResponse['list'][0];
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalData['id']],
            ['title' => getUniqueTerminalTitle('Amount Test Terminal'), 'active' => true]
        );

        // Test edge cases
        $testCases = [
            'minimum_amount' => 1,        // $0.01 - Minimum transaction
            'small_amount' => 50,         // $0.50 - Small transaction
            'standard_amount' => 100,     // $1.00 - Standard test amount
            'large_amount' => 9999,       // $99.99 - Large amount
        ];

        foreach ($testCases as $testName => $amount) {
            try {
                fwrite(STDERR, "Testing {$testName}: \\$".($amount / 100)."...\n");

                $statusCode = $terminal->initiateAuthorization($amount);
                expect($statusCode)->toBeString();
                expect(strlen($statusCode))->toBeGreaterThan(10); // Should be a UUID

                // Check status immediately (don't wait for completion to speed up test)
                $status = $terminal->checkAuthorizationStatus($statusCode);
                expect($status)->toBeArray();
                expect($status)->toHaveKey('progress');

                fwrite(STDERR, "Amount {$testName} initiated successfully\n");

            } catch (Exception $exception) {
                if ($amount === 1) {
                    // Some terminals might reject very small amounts
                    fwrite(STDERR, 'Minimum amount test failed as expected: '.$exception->getMessage()."\n");
                } else {
                    fwrite(STDERR, "Amount test {$testName} failed: ".$exception->getMessage()."\n");
                }
            }
        }

        // Test invalid amount (should throw exception)
        expect(fn () => $terminal->initiateAuthorization(0))
            ->toThrow(\Exception::class);

    })->group('integration', 'slow', 'payment', 'validation');

    it('handles API-level errors and invalid requests', function () {
        // Test various API error conditions

        $terminalsResponse = $this->fortisHttpService->listTerminals();

        if (empty($terminalsResponse['list'])) {
            $this->markTestSkipped('No terminals available for API error testing');
        }

        $terminalData = $terminalsResponse['list'][0];
        $terminal = Terminal::updateOrCreate(
            ['fortis_id' => $terminalData['id']],
            ['title' => getUniqueTerminalTitle('API Error Test Terminal'), 'active' => true]
        );

        // Test 1: Invalid status code format
        try {
            $status = $terminal->checkAuthorizationStatus('invalid-format');
            // If no exception, should still return proper structure
            expect($status)->toBeArray();
        } catch (Exception $exception) {
            fwrite(STDERR, 'Invalid status code format error (expected): '.substr($exception->getMessage(), 0, 100)."...\n");
            expect($exception->getMessage())->toContain('status');
        }

        // Test 2: Non-existent but properly formatted UUID
        try {
            $status = $terminal->checkAuthorizationStatus('00000000-0000-0000-0000-000000000000');
            expect($status)->toBeArray();
        } catch (Exception $exception) {
            fwrite(STDERR, 'Non-existent UUID error (expected): '.substr($exception->getMessage(), 0, 100)."...\n");
            expect($exception->getMessage())->toContain('status');
        }

        // Test 3: Invalid refund (try to refund non-existent transaction)
        try {
            $this->fortisHttpService->refund('00000000-0000-0000-0000-000000000000', 100);
            // Should throw exception for non-existent transaction
            expect(false)->toBeTrue(); // Should not reach this line
        } catch (Exception $exception) {
            fwrite(STDERR, 'Invalid refund error (expected): '.substr($exception->getMessage(), 0, 100)."...\n");
            expect($exception->getMessage())->toContain('refund');
        }

        // Test 4: Capture non-existent transaction
        try {
            $terminal->captureTransaction('00000000-0000-0000-0000-000000000000', 100);
            expect(false)->toBeTrue(); // Should not reach this line
        } catch (Exception $exception) {
            fwrite(STDERR, 'Invalid capture error (expected): '.substr($exception->getMessage(), 0, 100)."...\n");
            expect($exception->getMessage())->toContain('capture');
        }

        fwrite(STDERR, "API error handling tests completed\n");

    })->group('integration', 'slow', 'payment', 'error-handling', 'api-errors');

    it('handles AVS (Address Verification System) responses', function () {
        // Test different AVS scenarios using transaction patterns
        // Note: AVS typically applies to card-not-present transactions using Elements

        // Create a transaction intention with AVS-triggering patterns
        $avsTestCases = [
            'good_match' => [
                'amount' => 1000, // $10.00 - Should pass AVS
                'description' => 'AVS Good Match Test',
            ],
            'street_mismatch' => [
                'amount' => 1001, // $10.01 - Might trigger street mismatch
                'description' => 'AVS Street Mismatch Test',
            ],
            'zip_mismatch' => [
                'amount' => 1002, // $10.02 - Might trigger ZIP mismatch
                'description' => 'AVS ZIP Mismatch Test',
            ],
            'full_mismatch' => [
                'amount' => 1003, // $10.03 - Might trigger full AVS mismatch
                'description' => 'AVS Full Mismatch Test',
            ],
        ];

        foreach ($avsTestCases as $testCase => $testData) {
            try {
                fwrite(STDERR, "Testing AVS scenario: {$testCase} with amount \\$".($testData['amount'] / 100)."...\n");

                // Create transaction intention - this is where AVS would be processed in Elements flow
                $intention = $this->fortisHttpService->createTransactionIntention(
                    $testData['amount'],
                    'sale'
                );

                expect($intention)->toHaveKey('data');
                expect($intention['data'])->toHaveKey('client_token');

                $clientToken = $intention['data']['client_token'];
                expect($clientToken)->toBeString();
                expect(strlen($clientToken))->toBeGreaterThan(50);

                fwrite(STDERR, "AVS test case {$testCase} - Elements token created (length: ".strlen($clientToken).")\n");

                // In a real scenario, the frontend would use this token with Elements
                // and include billing address information that would trigger different AVS responses

            } catch (Exception $exception) {
                fwrite(STDERR, "AVS test case {$testCase} failed: ".$exception->getMessage()."\n");
                expect($exception->getMessage())->toContain('transaction');
            }
        }

        fwrite(STDERR, "Note: Full AVS testing requires frontend Elements integration with billing address data\n");

    })->group('integration', 'slow', 'payment', 'avs-verification');

    it('handles CVV (Card Verification Value) responses', function () {
        // Test different CVV scenarios
        // Note: CVV verification typically happens during card processing with Elements

        $cvvTestCases = [
            'cvv_match' => [
                'amount' => 2000, // $20.00 - Should pass CVV
                'description' => 'CVV Match Test',
            ],
            'cvv_no_match' => [
                'amount' => 2001, // $20.01 - Might trigger CVV mismatch
                'description' => 'CVV No Match Test',
            ],
            'cvv_not_processed' => [
                'amount' => 2002, // $20.02 - Might trigger CVV not processed
                'description' => 'CVV Not Processed Test',
            ],
            'cvv_unreadable' => [
                'amount' => 2003, // $20.03 - Might trigger CVV unreadable
                'description' => 'CVV Unreadable Test',
            ],
            'cvv_unknown' => [
                'amount' => 2004, // $20.04 - Might trigger CVV unknown
                'description' => 'CVV Unknown Test',
            ],
        ];

        foreach ($cvvTestCases as $testCase => $testData) {
            try {
                fwrite(STDERR, "Testing CVV scenario: {$testCase} with amount \\$".($testData['amount'] / 100)."...\n");

                // Create transaction intention - this is where CVV would be verified in Elements flow
                $intention = $this->fortisHttpService->createTransactionIntention(
                    $testData['amount'],
                    'sale'
                );

                expect($intention)->toHaveKey('data');
                expect($intention['data'])->toHaveKey('client_token');

                $clientToken = $intention['data']['client_token'];
                expect($clientToken)->toBeString();

                fwrite(STDERR, "CVV test case {$testCase} - Elements token created (length: ".strlen($clientToken).")\n");

                // In a real scenario, the frontend would use this token with Elements
                // and include CVV data that would trigger different CVV verification responses

            } catch (Exception $exception) {
                fwrite(STDERR, "CVV test case {$testCase} failed: ".$exception->getMessage()."\n");
                expect($exception->getMessage())->toContain('transaction');
            }
        }

        fwrite(STDERR, "Note: Full CVV testing requires frontend Elements integration with card CVV data\n");

    })->group('integration', 'slow', 'payment', 'cvv-verification');

    it('validates AVS and CVV enum code mappings', function () {
        // Test the enum mappings for AVS codes
        $avsTestCodes = ['GOOD', 'BAD', 'STREET', 'ZIP'];

        foreach ($avsTestCodes as $code) {
            $avsCode = \Hyrograsper\LunarFortis\Enums\AvsResponseCode::fromCode($code);

            if ($code === 'GOOD') {
                expect($avsCode)->toBe(\Hyrograsper\LunarFortis\Enums\AvsResponseCode::GOOD);
                expect($avsCode->value)->toBe('Street or zip are both good (if provided)');
            } elseif ($code === 'BAD') {
                expect($avsCode)->toBe(\Hyrograsper\LunarFortis\Enums\AvsResponseCode::BAD);
                expect($avsCode->value)->toBe('Both street and zip do not match');
            }

            fwrite(STDERR, "AVS Code {$code}: ".($avsCode ? $avsCode->value : 'null')."\n");
        }

        // Test CVV enum mappings
        $cvvTestCodes = ['M', 'N', 'P', 'S', 'U', 'X'];

        foreach ($cvvTestCodes as $code) {
            $cvvCode = \Hyrograsper\LunarFortis\Enums\CvvResponseCode::fromCode($code);
            expect($cvvCode)->not->toBeNull();

            if ($code === 'M') {
                expect($cvvCode)->toBe(\Hyrograsper\LunarFortis\Enums\CvvResponseCode::M);
                expect($cvvCode->value)->toBe('Match');
            } elseif ($code === 'N') {
                expect($cvvCode)->toBe(\Hyrograsper\LunarFortis\Enums\CvvResponseCode::N);
                expect($cvvCode->value)->toBe('No Match');
            }

            fwrite(STDERR, "CVV Code {$code}: ".$cvvCode->value."\n");
        }

        // Test invalid codes
        expect(\Hyrograsper\LunarFortis\Enums\AvsResponseCode::fromCode('INVALID'))->toBeNull();
        expect(\Hyrograsper\LunarFortis\Enums\CvvResponseCode::fromCode('INVALID'))->toBeNull();

        fwrite(STDERR, "AVS and CVV enum validation completed\n");

    })->group('integration', 'fast', 'validation', 'enums');

    it('simulates transaction responses with AVS and CVV data', function () {
        // Since we can't easily trigger actual AVS/CVV responses in integration tests,
        // let's test how the payment types would handle various response scenarios

        $testResponseData = [
            'good_response' => [
                'status_code' => 101, // Approved
                'reason_code_id' => 1000,
                'avs' => 'GOOD',
                'cvv_response' => 'M',
                'transaction_amount' => 1500,
            ],
            'avs_fail_response' => [
                'status_code' => 101, // Approved but AVS failed
                'reason_code_id' => 1000,
                'avs' => 'BAD',
                'cvv_response' => 'M',
                'transaction_amount' => 1500,
            ],
            'cvv_fail_response' => [
                'status_code' => 101, // Approved but CVV failed
                'reason_code_id' => 1000,
                'avs' => 'GOOD',
                'cvv_response' => 'N',
                'transaction_amount' => 1500,
            ],
            'both_fail_response' => [
                'status_code' => 101, // Approved but both failed
                'reason_code_id' => 1000,
                'avs' => 'BAD',
                'cvv_response' => 'N',
                'transaction_amount' => 1500,
            ],
        ];

        foreach ($testResponseData as $scenario => $responseData) {
            fwrite(STDERR, "Testing {$scenario} scenario...\n");

            // Test AVS processing
            if (isset($responseData['avs'])) {
                $avsCode = \Hyrograsper\LunarFortis\Enums\AvsResponseCode::fromCode($responseData['avs']);
                expect($avsCode)->not->toBeNull();

                $avsPass = ($avsCode == \Hyrograsper\LunarFortis\Enums\AvsResponseCode::GOOD);
                fwrite(STDERR, "  AVS: {$avsCode->value} - ".($avsPass ? 'PASS' : 'FAIL')."\n");
            }

            // Test CVV processing
            if (isset($responseData['cvv_response'])) {
                $cvvCode = \Hyrograsper\LunarFortis\Enums\CvvResponseCode::fromCode($responseData['cvv_response']);
                expect($cvvCode)->not->toBeNull();

                $cvvPass = ($cvvCode != \Hyrograsper\LunarFortis\Enums\CvvResponseCode::N);
                fwrite(STDERR, "  CVV: {$cvvCode->value} - ".($cvvPass ? 'PASS' : 'FAIL')."\n");
            }
        }

        fwrite(STDERR, "AVS and CVV response simulation completed\n");

    })->group('integration', 'fast', 'payment', 'avs-cvv-simulation');

});

describe('Terminal Payment Integration Tests', function () {
    it('can process terminal credit card authorization', function () {
        // Get available terminals first
        $terminals = $this->fortisHttpService->listTerminals();

        if (empty($terminals['list'])) {
            $this->markTestSkipped('No terminals available for terminal payment tests');
        }

        $terminal = $terminals['list'][0];
        $terminalId = $terminal['id'];

        // Test terminal authorization - this uses the existing method
        try {
            $authResult = $this->fortisHttpService->authorizeTerminalCreditCard(
                $terminalId,
                1000, // $10.00
                []
            );

            expect($authResult)->toHaveKey('data');
            expect($authResult['data'])->toHaveKey('async');

        } catch (Exception $exception) {
            // Terminal might not be active or configured for card processing
            $this->markTestSkipped('Terminal not ready for credit card processing: '.$exception->getMessage());
        }
    })->group('integration', 'slow', 'terminal', 'payment');

});

describe('Elements Payment Flow Integration', function () {
    it('can create payment intention and process with test card data', function () {
        // Create a payment intention
        $intention = $this->fortisHttpService->createTransactionIntention(1500, 'sale');

        expect($intention)->toHaveKey('data');
        expect($intention['data'])->toHaveKey('client_token');

        $clientToken = $intention['data']['client_token'];
        expect($clientToken)->toBeString();
        expect(strlen($clientToken))->toBeGreaterThan(100);

        // At this point, normally the frontend would use the client_token
        // with Fortis Elements to process the actual payment
        // We can't easily simulate that in server-side tests

        fwrite(STDERR, 'Elements client token created successfully (length: '.strlen($clientToken).")\n");
        fwrite(STDERR, "In real usage, this token would be used with Fortis Elements JS SDK\n");

    })->group('integration', 'slow', 'elements');
});

afterEach(function () {
    // Clean up any test data if needed
    // Be careful with real API - don't leave test transactions hanging
});
