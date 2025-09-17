<?php

use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('lunar-fortis.policy', 'manual');
    Config::set('lunar-fortis.status_mapping', [
        'payment-authorized' => 'payment-authorized',
        'payment-received' => 'payment-received',
    ]);

    // Set up required tables
    DB::statement('
        CREATE TABLE carts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NULL,
            total INTEGER DEFAULT 0,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )
    ');

    DB::statement('
        CREATE TABLE orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NULL,
            customer_id INTEGER NULL,
            reference VARCHAR(255),
            status VARCHAR(255),
            total INTEGER DEFAULT 0,
            placed_at TIMESTAMP NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )
    ');

    DB::statement('
        CREATE TABLE transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            parent_transaction_id INTEGER NULL,
            order_id INTEGER,
            success BOOLEAN,
            type VARCHAR(255),
            driver VARCHAR(255),
            amount INTEGER,
            reference VARCHAR(255),
            status VARCHAR(255),
            notes TEXT NULL,
            card_type VARCHAR(255),
            last_four VARCHAR(4),
            captured_at TIMESTAMP NULL,
            meta TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )
    ');

    $this->cart = new Cart();
    $this->cart->id = 1;
    $this->cart->total = (object) ['value' => 1000];

    $this->order = new Order();
    $this->order->id = 1;
    $this->order->reference = 'ORD-123';
    $this->order->customer_id = 456;
    $this->order->total = (object) ['value' => 1000];

    $this->mockFortis = Mockery::mock(LunarFortis::class);
    $this->paymentType = new FortisPaymentType($this->mockFortis);
    $this->paymentType = $this->paymentType->cart($this->cart)->order($this->order);
});

describe('FortisPaymentType Configuration', function () {
    it('sets policy from config', function () {
        Config::set('lunar-fortis.policy', 'automatic');
        $paymentType = new FortisPaymentType($this->mockFortis);

        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('automatic');
    });

    it('defaults to automatic policy when not configured', function () {
        Config::forget('lunar-fortis.policy');
        $paymentType = new FortisPaymentType($this->mockFortis);

        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('automatic');
    });
});

describe('FortisPaymentType Authorization - Manual Policy', function () {
    it('authorizes payment with successful transaction', function () {
        $transactionData = [
            '@action' => 'auth-only',
            'id' => 'trans-123',
            'status_code' => 102, // Authorized
            'transaction_amount' => 1000,
            'account_type' => 'credit',
            'last_four' => '1234',
            'auth_code' => 'AUTH123'
        ];

        $this->paymentType = $this->paymentType->withData($transactionData);

        $result = $this->paymentType->authorize();

        expect($result)->toBeInstanceOf(PaymentAuthorize::class);
        expect($result->success)->toBeTrue();
        expect($result->message)->toBe('Payment Authorized');
        expect($result->orderId)->toBe(1);

        // Check that transaction was stored
        $transaction = Transaction::where('reference', 'trans-123')->first();
        expect($transaction)->not->toBeNull();
        expect($transaction->type)->toBe('intent');
        expect($transaction->status)->toBe('authorized');
        expect($transaction->amount)->toBe(1000);
        expect($transaction->captured_at)->toBeNull();
    });

    it('handles captured transaction (sale)', function () {
        $transactionData = [
            '@action' => 'sale',
            'id' => 'sale-trans-123',
            'status_code' => 101, // Captured
            'transaction_amount' => 1500,
            'account_type' => 'credit',
            'last_four' => '5678'
        ];

        $this->paymentType = $this->paymentType->withData($transactionData);

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeTrue();
        expect($result->message)->toBe('Payment Captured');

        // Check that order is placed
        $this->order->refresh();
        expect($this->order->placed_at)->not->toBeNull();
        expect($this->order->status)->toBe('payment-received');

        // Check transaction
        $transaction = Transaction::where('reference', 'sale-trans-123')->first();
        expect($transaction->type)->toBe('capture');
        expect($transaction->status)->toBe('approved');
        expect($transaction->captured_at)->not->toBeNull();
    });

    it('handles failed transaction', function () {
        $transactionData = [
            '@action' => 'sale',
            'id' => 'failed-trans-123',
            'status_code' => 201, // Declined
            'transaction_amount' => 1000,
            'reason_code_id' => 1000,
            'verbiage' => 'Insufficient funds'
        ];

        $this->paymentType = $this->paymentType->withData($transactionData);

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeFalse();
        expect($result->message)->toContain('errors');

        $transaction = Transaction::where('reference', 'failed-trans-123')->first();
        expect($transaction->success)->toBeFalse();
        expect($transaction->status)->toBe('declined');
        expect($transaction->notes)->toContain('Insufficient funds');
    });
});

