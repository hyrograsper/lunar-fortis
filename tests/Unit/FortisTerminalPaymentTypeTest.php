<?php

use Exception;
use Hyrograsper\LunarFortis\Enums\ReasonCode;
use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up test configuration
    Config::set('lunar-fortis.terminal_policy', 'automatic');
    Config::set('lunar-fortis.status_mapping', [
        'payment-received' => 'payment-received',
        'payment-authorized' => 'payment-authorized',
    ]);

    // Mock database structure for tests
    \Illuminate\Support\Facades\Schema::create('fortis_terminals', function ($table) {
        $table->id();
        $table->string('fortis_id');
        $table->string('title');
        $table->string('serial_number');
        $table->timestamps();
    });

    \Illuminate\Support\Facades\Schema::create('carts', function ($table) {
        $table->id();
        $table->foreignId('user_id')->nullable();
        $table->foreignId('customer_id')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
    });

    \Illuminate\Support\Facades\Schema::create('orders', function ($table) {
        $table->id();
        $table->string('reference');
        $table->string('status')->nullable();
        $table->timestamp('placed_at')->nullable();
        $table->integer('customer_id')->nullable();
        $table->timestamps();
    });

    \Illuminate\Support\Facades\Schema::create('transactions', function ($table) {
        $table->id();
        $table->foreignId('parent_transaction_id')->nullable();
        $table->foreignId('order_id');
        $table->boolean('success');
        $table->string('type');
        $table->string('driver');
        $table->integer('amount');
        $table->string('reference');
        $table->string('status');
        $table->text('notes')->nullable();
        $table->string('card_type')->nullable();
        $table->string('last_four')->nullable();
        $table->timestamp('captured_at')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
    });

    // Create test objects
    $this->terminal = Terminal::create([
        'fortis_id' => 'terminal-123',
        'title' => 'Test Terminal',
        'serial_number' => 'SN12345',
    ]);

    $this->customer = new Customer([
        'id' => 1,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $this->order = Order::create([
        'reference' => 'ORD-TEST-123',
        'status' => 'cart',
        'customer_id' => 1,
    ]);

    // Mock cart with minimal structure
    $this->cart = new class {
        public $id = 1;
        public $total;
        public $draftOrder;
        public $completedOrder;

        public function __construct() {
            $this->total = new class {
                public $value = 1000;
            };
        }

        public function createOrder($orderIdToUpdate = null) {
            return Order::create([
                'reference' => 'ORD-NEW-' . rand(100, 999),
                'status' => 'cart',
                'customer_id' => 1,
            ]);
        }
    };

    $this->paymentType = new FortisTerminalPaymentType();
    $this->paymentType->cart = $this->cart;
    $this->paymentType->data = [
        'fortis_transaction_id' => 'trans-123'
    ];
});

describe('FortisTerminalPaymentType Configuration', function () {
    it('sets payment type constant correctly', function () {
        expect(FortisTerminalPaymentType::PAYMENT_TYPE)->toBe('fortis-terminal');
    });

    it('sets automatic policy by default', function () {
        $paymentType = new FortisTerminalPaymentType();
        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('automatic');
    });

    it('uses terminal-specific policy when configured', function () {
        Config::set('lunar-fortis.terminal_policy', 'manual');

        $paymentType = new FortisTerminalPaymentType();
        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('manual');
    });
});

