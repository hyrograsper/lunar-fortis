<?php

use Hyrograsper\LunarFortis\Services\FortisHttpService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    // Set up test configuration
    Config::set('lunar-fortis.environment', 'sandbox');
    Config::set('lunar-fortis.debug', true);
    Config::set('services.fortis', [
        'userId' => 'test-user-id',
        'userApiKey' => 'test-api-key',
        'developerId' => 'test-developer-id',
        'locationId' => 'test-location-id',
        'productTransactionId' => 'test-product-id',
        'terminalProductTransactionId' => 'test-terminal-product-id',
    ]);

    $this->service = new FortisHttpService();
});

describe('FortisHttpService Configuration', function () {
    it('sets sandbox base URL for sandbox environment', function () {
        Config::set('lunar-fortis.environment', 'sandbox');
        $service = new FortisHttpService();

        $reflection = new ReflectionClass($service);
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);

        expect($baseUrlProperty->getValue($service))->toBe('https://api.sandbox.fortis.tech');
    });

    it('sets production base URL for production environment', function () {
        Config::set('lunar-fortis.environment', 'production');
        $service = new FortisHttpService();

        $reflection = new ReflectionClass($service);
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);

        expect($baseUrlProperty->getValue($service))->toBe('https://api.fortis.tech');
    });

    it('sets correct headers', function () {
        $reflection = new ReflectionClass($this->service);
        $headersProperty = $reflection->getProperty('headers');
        $headersProperty->setAccessible(true);
        $headers = $headersProperty->getValue($this->service);

        expect($headers)->toHaveKeys(['Content-Type', 'user-id', 'user-api-key', 'developer-id']);
        expect($headers['Content-Type'])->toBe('application/json');
        expect($headers['user-id'])->toBe('test-user-id');
        expect($headers['user-api-key'])->toBe('test-api-key');
        expect($headers['developer-id'])->toBe('test-developer-id');
    });
});

describe('Transaction Intention', function () {
    it('creates transaction intention successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/elements/transaction/intention' => Http::response([
                'data' => [
                    'client_token' => 'test-client-token',
                    'id' => 'test-transaction-id'
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->createTransactionIntention(1000, 'auth-only');

        expect($result)->toHaveKey('data.client_token', 'test-client-token');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.sandbox.fortis.tech/v1/elements/transaction/intention' &&
                   $request->method() === 'POST' &&
                   $request['action'] === 'auth-only' &&
                   $request['amount'] === 1000 &&
                   $request['digitalWalletsOnly'] === false;
        });
    });

    it('handles transaction intention creation failure', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/elements/transaction/intention' => Http::response([
                'detail' => 'Invalid amount'
            ], 422)
        ]);

        Log::shouldReceive('error')->once();

        expect(fn() => $this->service->createTransactionIntention(0, 'sale'))
            ->toThrow(Exception::class, 'Failed to create transaction intention');
    });
});

describe('Authorization Complete', function () {
    it('completes authorized transaction successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/transactions/test-transaction-id/auth-complete' => Http::response([
                'data' => [
                    'id' => 'test-transaction-id',
                    'status_code' => 101,
                    'transaction_amount' => 1000
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->completeAuthorizedTransaction('test-transaction-id', 1000, [
            'order_number' => 'ORD-123',
            'customer_id' => '456'
        ]);

        expect($result['data']['id'])->toBe('test-transaction-id');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/auth-complete') &&
                   $request->method() === 'PATCH' &&
                   $request['transaction_amount'] === 1000 &&
                   $request['order_number'] === 'ORD-123' &&
                   $request['customer_id'] === '456';
        });
    });

    it('casts customer_id to string', function () {
        Http::fake([
            '*' => Http::response(['data' => []], 200)
        ]);

        $this->service->completeAuthorizedTransaction('test-id', 1000, [
            'customer_id' => 123 // Integer input
        ]);

        Http::assertSent(function ($request) {
            return $request['customer_id'] === '123'; // Should be string
        });
    });

    it('includes all optional fields', function () {
        Http::fake([
            '*' => Http::response(['data' => []], 200)
        ]);

        $options = [
            'order_number' => 'ORD-123',
            'customer_id' => '456',
            'billing_address' => [
                'street' => '123 Main St',
                'city' => 'Anytown',
                'state' => 'CA',
                'postal_code' => '12345'
            ],
            'tip_amount' => 200,
            'tax' => 100,
            'room_num' => '101',
            'room_rate' => 9900
        ];

        $this->service->completeAuthorizedTransaction('test-id', 1000, $options);

        Http::assertSent(function ($request) use ($options) {
            return $request['order_number'] === 'ORD-123' &&
                   $request['billing_address']['street'] === '123 Main St' &&
                   $request['tip_amount'] === 200 &&
                   $request['room_num'] === '101';
        });
    });
});