describe('FortisPaymentType Authorization - Automatic Policy', function () {
    beforeEach(function () {
        Config::set('lunar-fortis.policy', 'automatic');
        $this->paymentType = new FortisPaymentType($this->mockFortis);
        $this->paymentType = $this->paymentType->cart($this->cart)->order($this->order);
    });

    it('automatically captures authorized transaction', function () {
        $authData = [
            '@action' => 'auth-only',
            'id' => 'auth-trans-456',
            'status_code' => 102,
            'transaction_amount' => 2000,
            'account_type' => 'credit',
            'last_four' => '9012'
        ];

        $this->mockFortis->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->with(Mockery::type(Transaction::class), 2000)
            ->andReturn([
                'data' => [
                    'id' => 'captured-trans-456',
                    'status_code' => 101,
                    'transaction_amount' => 2000
                ]
            ]);

        $this->paymentType = $this->paymentType->withData($authData);

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeTrue();
        expect($result->message)->toBe('Payment Captured');

        // Should have both auth and capture transactions
        $transactions = Transaction::where('order_id', 1)->get();
        expect($transactions)->toHaveCount(2);
        expect($transactions->where('type', 'intent'))->toHaveCount(1);
        expect($transactions->where('type', 'capture'))->toHaveCount(1);

        $this->order->refresh();
        expect($this->order->placed_at)->not->toBeNull();
    });

    it('handles automatic capture failure', function () {
        $authData = [
            '@action' => 'auth-only',
            'id' => 'auth-fail-capture',
            'status_code' => 102,
            'transaction_amount' => 1000,
            'account_type' => 'credit',
            'last_four' => '1111'
        ];

        $this->mockFortis->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->andThrow(new Exception('Capture failed'));

        $this->paymentType = $this->paymentType->withData($authData);

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('Capture failed');
    });
});

describe('FortisPaymentType Capture', function () {
    it('captures authorized transaction successfully', function () {
        $authTransaction = Transaction::create([
            'order_id' => 1,
            'success' => true,
            'type' => 'intent',
            'driver' => 'fortis',
            'amount' => 1500,
            'reference' => 'auth-trans-capture',
            'status' => 'authorized',
            'card_type' => 'credit',
            'last_four' => '2468'
        ]);

        $this->mockFortis->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->with($authTransaction, 1500)
            ->andReturn([
                'data' => [
                    'id' => 'captured-trans-capture',
                    'status_code' => 101,
                    'transaction_amount' => 1500,
                    'account_type' => 'credit',
                    'last_four' => '2468'
                ]
            ]);

        $result = $this->paymentType->capture($authTransaction, 1500);

        expect($result)->toBeInstanceOf(PaymentCapture::class);
        expect($result->success)->toBeTrue();
        expect($result->message)->toBe('Payment captured successfully');

        // Check capture transaction was created
        $captureTransaction = Transaction::where('parent_transaction_id', $authTransaction->id)->first();
        expect($captureTransaction)->not->toBeNull();
        expect($captureTransaction->type)->toBe('capture');
        expect($captureTransaction->success)->toBeTrue();
    });

    it('handles capture failure', function () {
        $authTransaction = Transaction::create([
            'order_id' => 1,
            'success' => true,
            'type' => 'intent',
            'driver' => 'fortis',
            'amount' => 1000,
            'reference' => 'auth-trans-fail',
            'status' => 'authorized'
        ]);

        $this->mockFortis->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->andThrow(new Exception('Network error'));

        $result = $this->paymentType->capture($authTransaction, 1000);

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('Network error');
    });
});

