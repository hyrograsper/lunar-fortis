<?php

use Carbon\Carbon;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
beforeEach(function () {
    $this->terminal = Terminal::create([
        'fortis_id' => 'terminal-123',
        'title' => 'Test Terminal',
        'serial_number' => 'SN12345',
        'location_id' => 'location-456',
        'terminal_application_id' => 'app-789',
        'active' => true,
        'synced_at' => now()->subHours(2),
    ]);
});

describe('Terminal Model Attributes', function () {
    it('has correct fillable attributes', function () {
        $fillable = [
            'fortis_id', 'location_id', 'title', 'serial_number',
            'terminal_application_id', 'terminal_manufacturer_code',
            'default_product_transaction_id', 'active', 'fortis_created_at',
            'fortis_modified_at', 'created_user_id', 'modified_user_id',
            'synced_at', 'fortis_data',
        ];

        expect($this->terminal->getFillable())->toEqual($fillable);
    });

    it('casts attributes correctly', function () {
        $terminal = Terminal::create([
            'title' => 'Cast Test Terminal',
            'serial_number' => 'CAST123',
            'active' => '1',
            'fortis_created_at' => now(),
            'fortis_data' => ['key' => 'value'],
        ]);

        expect($terminal->active)->toBeTrue();
        expect($terminal->fortis_data)->toBeArray();
        expect($terminal->fortis_data['key'])->toBe('value');
    });

    it('generates display name attribute', function () {
        expect($this->terminal->display_name)->toBe('Test Terminal (SN12345)');
    });
});

describe('Terminal Model Scopes', function () {
    beforeEach(function () {
        Terminal::create([
            'title' => 'Active Terminal',
            'serial_number' => 'ACTIVE123',
            'active' => true,
        ]);

        Terminal::create([
            'title' => 'Inactive Terminal',
            'serial_number' => 'INACTIVE123',
            'active' => false,
        ]);

        Terminal::create([
            'title' => 'Different Location Terminal',
            'serial_number' => 'DIFF123',
            'location_id' => 'different-location',
            'active' => true,
        ]);
    });

    it('filters active terminals', function () {
        $activeTerminals = Terminal::active()->get();

        expect($activeTerminals)->toHaveCount(3); // Including the one from beforeEach
        expect($activeTerminals->pluck('title'))->toContain('Active Terminal');
        expect($activeTerminals->pluck('title'))->not->toContain('Inactive Terminal');
    });

    it('filters by location', function () {
        $locationTerminals = Terminal::forLocation('location-456')->get();

        expect($locationTerminals)->toHaveCount(1);
        expect($locationTerminals->first()->title)->toBe('Test Terminal');
    });

    it('filters by manufacturer', function () {
        Terminal::create([
            'title' => 'Manufacturer Terminal',
            'serial_number' => 'MFG123',
            'terminal_manufacturer_code' => '2',
        ]);

        $manufacturerTerminals = Terminal::byManufacturer('2')->get();

        expect($manufacturerTerminals)->toHaveCount(1);
        expect($manufacturerTerminals->first()->title)->toBe('Manufacturer Terminal');
    });
});

describe('Terminal Status Methods', function () {
    it('detects when sync is needed', function () {
        // Test null synced_at case (never synced)
        $neverSyncedTerminal = Terminal::create([
            'title' => 'Never Synced',
            'serial_number' => 'NEVER123',
            'synced_at' => null,
        ]);

        expect($neverSyncedTerminal->needsSync())->toBeTrue();

        // Test the needsSync method exists and is callable
        expect(method_exists($neverSyncedTerminal, 'needsSync'))->toBeTrue();

        // Test that method has the expected signature
        $reflection = new ReflectionMethod($neverSyncedTerminal, 'needsSync');
        $paramCount = $reflection->getNumberOfParameters();
        expect($paramCount >= 0 && $paramCount <= 1)->toBeTrue(); // Optional hoursThreshold parameter
    });

    it('marks terminal as synced', function () {
        $terminal = Terminal::create([
            'title' => 'Unsynced Terminal',
            'serial_number' => 'UNSYNC123',
        ]);

        expect($terminal->synced_at)->toBeNull();

        $terminal->markSynced();

        $terminal->refresh();
        expect($terminal->synced_at)->not->toBeNull();
    });

    it('checks if ready for payments', function () {
        $activeTerminal = Terminal::create([
            'title' => 'Active Terminal',
            'serial_number' => 'ACTIVE123',
            'active' => true,
        ]);

        $inactiveTerminal = Terminal::create([
            'title' => 'Inactive Terminal',
            'serial_number' => 'INACTIVE123',
            'active' => false,
        ]);

        expect($activeTerminal->isReadyForPayments())->toBeTrue();
        expect($inactiveTerminal->isReadyForPayments())->toBeFalse();
    });
});