describe('Credit Card Authorization from Token', function () {
    it('authorizes credit card from token successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/transactions/cc/auth-only/token' => Http::response([
                'data' => [
                    'id' => 'auth-transaction-id',
                    'status_code' => 102
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->authorizeCcFromToken('test-token-id', 1500, [
            'order_number' => 'ORD-456'
        ]);

        expect($result['data']['id'])->toBe('auth-transaction-id');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST' &&
                   $request['token_id'] === 'test-token-id' &&
                   $request['transaction_amount'] === 1500 &&
                   $request['order_number'] === 'ORD-456';
        });
    });
});

describe('Refund Processing', function () {
    it('processes refund successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/transactions/original-transaction-id/refund' => Http::response([
                'data' => [
                    'id' => 'refund-transaction-id',
                    'status_code' => 111
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->refund('original-transaction-id', 500);

        expect($result['data']['id'])->toBe('refund-transaction-id');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/refund') &&
                   $request->method() === 'PATCH' &&
                   $request['transaction_amount'] === 500;
        });
    });
});

describe('Transaction Retrieval', function () {
    it('retrieves transaction successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/transactions/test-transaction-id' => Http::response([
                'data' => [
                    'id' => 'test-transaction-id',
                    'status_code' => 101,
                    'transaction_amount' => 1000
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->getTransaction('test-transaction-id');

        expect($result['data']['id'])->toBe('test-transaction-id');

        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                   str_contains($request->url(), '/test-transaction-id');
        });
    });
});

describe('Terminal Management', function () {
    it('creates terminal successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/terminals' => Http::response([
                'data' => [
                    'id' => 'new-terminal-id',
                    'title' => 'Test Terminal'
                ]
            ], 201)
        ]);

        Log::shouldReceive('debug')->once();

        $terminalData = [
            'title' => 'Test Terminal',
            'serial_number' => 'SN123456',
            'terminal_application_id' => 'app-123'
        ];

        $result = $this->service->createTerminal($terminalData);

        expect($result['data']['title'])->toBe('Test Terminal');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST' &&
                   $request['title'] === 'Test Terminal' &&
                   $request['serial_number'] === 'SN123456';
        });
    });

    it('lists terminals successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/terminals*' => Http::response([
                'list' => [
                    ['id' => 'terminal-1', 'title' => 'Terminal 1'],
                    ['id' => 'terminal-2', 'title' => 'Terminal 2']
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->listTerminals([
            'page' => 1,
            'filterBy' => [
                ['key' => 'active', 'operator' => '=', 'value' => '1']
            ]
        ]);

        expect($result['list'])->toHaveCount(2);

        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                   str_contains($request->url(), '/terminals');
        });
    });

    it('retrieves single terminal successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/terminals/terminal-123*' => Http::response([
                'data' => [
                    'id' => 'terminal-123',
                    'title' => 'Test Terminal'
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->getTerminal('terminal-123', ['location'], ['id', 'title']);

        expect($result['data']['id'])->toBe('terminal-123');

        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                   str_contains($request->url(), 'terminal-123');
        });
    });

    it('updates terminal successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/terminals/terminal-123*' => Http::response([
                'data' => [
                    'id' => 'terminal-123',
                    'title' => 'Updated Terminal'
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->updateTerminal('terminal-123', [
            'title' => 'Updated Terminal',
            'active' => true
        ]);

        expect($result['data']['title'])->toBe('Updated Terminal');

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH' &&
                   $request['title'] === 'Updated Terminal' &&
                   $request['active'] === true;
        });
    });
});