describe('Authorization', function () {
    it('creates order if none exists', function () {
        $this->paymentType->order = null;
        $this->cart->draftOrder = null;
        $this->cart->completedOrder = null;

        LunarFortis::shouldReceive('getTransaction')
            ->once()
            ->with('trans-123')
            ->andReturn([
                'data' => [
                    'id' => 'trans-123',
                    'status_code' => 101,
                    'transaction_amount' => 1000,
                    'account_type' => 'credit',
                    'last_four' => '1234'
                ]
            ]);

        $result = $this->paymentType->authorize();

        expect($result)->toBeInstanceOf(PaymentAuthorize::class);
        expect($result->success)->toBeTrue();
        expect($this->paymentType->order)->not->toBeNull();
    });

    it('fails authorization when order already placed', function () {
        $this->paymentType->order = Order::create([
            'reference' => 'PLACED-ORDER',
            'status' => 'placed',
            'placed_at' => now(),
            'customer_id' => 1,
        ]);

        PaymentAttemptEvent::fake();

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('This order has already been placed');
        PaymentAttemptEvent::assertDispatched();
    });

    it('fails when fortis transaction fetch fails', function () {
        $this->paymentType->order = $this->order;

        LunarFortis::shouldReceive('getTransaction')
            ->once()
            ->andThrow(new Exception('Network error'));

        Log::shouldReceive('error')->once();
        PaymentAttemptEvent::fake();

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeFalse();
        expect($result->message)->toContain('Failed to fetch fortis transaction');
        PaymentAttemptEvent::assertDispatched();
    });

    it('handles successful capture transaction (sale)', function () {
        $this->paymentType->order = $this->order;

        LunarFortis::shouldReceive('getTransaction')
            ->once()
            ->with('trans-123')
            ->andReturn([
                'data' => [
                    'id' => 'trans-123',
                    'status_code' => 101, // Captured
                    'transaction_amount' => 1000,
                    'account_type' => 'credit',
                    'last_four' => '1234'
                ]
            ]);

        PaymentAttemptEvent::fake();

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeTrue();
        expect($result->message)->toBe('Terminal payment completed successfully');

        $this->order->refresh();
        expect($this->order->placed_at)->not->toBeNull();
        expect($this->order->status)->toBe('payment-received');

        PaymentAttemptEvent::assertDispatched();
    });

    it('handles authorization-only transaction with automatic capture', function () {
        Config::set('lunar-fortis.terminal_policy', 'automatic');
        $paymentType = new FortisTerminalPaymentType();
        $paymentType->cart = $this->cart;
        $paymentType->order = $this->order;
        $paymentType->data = ['fortis_transaction_id' => 'trans-auth-123'];

        LunarFortis::shouldReceive('getTransaction')
            ->once()
            ->with('trans-auth-123')
            ->andReturn([
                'data' => [
                    'id' => 'trans-auth-123',
                    'status_code' => 102, // Authorized only
                    'transaction_amount' => 1500,
                    'account_type' => 'credit',
                    'last_four' => '5678'
                ]
            ]);

        LunarFortis::shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->andReturn([
                'data' => [
                    'id' => 'captured-trans-123',
                    'status_code' => 101
                ]
            ]);

        PaymentAttemptEvent::fake();

        $result = $paymentType->authorize();

        expect($result->success)->toBeTrue();
        expect($result->message)->toBe('Terminal payment captured successfully');

        $this->order->refresh();
        expect($this->order->placed_at)->not->toBeNull();
        expect($this->order->status)->toBe('payment-received');

        PaymentAttemptEvent::assertDispatched();
    });

    it('handles authorization-only transaction with manual policy', function () {
        Config::set('lunar-fortis.terminal_policy', 'manual');
        $paymentType = new FortisTerminalPaymentType();
        $paymentType->cart = $this->cart;
        $paymentType->order = $this->order;
        $paymentType->data = ['fortis_transaction_id' => 'trans-manual-123'];

        LunarFortis::shouldReceive('getTransaction')
            ->once()
            ->with('trans-manual-123')
            ->andReturn([
                'data' => [
                    'id' => 'trans-manual-123',
                    'status_code' => 102, // Authorized only
                    'transaction_amount' => 2000,
                    'account_type' => 'credit',
                    'last_four' => '9012'
                ]
            ]);

        PaymentAttemptEvent::fake();

        $result = $paymentType->authorize();

        expect($result->success)->toBeTrue();
        expect($result->message)->toBe('Terminal payment authorized');

        $this->order->refresh();
        expect($this->order->placed_at)->toBeNull(); // Should not be placed yet
        expect($this->order->status)->toBe('payment-authorized');

        PaymentAttemptEvent::assertDispatched();
    });

    it('handles failed transaction from fortis', function () {
        $this->paymentType->order = $this->order;

        LunarFortis::shouldReceive('getTransaction')
            ->once()
            ->with('trans-123')
            ->andReturn([
                'data' => [
                    'id' => 'trans-failed-123',
                    'status_code' => 201, // Declined
                    'transaction_amount' => 1000,
                    'reason_code' => 'DECLINED'
                ]
            ]);

        PaymentAttemptEvent::fake();

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeFalse();
        PaymentAttemptEvent::assertDispatched();
    });
});

