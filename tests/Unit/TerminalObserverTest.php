<?php

use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\Observers\TerminalObserver;
use Illuminate\Support\Facades\Log;

describe('TerminalObserver - Basic Functionality', function () {
    beforeEach(function () {
        // Pre-configure the mock before swapping to avoid race conditions where
        // PHP 8.4 deprecations fire during mock setup on prefer-lowest
        $logMock = Mockery::mock(\Illuminate\Log\LogManager::class);
        $logMock->shouldReceive('channel')->withAnyArgs()->andReturnSelf();
        $logMock->shouldReceive('info')->withAnyArgs();
        $logMock->shouldReceive('error')->withAnyArgs();
        Log::swap($logMock);

        $this->mockFortis = Mockery::mock(LunarFortis::class);
        $this->observer = new TerminalObserver($this->mockFortis);
    });

    it('skips sync when terminal has no fortis_id', function () {
        $terminal = Terminal::create([
            'title' => 'Test Terminal',
            'serial_number' => 'SN123',
            'fortis_id' => null, // No Fortis ID
        ]);

        $result = $this->observer->updating($terminal);

        expect($result)->toBeTrue();
        $this->mockFortis->shouldNotHaveReceived('updateTerminal');
    });

    it('skips sync when terminal has fortis_id but no significant changes', function () {
        $terminal = Terminal::create([
            'fortis_id' => 'fortis-123',
            'title' => 'Test Terminal',
            'serial_number' => 'SN123',
        ]);

        // Only change local tracking fields
        $terminal->synced_at = now();
        $terminal->fortis_data = ['key' => 'value'];

        $result = $this->observer->updating($terminal);

        expect($result)->toBeTrue();
        $this->mockFortis->shouldNotHaveReceived('updateTerminal');
    });

    it('skips creation sync when terminal has fortis_id', function () {
        $terminal = Terminal::create([
            'fortis_id' => 'fortis-123', // Has Fortis ID
            'title' => 'Test Terminal',
            'serial_number' => 'SN123',
        ]);

        $this->observer->created($terminal);

        $this->mockFortis->shouldNotHaveReceived('createTerminal');
    });

    it('skips creation sync when required fields are missing', function () {
        $terminal = Terminal::create([
            'title' => 'Test Terminal',
            // Missing serial_number
        ]);

        $this->observer->created($terminal);

        $this->mockFortis->shouldNotHaveReceived('createTerminal');
    });

    it('skips creation sync when title is empty', function () {
        $terminal = Terminal::create([
            'title' => '', // Empty title
            'serial_number' => 'SN456',
        ]);

        $this->observer->created($terminal);

        $this->mockFortis->shouldNotHaveReceived('createTerminal');
    });

    it('skips creation sync when serial_number is empty', function () {
        $terminal = Terminal::create([
            'title' => 'Test Terminal',
            'serial_number' => '', // Empty serial
        ]);

        $this->observer->created($terminal);

        $this->mockFortis->shouldNotHaveReceived('createTerminal');
    });
});