describe('Terminal Transaction Processing', function () {
    it('authorizes terminal credit card successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/transactions/cc/auth-only/terminal' => Http::response([
                'data' => [
                    'async' => [
                        'code' => 'async-123',
                        'link' => 'https://api.sandbox.fortis.tech/v1/async/status/async-123'
                    ]
                ]
            ], 202)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->authorizeTerminalCreditCard('terminal-123', 2500, [
            'description' => 'Test payment'
        ]);

        expect($result['data']['async']['code'])->toBe('async-123');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST' &&
                   $request['terminal_id'] === 'terminal-123' &&
                   $request['transaction_amount'] === 2500 &&
                   $request['card_present'] === true &&
                   $request['cardholder_present'] === true &&
                   $request['product_transaction_id'] === 'test-terminal-product-id';
        });
    });
});

describe('Async Status Checking', function () {
    it('checks async status successfully', function () {
        Http::fake([
            'api.sandbox.fortis.tech/v1/async/status/async-123' => Http::response([
                'data' => [
                    'progress' => 100,
                    'id' => 'completed-transaction-id'
                ]
            ], 200)
        ]);

        Log::shouldReceive('debug')->once();

        $result = $this->service->checkAsyncStatus('async-123');

        expect($result['data']['progress'])->toBe(100);

        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                   str_contains($request->url(), '/async/status/async-123');
        });
    });
});

describe('HTTP Retry Logic', function () {
    it('handles successful request without retry', function () {
        Http::fake([
            '*' => Http::response(['data' => ['success' => true]], 200)
        ]);

        Log::shouldReceive('debug')->once(); // Success log

        $result = $this->service->createTransactionIntention(1000, 'sale');

        expect($result['data']['success'])->toBeTrue();
    });

    it('handles 429 rate limiting response', function () {
        Http::fake([
            '*' => Http::response('Rate limited', 429)
        ]);

        Log::shouldReceive('error')->once();

        expect(fn() => $this->service->createTransactionIntention(1000, 'sale'))
            ->toThrow(Exception::class);
    });

    it('handles other HTTP errors', function () {
        Http::fake([
            '*' => Http::response('Bad request', 400)
        ]);

        Log::shouldReceive('error')->once();

        expect(fn() => $this->service->createTransactionIntention(1000, 'sale'))
            ->toThrow(Exception::class);
    });
});

describe('Error Handling', function () {
    it('logs detailed error information', function () {
        Http::fake([
            '*' => Http::response(['detail' => 'Validation failed'], 422)
        ]);

        Log::shouldReceive('error')->once()->withArgs(function ($message, $context) {
            return str_contains($message, 'LunarFortis: Failed to create transaction intention') &&
                   isset($context['error']) &&
                   isset($context['response']);
        });

        expect(fn() => $this->service->createTransactionIntention(1000, 'sale'))
            ->toThrow(Exception::class, 'Failed to create transaction intention');
    });

    it('includes API error details in exception message', function () {
        Http::fake([
            '*' => Http::response(['detail' => 'Invalid payment method'], 422)
        ]);

        Log::shouldReceive('error');

        expect(fn() => $this->service->createTransactionIntention(1000, 'sale'))
            ->toThrow(Exception::class, 'Invalid payment method');
    });
});