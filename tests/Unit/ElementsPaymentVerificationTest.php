<?php

use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

uses(InteractsWithLunarCarts::class);

const FORTIS_API = 'api.sandbox.fortis.tech/v1/transactions';

beforeEach(function () {
    $this->setUpLunar();

    Http::preventStrayRequests();

    $this->cart = $this->makeCart(1000);
});

function authorizeElementsPayment(Cart $cart, array $payload, string $policy = 'automatic'): PaymentAuthorize
{
    Config::set('lunar-fortis.policy', $policy);

    return app(FortisPaymentType::class)
        ->cart($cart)
        ->withData($payload)
        ->authorize();
}

function elementsPayload(array $overrides = []): array
{
    return array_merge([
        'id' => 'txn0000000000000000000001',
        '@action' => 'auth-only',
        'status_code' => 102,
        'transaction_amount' => 1000,
        'account_type' => 'visa',
        'last_four' => '1111',
    ], $overrides);
}

function fortisRecord(array $overrides = []): array
{
    return array_merge([
        'id' => 'txn0000000000000000000001',
        'status_code' => 102,
        'transaction_amount' => 1000,
        'account_type' => 'visa',
        'last_four' => '4242',
    ], $overrides);
}

function fakeFortisLookup(array $record, ?array $captureRecord = null): void
{
    $id = $record['id'];

    Http::fake([
        FORTIS_API."/{$id}/auth-complete" => Http::response([
            'data' => $captureRecord ?? array_merge($record, ['status_code' => 101]),
        ]),
        FORTIS_API."/{$id}" => Http::response(['data' => $record]),
    ]);
}

function fakeUnknownFortisTransaction(): void
{
    Http::fake([
        FORTIS_API.'/*' => Http::response(['detail' => 'Not found'], 404),
    ]);
}

function orderFor(Cart $cart): ?Order
{
    return Order::where('cart_id', $cart->id)->first();
}

function successfulTransactionsFor(Cart $cart): int
{
    $order = orderFor($cart);

    if (! $order) {
        return 0;
    }

    return Transaction::where('order_id', $order->id)->where('success', true)->count();
}

function assertTransactionLookedUp(string $id): void
{
    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && str_ends_with($request->url(), "/v1/transactions/{$id}"));
}

