<?php

use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\Services\FortisHttpService;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\Models\Order;

beforeEach(function () {
    Config::set('services.fortis', [
        'userId' => 'test-user-id',
        'userApiKey' => 'test-api-key',
        'developerId' => 'test-developer-id',
        'locationId' => 'test-location-id',
        'productTransactionId' => 'test-product-id',
    ]);

    $this->lunarFortis = new LunarFortis;
});

describe('LunarFortis Client Token Management', function () {
    it('gets client token for sale amount', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('createTransactionIntention')
            ->once()
            ->with(1000, 'sale')
            ->andReturn([
                'data' => [
                    'client_token' => 'test-client-token-123',
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $token = $this->lunarFortis->getClientTokenForSaleAmount(1000, 'sale');

        expect($token)->toBe('test-client-token-123');
    });

    it('returns null when no client token in response', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('createTransactionIntention')
            ->once()
            ->andReturn(['data' => []]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $token = $this->lunarFortis->getClientTokenForSaleAmount(1000);

        expect($token)->toBeNull();
    });

    it('throws exception on client token creation failure', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('createTransactionIntention')
            ->once()
            ->andThrow(new Exception('API Error'));

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        expect(fn () => $this->lunarFortis->getClientTokenForSaleAmount(1000))
            ->toThrow(Exception::class, 'Unable to get client token for sale: API Error');
    });
});

describe('LunarFortis Transaction Completion', function () {
    it('completes authorized transaction successfully', function () {
        $mockTransaction = Mockery::mock(TransactionContract::class);
        $mockTransaction->reference = 'trans-123';

        $mockOrder = Mockery::mock();
        $mockOrder->reference = 'ORD-456';
        $mockOrder->customer_id = 789;
        $mockTransaction->order = $mockOrder;

        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->with('trans-123', 1000, [
                'order_number' => 'ORD-456',
                'customer_id' => '789',
            ])
            ->andReturn([
                'data' => [
                    'id' => 'completed-trans-123',
                    'status_code' => 101,
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $result = $this->lunarFortis->completeAuthorizedTransaction($mockTransaction, 1000);

        expect($result['data']['id'])->toBe('completed-trans-123');
    });

    it('merges additional options with transaction data', function () {
        $mockTransaction = Mockery::mock(TransactionContract::class);
        $mockTransaction->reference = 'trans-123';

        $mockOrder = Mockery::mock();
        $mockOrder->reference = 'ORD-456';
        $mockOrder->customer_id = 789;
        $mockTransaction->order = $mockOrder;

        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->with('trans-123', 1000, [
                'order_number' => 'ORD-456',
                'customer_id' => '789',
                'tip_amount' => 200,
                'description' => 'Test payment',
            ])
            ->andReturn(['data' => []]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $this->lunarFortis->completeAuthorizedTransaction($mockTransaction, 1000, [
            'tip_amount' => 200,
            'description' => 'Test payment',
        ]);
    });

    it('handles transactions without order', function () {
        $mockTransaction = Mockery::mock(TransactionContract::class);
        $mockTransaction->reference = 'trans-123';
        $mockTransaction->order = null;

        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->with('trans-123', 1000, [])
            ->andReturn(['data' => []]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $this->lunarFortis->completeAuthorizedTransaction($mockTransaction, 1000);
    });
});

describe('LunarFortis Credit Card Authorization', function () {
    it('authorizes credit card from token successfully', function () {
        $mockOrder = Mockery::mock(Order::class);
        $mockOrder->shouldReceive('setAttribute')->andReturnSelf();

        $mockBillingAddress = Mockery::mock();
        $mockBillingAddress->line_one = '123 Main St';
        $mockBillingAddress->city = 'Anytown';
        $mockBillingAddress->state = 'CA';
        $mockBillingAddress->postcode = '12345';

        $mockCountry = Mockery::mock();
        $mockCountry->iso3 = 'USA';
        $mockBillingAddress->country = $mockCountry;

        // Set up property access for Laravel model - Laravel uses both __get and getAttribute
        $mockOrder->shouldReceive('getAttribute')->with('reference')->andReturn('ORD-789');
        $mockOrder->shouldReceive('getAttribute')->with('customer_id')->andReturn(456);
        $mockOrder->shouldReceive('getAttribute')->with('total')->andReturn((object) ['value' => 1500]);
        $mockOrder->shouldReceive('getAttribute')->with('billingAddress')->andReturn($mockBillingAddress);
        $mockOrder->shouldReceive('getAttribute')->andReturn(null); // Fallback for any other getAttribute calls

        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('authorizeCcFromToken')
            ->once()
            ->with('token-123', 1500, [
                'order_number' => 'ORD-789',
                'customer_id' => 456,
                'billing_address' => [
                    'street' => '123 Main St',
                    'city' => 'Anytown',
                    'state' => 'CA',
                    'postal_code' => '12345',
                    'country' => 'USA',
                ],
            ])
            ->andReturn([
                'data' => [
                    'id' => 'auth-trans-123',
                    'status_code' => 102,
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $result = $this->lunarFortis->authorizeCcFromToken('token-123', $mockOrder);

        expect($result['data']['id'])->toBe('auth-trans-123');
    });
});

describe('LunarFortis Refund Processing', function () {
    it('processes refund successfully', function () {
        $mockTransaction = Mockery::mock(TransactionContract::class);
        $mockTransaction->reference = 'original-trans-123';

        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('refund')
            ->once()
            ->with('original-trans-123', 500)
            ->andReturn([
                'data' => [
                    'id' => 'refund-trans-123',
                    'status_code' => 111,
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $result = $this->lunarFortis->refund($mockTransaction, 500);

        expect($result['data']['id'])->toBe('refund-trans-123');
    });
});

describe('LunarFortis Transaction Retrieval', function () {
    it('retrieves transaction successfully', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('getTransaction')
            ->once()
            ->with('trans-123')
            ->andReturn([
                'data' => [
                    'id' => 'trans-123',
                    'status_code' => 101,
                    'transaction_amount' => 1000,
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $result = $this->lunarFortis->getTransaction('trans-123');

        expect($result['data']['transaction_amount'])->toBe(1000);
    });
});

describe('LunarFortis Terminal Management', function () {
    it('creates terminal successfully', function () {
        $terminalData = [
            'title' => 'Test Terminal',
            'serial_number' => 'SN123456',
            'terminal_application_id' => 'app-123',
        ];

        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('createTerminal')
            ->once()
            ->with($terminalData)
            ->andReturn([
                'data' => [
                    'id' => 'new-terminal-123',
                    'title' => 'Test Terminal',
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $result = $this->lunarFortis->createTerminal($terminalData);

        expect($result['data']['title'])->toBe('Test Terminal');
    });

    it('lists terminals successfully', function () {
        $options = ['page' => 1];

        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('listTerminals')
            ->once()
            ->with($options)
            ->andReturn([
                'list' => [
                    ['id' => 'terminal-1', 'title' => 'Terminal 1'],
                    ['id' => 'terminal-2', 'title' => 'Terminal 2'],
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $result = $this->lunarFortis->listTerminals($options);

        expect($result['list'])->toHaveCount(2);
    });

    it('creates simple terminal with defaults', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('createTerminal')
            ->once()
            ->with([
                'title' => 'Simple Terminal',
                'serial_number' => 'SN789',
                'terminal_application_id' => 'app-456',
                'active' => true,
                'location_id' => 'custom-location',
            ])
            ->andReturn(['data' => []]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $this->lunarFortis->createSimpleTerminal(
            'Simple Terminal',
            'SN789',
            'app-456',
            ['location_id' => 'custom-location']
        );
    });

    it('gets active terminals with filters', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('listTerminals')
            ->once()
            ->with([
                'filterBy' => [
                    [
                        'key' => 'active',
                        'operator' => '=',
                        'value' => '1',
                    ],
                    [
                        'key' => 'location_id',
                        'operator' => '=',
                        'value' => 'test-location-id',
                    ],
                ],
            ])
            ->andReturn(['list' => []]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $this->lunarFortis->getActiveTerminals();
    });

    it('sets terminal status', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('updateTerminal')
            ->once()
            ->with('terminal-123', ['active' => false], null)
            ->andReturn(['data' => []]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $this->lunarFortis->setTerminalStatus('terminal-123', false);
    });
});

describe('LunarFortis Terminal Credit Card Processing', function () {
    it('authorizes terminal credit card successfully', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('authorizeTerminalCreditCard')
            ->once()
            ->with('terminal-123', 2000, ['description' => 'Test'])
            ->andReturn([
                'data' => [
                    'async' => [
                        'code' => 'async-456',
                    ],
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $result = $this->lunarFortis->authorizeTerminalCreditCard('terminal-123', 2000, [
            'description' => 'Test',
        ]);

        expect($result['data']['async']['code'])->toBe('async-456');
    });

    it('captures terminal transaction successfully', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);
        $mockHttpService->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->with('trans-123', 1500, [
                'order_number' => 'ORD-999',
                'customer_id' => 'CUST-888',
            ])
            ->andReturn([
                'data' => [
                    'id' => 'captured-trans-123',
                    'status_code' => 101,
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        $result = $this->lunarFortis->captureTerminalTransaction('trans-123', 1500, [
            'order_number' => 'ORD-999',
            'customer_id' => 'CUST-888',
        ]);

        expect($result['data']['id'])->toBe('captured-trans-123');
    });

    it('processes complete terminal credit card auth workflow', function () {
        $mockHttpService = Mockery::mock(FortisHttpService::class);

        // Mock the authorization call
        $mockHttpService->shouldReceive('authorizeTerminalCreditCard')
            ->once()
            ->with('terminal-123', 3000, ['description' => 'Full workflow'])
            ->andReturn([
                'data' => [
                    'async' => [
                        'code' => 'async-789',
                        'link' => 'https://api.sandbox.fortis.tech/v1/async/status/async-789',
                    ],
                ],
            ]);

        // Mock the status checking calls (simulating completion)
        $mockHttpService->shouldReceive('checkAsyncStatus')
            ->once()
            ->with('async-789')
            ->andReturn([
                'data' => [
                    'progress' => 100,
                    'id' => 'completed-trans-789',
                ],
            ]);

        $reflection = new ReflectionClass($this->lunarFortis);
        $httpServiceProperty = $reflection->getProperty('httpService');
        $httpServiceProperty->setAccessible(true);
        $httpServiceProperty->setValue($this->lunarFortis, $mockHttpService);

        Config::set('lunar-fortis.debug', true);

        $result = $this->lunarFortis->processTerminalCreditCardAuth('terminal-123', 3000, [
            'description' => 'Full workflow',
            'timeout_seconds' => 10,
            'poll_interval_seconds' => 1,
        ]);

        expect($result['success'])->toBeTrue();
        expect($result['transaction_id'])->toBe('completed-trans-789');
        expect($result['completed'])->toBeTrue();
    });
});

afterEach(function () {
    Mockery::close();
});