describe('FortisPaymentType Refund', function () {
    it('processes successful refund', function () {
        $originalTransaction = Transaction::create([
            'order_id' => 1,
            'success' => true,
            'type' => 'capture',
            'driver' => 'fortis',
            'amount' => 2000,
            'reference' => 'original-trans-refund',
            'status' => 'approved',
            'card_type' => 'credit',
            'last_four' => '3579'
        ]);

        $this->mockFortis->shouldReceive('refund')
            ->once()
            ->with($originalTransaction, 500)
            ->andReturn([
                'data' => [
                    'id' => 'refund-trans-123',
                    'status_code' => 111, // Refunded
                    'reason_code_id' => 1000, // Approved
                    'transaction_amount' => 500,
                    'account_type' => 'credit',
                    'last_four' => '3579'
                ]
            ]);

        $result = $this->paymentType->refund($originalTransaction, 500);

        expect($result)->toBeInstanceOf(PaymentRefund::class);
        expect($result->success)->toBeTrue();

        // Check refund transaction was created
        $refundTransaction = Transaction::where('parent_transaction_id', $originalTransaction->id)
            ->where('type', 'refund')->first();
        expect($refundTransaction)->not->toBeNull();
        expect($refundTransaction->success)->toBeTrue();
        expect($refundTransaction->status)->toBe('refunded');
        expect($refundTransaction->amount)->toBe(500);
    });

    it('handles failed refund', function () {
        $originalTransaction = Transaction::create([
            'order_id' => 1,
            'success' => true,
            'type' => 'capture',
            'driver' => 'fortis',
            'amount' => 1000,
            'reference' => 'original-trans-fail-refund',
            'status' => 'approved'
        ]);

        $this->mockFortis->shouldReceive('refund')
            ->once()
            ->andReturn([
                'data' => [
                    'id' => 'failed-refund-123',
                    'status_code' => 201, // Declined
                    'reason_code_id' => 2000, // Declined reason
                    'reason_code' => 'Refund not allowed',
                    'transaction_amount' => 1000
                ]
            ]);

        $result = $this->paymentType->refund($originalTransaction, 1000);

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('Refund not allowed');

        // Check failed refund transaction was created
        $refundTransaction = Transaction::where('parent_transaction_id', $originalTransaction->id)
            ->where('type', 'refund')->first();
        expect($refundTransaction)->not->toBeNull();
        expect($refundTransaction->success)->toBeFalse();
        expect($refundTransaction->status)->toBe('declined');
    });

    it('handles refund API exception', function () {
        $originalTransaction = Transaction::create([
            'order_id' => 1,
            'success' => true,
            'type' => 'capture',
            'driver' => 'fortis',
            'amount' => 1000,
            'reference' => 'original-trans-exception',
            'status' => 'approved'
        ]);

        $this->mockFortis->shouldReceive('refund')
            ->once()
            ->andThrow(new Exception('API connection failed'));

        $result = $this->paymentType->refund($originalTransaction, 1000);

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('API connection failed');
    });
});