describe('Capture', function () {
    beforeEach(function () {
        $this->transaction = Transaction::create([
            'order_id' => $this->order->id,
            'success' => true,
            'type' => 'intent',
            'driver' => 'fortis-terminal',
            'amount' => 1000,
            'reference' => 'trans-123',
            'status' => 'authorized',
            'card_type' => 'credit',
            'last_four' => '1234',
            'meta' => [],
        ]);
    });

    it('captures transaction successfully', function () {
        LunarFortis::shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->with($this->transaction, 1000)
            ->andReturn([
                'data' => [
                    'id' => 'captured-trans-123',
                    'status_code' => 101,
                    'transaction_amount' => 1000
                ]
            ]);

        $result = $this->paymentType->capture($this->transaction, 1000);

        expect($result)->toBeInstanceOf(PaymentCapture::class);
        expect($result->success)->toBeTrue();
        expect($result->message)->toBe('Terminal payment captured successfully');
    });

    it('handles capture failure', function () {
        LunarFortis::shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->andThrow(new Exception('Capture failed'));

        Log::shouldReceive('error')->once();

        $result = $this->paymentType->capture($this->transaction, 1000);

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('Capture failed');
    });

    it('handles unsuccessful capture transaction', function () {
        LunarFortis::shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->andReturn([
                'data' => [
                    'id' => 'failed-capture-123',
                    'status_code' => 201, // Failed
                    'reason_code' => 'INSUFFICIENT_FUNDS'
                ]
            ]);

        $result = $this->paymentType->capture($this->transaction, 1000);

        expect($result->success)->toBeFalse();
    });
});

describe('Refund', function () {
    beforeEach(function () {
        $this->transaction = Transaction::create([
            'order_id' => $this->order->id,
            'success' => true,
            'type' => 'capture',
            'driver' => 'fortis-terminal',
            'amount' => 1000,
            'reference' => 'trans-capture-123',
            'status' => 'approved',
            'card_type' => 'credit',
            'last_four' => '1234',
            'meta' => ['terminal_id' => 'terminal-123'],
        ]);
    });

    it('processes refund successfully', function () {
        LunarFortis::shouldReceive('refund')
            ->once()
            ->with($this->transaction, 500)
            ->andReturn([
                'data' => [
                    'id' => 'refund-trans-123',
                    'status_code' => 111, // Refunded
                    'reason_code_id' => 1000, // Approved
                    'transaction_amount' => 500,
                    'reason_code' => 'APPROVED'
                ]
            ]);

        $result = $this->paymentType->refund($this->transaction, 500);

        expect($result)->toBeInstanceOf(PaymentRefund::class);
        expect($result->success)->toBeTrue();

        // Verify refund transaction was created
        $refundTransaction = Transaction::where('parent_transaction_id', $this->transaction->id)
            ->where('type', 'refund')
            ->first();

        expect($refundTransaction)->not->toBeNull();
        expect($refundTransaction->success)->toBeTrue();
        expect($refundTransaction->amount)->toBe(500);
    });

    it('handles refund failure', function () {
        LunarFortis::shouldReceive('refund')
            ->once()
            ->andThrow(new Exception('Network error'));

        Log::shouldReceive('error')->once();

        $result = $this->paymentType->refund($this->transaction, 500);

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('Network error');
    });

    it('handles declined refund from fortis', function () {
        LunarFortis::shouldReceive('refund')
            ->once()
            ->with($this->transaction, 750)
            ->andReturn([
                'data' => [
                    'id' => 'declined-refund-123',
                    'status_code' => 201, // Declined
                    'reason_code_id' => 1001, // Declined
                    'transaction_amount' => 750,
                    'reason_code' => 'INSUFFICIENT_FUNDS'
                ]
            ]);

        $result = $this->paymentType->refund($this->transaction, 750);

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('INSUFFICIENT_FUNDS');

        // Verify failed refund transaction was created
        $refundTransaction = Transaction::where('parent_transaction_id', $this->transaction->id)
            ->where('type', 'refund')
            ->first();

        expect($refundTransaction)->not->toBeNull();
        expect($refundTransaction->success)->toBeFalse();
        expect($refundTransaction->status)->toBe('declined');
    });
});