describe('forged Elements payloads', function () {
    it('does not place an order for a forged sale payload', function () {
        fakeUnknownFortisTransaction();

        $result = authorizeElementsPayment($this->cart, elementsPayload([
            'id' => 'forgedsale0000000000000001',
            '@action' => 'sale',
            'status_code' => 101,
        ]));

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)?->placed_at)->toBeNull()
            ->and(successfulTransactionsFor($this->cart))->toBe(0);
    });

    it('does not authorize an order when Fortis authorized less than the cart total', function () {
        fakeFortisLookup(fortisRecord(['transaction_amount' => 100]));

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)->status)->not->toBe('payment-authorized')
            ->and(successfulTransactionsFor($this->cart))->toBe(0);
    });

    it('does not authorize an order when Fortis declined the transaction', function () {
        fakeFortisLookup(fortisRecord(['status_code' => 301, 'reason_code_id' => 1500]));

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)->status)->not->toBe('payment-authorized')
            ->and(successfulTransactionsFor($this->cart))->toBe(0);
    });

    it('rejects a sale transaction because the intention only requests auth-only', function () {
        fakeFortisLookup(fortisRecord(['status_code' => 101]));

        $result = authorizeElementsPayment($this->cart, elementsPayload([
            '@action' => 'sale',
            'status_code' => 101,
        ]));

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)?->placed_at)->toBeNull()
            ->and(successfulTransactionsFor($this->cart))->toBe(0);
    });

    it('rejects a genuine transaction id already recorded against another order', function () {
        $otherCart = $this->makeCart(1000);
        $otherOrder = $otherCart->createOrder();

        Transaction::create([
            'order_id' => $otherOrder->id,
            'success' => true,
            'type' => 'intent',
            'driver' => FortisPaymentType::PAYMENT_TYPE,
            'amount' => 1000,
            'reference' => 'txn0000000000000000000001',
            'status' => 'authorized',
            'card_type' => 'visa',
            'last_four' => '4242',
        ]);

        fakeFortisLookup(fortisRecord());

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)->status)->not->toBe('payment-authorized')
            ->and(Transaction::where('reference', 'txn0000000000000000000001')->count())->toBe(1);
    });

    it('rejects a transaction id that is already being verified by another request', function () {
        fakeFortisLookup(fortisRecord());

        $lock = Cache::lock('lunar-fortis:elements-transaction:txn0000000000000000000001', 30);
        $lock->get();

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        $lock->release();

        expect($result->success)->toBeFalse()
            ->and(successfulTransactionsFor($this->cart))->toBe(0);
    });

    it('rejects a lookup that returns a different transaction than the one requested', function () {
        Http::fake([
            FORTIS_API.'/txn0000000000000000000001' => Http::response([
                'data' => fortisRecord(['id' => 'txn0000000000000000000999']),
            ]),
        ]);

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)->status)->not->toBe('payment-authorized')
            ->and(successfulTransactionsFor($this->cart))->toBe(0);
    });

    it('rejects a malformed transaction id without calling Fortis', function () {
        Http::fake();

        $result = authorizeElementsPayment($this->cart, elementsPayload(['id' => '../terminals']));

        expect($result->success)->toBeFalse()
            ->and(successfulTransactionsFor($this->cart))->toBe(0);

        Http::assertNothingSent();
    });

    it('does not place an order when the Livewire listener receives a forged sale payload', function () {
        Config::set('lunar-fortis.policy', 'automatic');
        fakeUnknownFortisTransaction();

        $form = new PaymentForm;
        $form->cart = $this->cart;
        $form->mount();

        $form->handlePaymentResponse(['data' => elementsPayload([
            'id' => 'forgedsale0000000000000001',
            '@action' => 'sale',
            'status_code' => 101,
        ])]);

        expect(orderFor($this->cart)?->placed_at)->toBeNull()
            ->and(successfulTransactionsFor($this->cart))->toBe(0);
    });
});

describe('genuine Elements payments', function () {
    it('verifies the transaction with Fortis and captures it under the automatic policy', function () {
        fakeFortisLookup(fortisRecord());

        $result = authorizeElementsPayment($this->cart, elementsPayload());

        $order = orderFor($this->cart);
        $intent = Transaction::where('order_id', $order->id)->where('type', 'intent')->sole();

        expect($result->success)->toBeTrue()
            ->and($order->placed_at)->not->toBeNull()
            ->and($order->status)->toBe('payment-received')
            ->and($intent->reference)->toBe('txn0000000000000000000001')
            ->and($intent->amount->value)->toBe(1000)
            ->and($intent->last_four)->toBe('4242')
            ->and(Transaction::where('order_id', $order->id)->where('type', 'capture')->where('success', true)->count())->toBe(1);

        assertTransactionLookedUp('txn0000000000000000000001');
    });

    it('verifies the transaction with Fortis and records an intent under the manual policy', function () {
        fakeFortisLookup(fortisRecord());

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        $order = orderFor($this->cart);
        $intent = Transaction::where('order_id', $order->id)->sole();

        expect($result->success)->toBeTrue()
            ->and($order->placed_at)->toBeNull()
            ->and($order->status)->toBe('payment-authorized')
            ->and($intent->type)->toBe('intent')
            ->and((bool) $intent->success)->toBeTrue()
            ->and($intent->last_four)->toBe('4242');

        assertTransactionLookedUp('txn0000000000000000000001');
    });

    it('accepts a payload that carries only the transaction id', function () {
        fakeFortisLookup(fortisRecord());

        $result = authorizeElementsPayment($this->cart, ['id' => 'txn0000000000000000000001'], 'manual');

        expect($result->success)->toBeTrue()
            ->and(orderFor($this->cart)->status)->toBe('payment-authorized');
    });

    it('places the order from the Livewire listener when no success event class is configured', function () {
        Config::set('lunar-fortis.policy', 'automatic');
        Config::set('lunar-fortis.success_event_class', null);
        Config::set('lunar-fortis.success_redirect', false);
        fakeFortisLookup(fortisRecord());

        $this->withoutDeprecationHandling();

        $form = new PaymentForm;
        $form->cart = $this->cart;
        $form->mount();

        $form->handlePaymentResponse(['data' => elementsPayload()]);

        expect(orderFor($this->cart)->placed_at)->not->toBeNull();
    });
});