describe('Terminal Payment Processing', function () {
    it('authorizes payment successfully', function () {
        LunarFortis::shouldReceive('processTerminalCreditCardAuth')
            ->once()
            ->with('terminal-123', 1000, ['order_number' => 'ORD-123'])
            ->andReturn([
                'success' => true,
                'transaction_id' => 'auth-trans-123'
            ]);

        $result = $this->terminal->authorizePayment(1000, [
            'order_number' => 'ORD-123'
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['transaction_id'])->toBe('auth-trans-123');
    });

    it('throws exception when terminal is inactive', function () {
        $inactiveTerminal = Terminal::create([
            'fortis_id' => 'inactive-terminal',
            'title' => 'Inactive Terminal',
            'serial_number' => 'INACTIVE123',
            'active' => false,
        ]);

        expect(fn() => $inactiveTerminal->authorizePayment(1000))
            ->toThrow(Exception::class, 'Terminal inactive-terminal is not active');
    });

    it('processes complete payment successfully', function () {
        // Mock authorize
        LunarFortis::shouldReceive('processTerminalCreditCardAuth')
            ->once()
            ->with('terminal-123', 1500, ['description' => 'Test'])
            ->andReturn([
                'success' => true,
                'transaction_id' => 'auth-trans-456'
            ]);

        // Mock capture
        LunarFortis::shouldReceive('captureTerminalTransaction')
            ->once()
            ->with('auth-trans-456', 1500, ['description' => 'Test', 'order_number' => null, 'customer_id' => null])
            ->andReturn([
                'data' => [
                    'id' => 'captured-trans-456',
                    'status_code' => 101
                ]
            ]);

        $result = $this->terminal->processCompletePayment(1500, [
            'description' => 'Test'
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['captured'])->toBeTrue();
        expect($result['capture_result']['data']['id'])->toBe('captured-trans-456');
    });

    it('handles authorize failure in complete payment', function () {
        LunarFortis::shouldReceive('processTerminalCreditCardAuth')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'Authorization failed'
            ]);

        $result = $this->terminal->processCompletePayment(1000);

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('Authorization failed');
    });

    it('initiates authorization and returns status code', function () {
        LunarFortis::shouldReceive('authorizeTerminalCreditCard')
            ->once()
            ->with('terminal-123', 2000, [])
            ->andReturn([
                'data' => [
                    'async' => [
                        'code' => 'status-code-789'
                    ]
                ]
            ]);

        $statusCode = $this->terminal->initiateAuthorization(2000);

        expect($statusCode)->toBe('status-code-789');
    });

    it('throws exception when no async status code received', function () {
        LunarFortis::shouldReceive('authorizeTerminalCreditCard')
            ->once()
            ->andReturn(['data' => []]);

        expect(fn() => $this->terminal->initiateAuthorization(1000))
            ->toThrow(Exception::class, 'No async status code received from terminal authorization initiation');
    });

    it('checks authorization status', function () {
        LunarFortis::shouldReceive('checkTerminalTransactionStatus')
            ->once()
            ->with('status-123')
            ->andReturn([
                'data' => [
                    'progress' => 75,
                    'id' => 'trans-123',
                    'error' => null
                ]
            ]);

        $status = $this->terminal->checkAuthorizationStatus('status-123');

        expect($status['progress'])->toBe(75);
        expect($status['completed'])->toBeFalse();
        expect($status['success'])->toBeFalse(); // Not completed yet
        expect($status['transaction_id'])->toBe('trans-123');
    });

    it('waits for authorization completion', function () {
        LunarFortis::shouldReceive('waitForTerminalTransaction')
            ->once()
            ->with('status-456', 300, 2)
            ->andReturn([
                'data' => [
                    'progress' => 100,
                    'id' => 'completed-trans-456',
                    'error' => null
                ]
            ]);

        $result = $this->terminal->waitForAuthorization('status-456');

        expect($result['progress'])->toBe(100);
        expect($result['completed'])->toBeTrue();
        expect($result['success'])->toBeTrue();
        expect($result['timed_out'])->toBeFalse();
    });

    it('captures transaction successfully', function () {
        LunarFortis::shouldReceive('captureTerminalTransaction')
            ->once()
            ->with('trans-789', 2500, [
                'order_number' => 'ORD-789',
                'customer_id' => 'CUST-456'
            ])
            ->andReturn([
                'data' => [
                    'id' => 'captured-trans-789',
                    'status_code' => 101
                ]
            ]);

        $result = $this->terminal->captureTransaction('trans-789', 2500, [
            'order_number' => 'ORD-789',
            'customer_id' => 'CUST-456'
        ]);

        expect($result['data']['id'])->toBe('captured-trans-789');
    });
});