describe('TerminalObserver - Direct Method Testing', function () {
    beforeEach(function () {
        $logMock = Mockery::mock(\Illuminate\Log\LogManager::class);
        $logMock->shouldReceive('channel')->withAnyArgs()->andReturnSelf();
        $logMock->shouldReceive('info')->withAnyArgs();
        $logMock->shouldReceive('error')->withAnyArgs();
        Log::swap($logMock);

        $this->mockFortis = Mockery::mock(LunarFortis::class);
        $this->observer = new TerminalObserver($this->mockFortis);
    });

    it('can call syncToFortis method directly', function () {
        $terminal = Terminal::create([
            'fortis_id' => 'fortis-123',
            'title' => 'Test Terminal',
            'serial_number' => 'SN123',
        ]);

        // Set the attributes that will be mapped
        $terminal->setAttribute('title', 'Updated Title');
        $terminal->setAttribute('active', false);

        // Use reflection to call the protected method
        $reflection = new ReflectionClass($this->observer);
        $method = $reflection->getMethod('syncToFortis');
        $method->setAccessible(true);

        $this->mockFortis->shouldReceive('updateTerminal')
            ->once()
            ->with('fortis-123', [
                'title' => 'Updated Title',
                'active' => false,
            ])
            ->andReturn(['success' => true]);

        // Call the protected method directly
        $method->invoke($this->observer, $terminal, ['title', 'active']);

        // Verify the terminal was updated with sync timestamp
        $terminal->refresh();
        expect($terminal->synced_at)->not->toBeNull();
    });

    it('can call createInFortis method directly', function () {
        $terminal = Terminal::create([
            'title' => 'New Terminal',
            'serial_number' => 'SN456',
            'location_id' => 'location-123',
            'active' => true,
        ]);

        // Use reflection to call the protected method
        $reflection = new ReflectionClass($this->observer);
        $method = $reflection->getMethod('createInFortis');
        $method->setAccessible(true);

        $this->mockFortis->shouldReceive('createTerminal')
            ->once()
            ->with([
                'title' => 'New Terminal',
                'serial_number' => 'SN456',
                'location_id' => 'location-123',
                'active' => true,
            ])
            ->andReturn([
                'data' => [
                    'id' => 'fortis-terminal-456',
                    'title' => 'New Terminal',
                ],
            ]);

        // Call the protected method directly
        $method->invoke($this->observer, $terminal);

        // Verify the terminal was updated with Fortis ID
        $terminal->refresh();
        expect($terminal->fortis_id)->toBe('fortis-terminal-456');
        expect($terminal->synced_at)->not->toBeNull();
    });

    it('handles createInFortis with default location_id', function () {
        config(['services.fortis.locationId' => 'default-location']);

        $terminal = Terminal::create([
            'title' => 'New Terminal',
            'serial_number' => 'SN456',
            'location_id' => null,
        ]);

        // Use reflection to call the protected method
        $reflection = new ReflectionClass($this->observer);
        $method = $reflection->getMethod('createInFortis');
        $method->setAccessible(true);

        $this->mockFortis->shouldReceive('createTerminal')
            ->once()
            ->with([
                'title' => 'New Terminal',
                'serial_number' => 'SN456',
                'location_id' => 'default-location',
                'active' => true,
            ])
            ->andReturn([
                'data' => [
                    'id' => 'fortis-terminal-789',
                    'title' => 'New Terminal',
                ],
            ]);

        $method->invoke($this->observer, $terminal);

        $terminal->refresh();
        expect($terminal->fortis_id)->toBe('fortis-terminal-789');
    });

    it('includes optional fields in createInFortis', function () {
        $terminal = Terminal::create([
            'title' => 'New Terminal',
            'serial_number' => 'SN456',
            'terminal_application_id' => 'app-123',
            'terminal_manufacturer_code' => '2',
            'default_product_transaction_id' => 'product-456',
        ]);

        // Use reflection to call the protected method
        $reflection = new ReflectionClass($this->observer);
        $method = $reflection->getMethod('createInFortis');
        $method->setAccessible(true);

        $this->mockFortis->shouldReceive('createTerminal')
            ->once()
            ->with([
                'title' => 'New Terminal',
                'serial_number' => 'SN456',
                'location_id' => config('services.fortis.locationId'),
                'active' => true,
                'terminal_application_id' => 'app-123',
                'terminal_manufacturer_code' => '2',
                'default_product_transaction_id' => 'product-456',
            ])
            ->andReturn([
                'data' => [
                    'id' => 'fortis-terminal-optional',
                    'title' => 'New Terminal',
                ],
            ]);

        $method->invoke($this->observer, $terminal);

        $terminal->refresh();
        expect($terminal->fortis_id)->toBe('fortis-terminal-optional');
    });
});

describe('TerminalObserver - Field Mapping', function () {
    beforeEach(function () {
        $logMock = Mockery::mock(\Illuminate\Log\LogManager::class);
        $logMock->shouldReceive('channel')->withAnyArgs()->andReturnSelf();
        $logMock->shouldReceive('info')->withAnyArgs();
        $logMock->shouldReceive('error')->withAnyArgs();
        Log::swap($logMock);

        $this->mockFortis = Mockery::mock(LunarFortis::class);
        $this->observer = new TerminalObserver($this->mockFortis);
    });

    it('maps field names correctly in syncToFortis', function () {
        $terminal = Terminal::create([
            'fortis_id' => 'fortis-123',
            'title' => 'Test Terminal',
            'serial_number' => 'SN123',
        ]);

        // Set the attributes that will be mapped
        $terminal->setAttribute('terminal_application_id', 'app-456');
        $terminal->setAttribute('terminal_manufacturer_code', '2');
        $terminal->setAttribute('default_product_transaction_id', 'product-789');

        // Use reflection to call the protected method
        $reflection = new ReflectionClass($this->observer);
        $method = $reflection->getMethod('syncToFortis');
        $method->setAccessible(true);

        $this->mockFortis->shouldReceive('updateTerminal')
            ->once()
            ->with('fortis-123', [
                'terminal_application_id' => 'app-456',
                'terminal_manufacturer_code' => '2',
                'default_product_transaction_id' => 'product-789',
            ])
            ->andReturn(['success' => true]);

        $method->invoke($this->observer, $terminal, [
            'terminal_application_id',
            'terminal_manufacturer_code',
            'default_product_transaction_id',
        ]);
    });
});

afterEach(function () {
    Mockery::close();
});