describe('FortisPaymentType Meta Data Building', function () {
    it('builds meta array with AVS and CVV validation', function () {
        $transactionData = [
            '@action' => 'sale',
            'id' => 'meta-test-123',
            'status_code' => 101,
            'transaction_amount' => 1000,
            'avs' => 'Y', // Good AVS
            'cvv_response' => 'M', // Match
            'auth_code' => 'AUTH456',
            'first_six' => '424242',
            'account_holder_name' => 'John Doe'
        ];

        $this->paymentType = $this->paymentType->withData($transactionData);
        $this->paymentType->authorize();

        $transaction = Transaction::where('reference', 'meta-test-123')->first();
        $meta = json_decode($transaction->meta, true);

        expect($meta['@action'])->toBe('sale');
        expect($meta['auth_code'])->toBe('AUTH456');
        expect($meta['first_six'])->toBe('424242');
        expect($meta['account_holder_name'])->toBe('John Doe');
        expect($meta)->not->toHaveKey('errors'); // Good AVS/CVV shouldn't create errors
    });

    it('identifies AVS failures', function () {
        $transactionData = [
            '@action' => 'sale',
            'id' => 'avs-fail-123',
            'status_code' => 101,
            'transaction_amount' => 1000,
            'avs' => 'N', // AVS failure
            'cvv_response' => 'M'
        ];

        $this->paymentType = $this->paymentType->withData($transactionData);
        $this->paymentType->authorize();

        $transaction = Transaction::where('reference', 'avs-fail-123')->first();
        $meta = json_decode($transaction->meta, true);

        expect($meta['errors'])->toContain('AVS Failed');
    });

    it('identifies CVV failures', function () {
        $transactionData = [
            '@action' => 'sale',
            'id' => 'cvv-fail-123',
            'status_code' => 101,
            'transaction_amount' => 1000,
            'avs' => 'Y',
            'cvv_response' => 'N' // CVV failure
        ];

        $this->paymentType = $this->paymentType->withData($transactionData);
        $this->paymentType->authorize();

        $transaction = Transaction::where('reference', 'cvv-fail-123')->first();
        $meta = json_decode($transaction->meta, true);

        expect($meta['errors'])->toContain('CVV Failed');
    });

    it('handles unknown status codes with reason codes', function () {
        $transactionData = [
            '@action' => 'sale',
            'id' => 'unknown-status-123',
            'status_code' => 999, // Unknown status
            'transaction_amount' => 1000,
            'reason_code_id' => 1001,
            'verbiage' => 'Custom error message'
        ];

        $this->paymentType = $this->paymentType->withData($transactionData);
        $this->paymentType->authorize();

        $transaction = Transaction::where('reference', 'unknown-status-123')->first();
        $meta = json_decode($transaction->meta, true);

        expect($meta['errors'])->toContain('Custom error message');
    });
});

describe('FortisPaymentType Order Management', function () {
    it('creates order when cart has no draft order', function () {
        $newCart = new Cart();
        $newCart->id = 2;
        $newCart->total = (object) ['value' => 1500];
        $newCart->draftOrder = null;
        $newCart->completedOrder = null;

        $transactionData = [
            '@action' => 'sale',
            'id' => 'new-order-123',
            'status_code' => 101,
            'transaction_amount' => 1500
        ];

        // Mock cart createOrder method
        $newOrder = new Order();
        $newOrder->id = 2;
        $newOrder->reference = 'NEW-ORD-456';
        $newOrder->total = (object) ['value' => 1500];

        $mockCart = Mockery::mock($newCart);
        $mockCart->shouldReceive('createOrder')->once()->andReturn($newOrder);
        $mockCart->total = (object) ['value' => 1500];

        $paymentType = new FortisPaymentType($this->mockFortis);
        $paymentType = $paymentType->cart($mockCart)->withData($transactionData);

        $result = $paymentType->authorize();

        expect($result->success)->toBeTrue();
        expect($result->orderId)->toBe(2);
    });

    it('prevents processing already placed orders', function () {
        $this->order->placed_at = now();
        $this->order->save();

        $transactionData = [
            '@action' => 'sale',
            'id' => 'placed-order-123',
            'status_code' => 101,
            'transaction_amount' => 1000
        ];

        $this->paymentType = $this->paymentType->withData($transactionData);

        $result = $this->paymentType->authorize();

        expect($result->success)->toBeFalse();
        expect($result->message)->toBe('This order has already been placed');
    });
});

afterEach(function () {
    Mockery::close();
});