describe('Transaction Storage', function () {
    it('stores successful terminal transaction with full details', function () {
        $this->paymentType->order = $this->order;

        $response = [
            'data' => [
                'id' => 'terminal-trans-123',
                'status_code' => 101,
                'transaction_amount' => 1500,
                'account_type' => 'credit',
                'last_four' => '4567',
                'terminal_id' => 'terminal-123',
                'tip_amount' => 150,
                'clerk_number' => '001',
                'description' => 'Test payment',
                'verbiage' => 'APPROVED'
            ]
        ];

        $reflection = new ReflectionClass($this->paymentType);
        $method = $reflection->getMethod('storeTerminalTransaction');
        $method->setAccessible(true);

        $transaction = $method->invoke($this->paymentType, $response);

        expect($transaction)->toBeInstanceOf(Transaction::class);
        expect($transaction->success)->toBeTrue();
        expect($transaction->amount)->toBe(1500);
        expect($transaction->card_type)->toBe('credit');
        expect($transaction->last_four)->toBe('4567');
        expect($transaction->meta['terminal_id'])->toBe('terminal-123');
        expect($transaction->meta['tip_amount'])->toBe(150);
        expect($transaction->meta['clerk_number'])->toBe('001');
        expect($transaction->meta['terminal_title'])->toBe('Test Terminal'); // From database
    });

    it('stores failed transaction with empty response', function () {
        $this->paymentType->order = $this->order;

        $response = ['data' => []];

        $reflection = new ReflectionClass($this->paymentType);
        $method = $reflection->getMethod('storeTerminalTransaction');
        $method->setAccessible(true);

        $transaction = $method->invoke($this->paymentType, $response);

        expect($transaction->success)->toBeFalse();
        expect($transaction->status)->toBe('failed');
        expect($transaction->notes)->toBe('No response data received');
    });

    it('determines transaction type correctly', function () {
        $reflection = new ReflectionClass($this->paymentType);
        $method = $reflection->getMethod('determineTransactionType');
        $method->setAccessible(true);

        // Test with @action field
        $saleData = ['@action' => 'sale'];
        expect($method->invoke($this->paymentType, $saleData))->toBe('capture');

        $authData = ['@action' => 'auth-only'];
        expect($method->invoke($this->paymentType, $authData))->toBe('intent');

        // Test with status code
        $capturedData = ['status_code' => 101]; // Captured
        expect($method->invoke($this->paymentType, $capturedData))->toBe('capture');

        $authorizedData = ['status_code' => 102]; // Authorized only
        expect($method->invoke($this->paymentType, $authorizedData))->toBe('intent');

        // Test default case
        $unknownData = [];
        expect($method->invoke($this->paymentType, $unknownData))->toBe('intent');
    });
});

afterEach(function () {
    Mockery::close();
});