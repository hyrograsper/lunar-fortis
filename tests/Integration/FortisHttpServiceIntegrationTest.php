<?php

use Hyrograsper\LunarFortis\Services\FortisHttpService;
use Hyrograsper\LunarFortis\LunarFortis;
use Illuminate\Support\Facades\Http;

require_once __DIR__ . '/helpers.php';

beforeEach(function () {
    // Check if we have real Fortis credentials configured for integration testing
    if (!hasValidFortisCredentials()) {
        $this->markTestSkipped('Integration tests require real Fortis API credentials. Set FORTIS_INTEGRATION_* environment variables to run these tests.');
    }

    // Set up real Fortis configuration for integration testing
    setupIntegrationConfig();

    $this->fortisHttpService = new FortisHttpService();
    $this->lunarFortis = new LunarFortis();
});

describe('Fortis API Integration Tests', function () {
    it('can authenticate with Fortis API and create a client token', function () {
        $clientToken = $this->lunarFortis->getClientTokenForSaleAmount(1000, 'sale');

        expect($clientToken)->toBeString();
        expect(strlen($clientToken))->toBeGreaterThan(10);
    })->group('integration', 'slow');

    it('can retrieve terminals from Fortis API', function () {
        $response = $this->fortisHttpService->listTerminals();

        expect($response)->toBeArray();
        expect($response)->toHaveKey('list');
        expect($response['list'])->toBeArray();

        // If there are terminals, verify their structure
        if (count($response['list']) > 0) {
            $terminal = $response['list'][0];
            expect($terminal)->toHaveKey('id');
            expect($terminal)->toHaveKey('title');
            expect($terminal)->toHaveKey('active');
        }
    })->group('integration', 'slow');

    it('can create a transaction intention for Elements', function () {
        $response = $this->fortisHttpService->createTransactionIntention(100, 'sale'); // $1.00 in cents

        expect($response)->toBeArray();
        expect($response)->toHaveKey('data');
        expect($response['data'])->toHaveKey('client_token');
        expect($response['data'])->toHaveKey('action');
        expect($response['data'])->toHaveKey('amount');

        // Verify the correct values
        expect($response['data']['action'])->toBe('sale');
        expect($response['data']['amount'])->toBe(100);
        expect($response['data']['client_token'])->toBeString();
        expect(strlen($response['data']['client_token']))->toBeGreaterThan(100); // JWT tokens are long
    })->group('integration', 'slow');

    it('handles API errors gracefully', function () {
        // Try to get a non-existent transaction
        expect(fn() => $this->fortisHttpService->getTransaction('invalid-transaction-id'))
            ->toThrow(Exception::class);
    })->group('integration', 'slow');

    it('can handle location-based operations', function () {
        $locationId = config('services.fortis.locationId');

        // This test verifies that our configured location is valid
        // by attempting to create a client token (which requires a valid location)
        $clientToken = $this->lunarFortis->getClientTokenForSaleAmount(500, 'sale');

        expect($clientToken)->toBeString();
        expect(strlen($clientToken))->toBeGreaterThan(10);
    })->group('integration', 'slow');
});

describe('Fortis Terminal Integration Tests', function () {
    it('can perform terminal operations if terminals are available', function () {
        $terminals = $this->fortisHttpService->listTerminals();

        if (empty($terminals['list'])) {
            $this->markTestSkipped('No terminals available for testing terminal operations.');
        }

        $terminal = $terminals['list'][0];
        $terminalId = $terminal['id'];

        // Try to get the terminal details
        $terminalResponse = $this->fortisHttpService->getTerminal($terminalId);

        expect($terminalResponse)->toBeArray();
        expect($terminalResponse)->toHaveKey('data');
        expect($terminalResponse['data']['id'])->toBe($terminalId);
    })->group('integration', 'slow', 'terminal');
});

afterEach(function () {
    // Clean up any test data if needed
    Http::fake(); // Reset any HTTP fakes that might interfere
});