describe('Terminal Sync Operations', function () {
    it('syncs all terminals from Fortis API', function () {
        LunarFortis::shouldReceive('listTerminals')
            ->once()
            ->andReturn([
                'list' => [
                    [
                        'id' => 'fortis-terminal-1',
                        'title' => 'Fortis Terminal 1',
                        'serial_number' => 'FORTIS123',
                        'active' => true,
                        'created_ts' => 1640995200, // 2022-01-01 00:00:00
                        'location_id' => 'loc-123'
                    ],
                    [
                        'id' => 'fortis-terminal-2',
                        'title' => 'Fortis Terminal 2',
                        'serial_number' => 'FORTIS456',
                        'active' => false,
                        'created_ts' => 1640995200,
                        'location_id' => 'loc-456'
                    ]
                ]
            ]);

        $stats = Terminal::syncFromFortis();

        expect($stats['created'])->toBe(2);
        expect($stats['updated'])->toBe(0);
        expect($stats['errors'])->toBe(0);
        expect($stats['total_processed'])->toBe(2);

        $terminals = Terminal::whereIn('fortis_id', ['fortis-terminal-1', 'fortis-terminal-2'])->get();
        expect($terminals)->toHaveCount(2);
        expect($terminals->first()->title)->toBe('Fortis Terminal 1');
    });

    it('syncs single terminal from Fortis API', function () {
        LunarFortis::shouldReceive('getTerminal')
            ->once()
            ->with('single-terminal-123')
            ->andReturn([
                'data' => [
                    'id' => 'single-terminal-123',
                    'title' => 'Single Terminal',
                    'serial_number' => 'SINGLE123',
                    'active' => true,
                    'location_id' => 'single-loc'
                ]
            ]);

        $terminal = Terminal::syncSingleFromFortis('single-terminal-123');

        expect($terminal)->not->toBeNull();
        expect($terminal->fortis_id)->toBe('single-terminal-123');
        expect($terminal->title)->toBe('Single Terminal');
        expect($terminal->synced_at)->not->toBeNull();
    });

    it('returns null when single terminal not found', function () {
        LunarFortis::shouldReceive('getTerminal')
            ->once()
            ->with('nonexistent-terminal')
            ->andReturn(['data' => []]);

        $terminal = Terminal::syncSingleFromFortis('nonexistent-terminal');

        expect($terminal)->toBeNull();
    });
});

describe('Terminal Validation', function () {
    it('provides validation rules for different scenarios', function () {
        $createRules = Terminal::getValidationRules('create');
        $updateRules = Terminal::getValidationRules('update');
        $generalRules = Terminal::getValidationRules();

        expect($createRules['fortis_id'])->toContain('unique:fortis_terminals,fortis_id');
        expect($updateRules['fortis_id'])->toContain('sometimes');
        expect($generalRules['title'])->toContain('required');
    });

    it('validates manufacturer codes correctly', function () {
        expect(Terminal::isValidManufacturerCode(1))->toBeTrue();
        expect(Terminal::isValidManufacturerCode(2))->toBeTrue();
        expect(Terminal::isValidManufacturerCode(4))->toBeTrue();
        expect(Terminal::isValidManufacturerCode(100))->toBeTrue();
        expect(Terminal::isValidManufacturerCode(3))->toBeFalse();
        expect(Terminal::isValidManufacturerCode('invalid'))->toBeFalse();
    });

    it('provides manufacturer code labels', function () {
        $labels = Terminal::getManufacturerCodeLabels();
        expect($labels)->toBeArray();
        expect($labels[1])->toBe('Manufacturer 1');

        $singleLabel = Terminal::getManufacturerCodeLabels(2);
        expect($singleLabel)->toBe('Manufacturer 2');

        $invalidLabel = Terminal::getManufacturerCodeLabels(999);
        expect($invalidLabel)->toBeNull();
    });

    it('provides manufacturer code options for dropdowns', function () {
        $options = Terminal::getManufacturerCodeOptions();

        expect($options)->toBeArray();
        expect($options[0])->toHaveKeys(['value', 'label']);
        expect($options[0]['value'])->toBe(1);
        expect($options[0]['label'])->toBe('Manufacturer 1');
    });
});

describe('Terminal Data Mapping', function () {
    it('maps Fortis data to attributes correctly', function () {
        $fortisData = [
            'id' => 'fortis-mapping-test',
            'title' => 'Mapping Test Terminal',
            'serial_number' => 'MAP123',
            'location_id' => 'mapping-loc',
            'terminal_application_id' => 'mapping-app',
            'terminal_manufacturer_code' => 2,
            'default_product_transaction_id' => 'mapping-product',
            'active' => 1,
            'created_ts' => 1640995200,
            'modified_ts' => 1641081600,
            'created_user_id' => 'creator-123',
            'modified_user_id' => 'modifier-456'
        ];

        $reflection = new ReflectionClass(Terminal::class);
        $mapMethod = $reflection->getMethod('mapFortisDataToAttributes');
        $mapMethod->setAccessible(true);

        $attributes = $mapMethod->invokeArgs(null, [$fortisData]);

        expect($attributes['fortis_id'])->toBe('fortis-mapping-test');
        expect($attributes['title'])->toBe('Mapping Test Terminal');
        expect($attributes['terminal_manufacturer_code'])->toBe('2'); // Cast to string
        expect($attributes['active'])->toBeTrue(); // Cast to boolean
        expect($attributes['fortis_created_at'])->toBeInstanceOf(Carbon::class);
        expect($attributes['fortis_data'])->toEqual($fortisData);
    });
});

afterEach(function () {
    Mockery::close();
});