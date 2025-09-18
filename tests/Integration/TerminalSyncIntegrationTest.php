<?php

use Hyrograsper\LunarFortis\Models\Terminal;

require_once __DIR__.'/helpers.php';

beforeEach(function () {
    // Check if we have real Fortis credentials configured for integration testing
    if (! hasValidFortisCredentials()) {
        $this->markTestSkipped('Integration tests require real Fortis API credentials. Set FORTIS_INTEGRATION_* environment variables to run these tests.');
    }

    // Set up real Fortis configuration for integration testing
    setupIntegrationConfig();

    // Set up database for terminal tests
    setupIntegrationDatabase();
});

describe('Terminal Sync Integration Tests', function () {
    it('can sync terminals from Fortis API', function () {
        // Get initial terminal count
        $initialCount = Terminal::count();

        // Sync terminals from Fortis API
        $stats = Terminal::syncFromFortis();

        expect($stats)->toBeArray();
        expect($stats)->toHaveKeys(['created', 'updated', 'errors', 'total_processed']);
        expect($stats['errors'])->toBe(0); // Should be no errors with valid credentials

        // Check that terminals were processed
        if ($stats['total_processed'] > 0) {
            expect(Terminal::count())->toBeGreaterThan($initialCount);

            // Verify a terminal was properly synced
            $syncedTerminal = Terminal::latest('synced_at')->first();
            expect($syncedTerminal)->not->toBeNull();
            expect($syncedTerminal->fortis_id)->not->toBeNull();
            expect($syncedTerminal->synced_at)->not->toBeNull();
        }
    })->group('integration', 'slow', 'database');

    it('can sync a single terminal from Fortis API', function () {
        // First get the list of available terminals
        $lunarFortis = app(\Hyrograsper\LunarFortis\LunarFortis::class);
        $terminalsList = $lunarFortis->listTerminals();

        if (empty($terminalsList['list'])) {
            $this->markTestSkipped('No terminals available for single terminal sync test.');
        }

        $fortisTerminal = $terminalsList['list'][0];
        $fortisId = $fortisTerminal['id'];

        // Delete any existing terminal with this ID to test creation
        Terminal::where('fortis_id', $fortisId)->delete();

        // Sync the single terminal
        $terminal = Terminal::syncSingleFromFortis($fortisId);

        expect($terminal)->not->toBeNull();
        expect($terminal->fortis_id)->toBe($fortisId);
        expect($terminal->synced_at)->not->toBeNull();
        expect($terminal->title)->not->toBeEmpty();

        // Verify it was saved to database
        $dbTerminal = Terminal::where('fortis_id', $fortisId)->first();
        expect($dbTerminal)->not->toBeNull();
        expect($dbTerminal->id)->toBe($terminal->id);
    })->group('integration', 'slow', 'database');

    it('returns null when syncing non-existent terminal', function () {
        // The API returns a 412 error for non-existent terminals,
        // which the syncSingleFromFortis method should handle by returning null
        $result = Terminal::syncSingleFromFortis('non-existent-terminal-id');

        expect($result)->toBeNull();
    })->group('integration', 'slow', 'database');

    it('can check if terminals need sync', function () {
        // First sync a terminal so we have one to work with
        $stats = Terminal::syncFromFortis();

        if ($stats['total_processed'] === 0) {
            $this->markTestSkipped('No terminals available for sync check test.');
        }

        // Get a recently synced terminal
        $terminal = Terminal::latest('synced_at')->first();
        expect($terminal)->not->toBeNull();

        // A recently synced terminal should not need sync
        expect($terminal->needsSync())->toBeFalse();

        // Manually set sync time to old date using Carbon explicitly
        $terminal->synced_at = \Carbon\Carbon::now()->subHours(25);
        $terminal->save();
        $terminal->refresh(); // Refresh to get the updated model with proper casting

        // Now it should need sync
        expect($terminal->needsSync())->toBeTrue();

        // Test with null synced_at
        $terminal->synced_at = null;
        $terminal->save();
        $terminal->refresh();
        expect($terminal->needsSync())->toBeTrue();
    })->group('integration', 'slow', 'database');
});