describe('driver state across requests', function () {
    it('does not reuse the previous buyers transaction id when the next request omits data', function () {
        Config::set('lunar-fortis.policy', 'manual');
        fakeFortisLookup(fortisRecord());

        $firstBuyerCart = $this->makeCart(2000);

        Payments::driver(FortisPaymentType::PAYMENT_TYPE)
            ->cart($firstBuyerCart)
            ->withData(elementsPayload())
            ->authorize();

        $result = Payments::driver(FortisPaymentType::PAYMENT_TYPE)
            ->cart($this->cart)
            ->authorize();

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart))->toBeNull()
            ->and(Http::recorded()->count())->toBe(1);
    });

    it('does not reuse the previous buyers cart when the next request omits the cart', function () {
        Config::set('lunar-fortis.policy', 'manual');
        fakeFortisLookup(fortisRecord());
        fakeFortisLookup(fortisRecord(['id' => 'txn0000000000000000000002']));

        Payments::driver(FortisPaymentType::PAYMENT_TYPE)
            ->cart($this->cart)
            ->withData(elementsPayload())
            ->authorize();

        $result = Payments::driver(FortisPaymentType::PAYMENT_TYPE)
            ->withData(elementsPayload(['id' => 'txn0000000000000000000002']))
            ->authorize();

        expect($result->success)->toBeFalse()
            ->and(Transaction::where('order_id', orderFor($this->cart)->id)->count())->toBe(1);

        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), 'txn0000000000000000000002'));
    });
});

describe('numeric fields returned by Fortis', function () {
    it('accepts a status code and amount returned as integer strings', function () {
        fakeFortisLookup(fortisRecord(['status_code' => '102', 'transaction_amount' => '1000']));

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        $intent = Transaction::where('order_id', orderFor($this->cart)->id)->sole();

        expect($result->success)->toBeTrue()
            ->and($intent->amount->value)->toBe(1000)
            ->and($intent->status)->toBe('authorized');
    });

    it('rejects an amount that is not a whole number of minor units', function (mixed $amount) {
        fakeFortisLookup(fortisRecord(['transaction_amount' => $amount]));

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)->status)->not->toBe('payment-authorized')
            ->and(successfulTransactionsFor($this->cart))->toBe(0);
    })->with([
        'decimal string' => ['10.50'],
        'decimal string of the total' => ['1000.00'],
        'negative int' => [-1000],
        'negative string' => ['-1000'],
        'thousands separator' => ['1,000'],
        'padded string' => [' 1000'],
        'non-numeric' => ['abc'],
        'empty string' => [''],
        'null' => [null],
        'bool' => [true],
    ]);

    it('rejects numbers that Fortis returns as JSON floats', function (string $json) {
        Http::fake([
            FORTIS_API.'/txn0000000000000000000001' => Http::response($json, 200, ['Content-Type' => 'application/json']),
        ]);

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)->status)->not->toBe('payment-authorized')
            ->and(Transaction::count())->toBe(0);
    })->with([
        'amount' => ['{"data":{"id":"txn0000000000000000000001","status_code":102,"transaction_amount":1000.0}}'],
        'fractional amount' => ['{"data":{"id":"txn0000000000000000000001","status_code":102,"transaction_amount":1000.5}}'],
        'status code' => ['{"data":{"id":"txn0000000000000000000001","status_code":102.0,"transaction_amount":1000}}'],
    ]);

    it('rejects a status code that is not a whole number', function (mixed $statusCode) {
        fakeFortisLookup(fortisRecord(['status_code' => $statusCode]));

        $result = authorizeElementsPayment($this->cart, elementsPayload(), 'manual');

        expect($result->success)->toBeFalse()
            ->and(orderFor($this->cart)->status)->not->toBe('payment-authorized')
            ->and(Transaction::count())->toBe(0);
    })->with([
        'decimal string' => ['102.0'],
        'negative string' => ['-102'],
        'negative int' => [-102],
        'non-numeric' => ['abc'],
        'empty string' => [''],
        'null' => [null],
    ]);
});
