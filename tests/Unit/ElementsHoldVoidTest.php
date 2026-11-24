<?php

use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\Services\ElementsTransactionBinding;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Mockery;

uses(InteractsWithLunarCarts::class);

const HOLD_TRANSACTIONS_API = 'api.sandbox.fortis.tech/v1/transactions';

const HOLD_TRANSACTION_ID = 'txnhold000000000000000001';

const SECOND_HOLD_TRANSACTION_ID = 'txnhold000000000000000002';

beforeEach(function () {
    $this->setUpLunar();

    Http::preventStrayRequests();

    Config::set('lunar-fortis.elements.require_transaction_binding', true);

    $this->cart = $this->makeCart(1000);
    $this->binding = app(ElementsTransactionBinding::class);
});

function authorizeHold(Cart $cart, string $transactionId = HOLD_TRANSACTION_ID, string $policy = 'manual'): PaymentAuthorize
{
    Config::set('lunar-fortis.policy', $policy);

    return app(FortisPaymentType::class)
        ->cart($cart)
        ->withData(['id' => $transactionId])
        ->authorize();
}

function holdRecord(array $overrides = []): array
{
    return array_merge([
        'id' => HOLD_TRANSACTION_ID,
        'status_code' => 102,
        'transaction_amount' => 1000,
        'account_type' => 'visa',
        'last_four' => '4242',
    ], $overrides);
}

/** @param array<int, array<string, mixed>> $records */
function fakeHolds(array $records, ?Closure $void = null): void
{
    $fakes = [];

    foreach ($records as $record) {
        $fakes[HOLD_TRANSACTIONS_API."/{$record['id']}/void"] = $void ?? fn () => Http::response([
            'data' => ['id' => $record['id'], 'status_code' => 201],
        ]);
    }

    foreach ($records as $record) {
        $fakes[HOLD_TRANSACTIONS_API."/{$record['id']}/auth-complete"] = Http::response([
            'data' => array_merge($record, ['status_code' => 101]),
        ]);
        $fakes[HOLD_TRANSACTIONS_API."/{$record['id']}*"] = Http::response(['data' => $record]);
    }

    Http::fake($fakes);
}

function holdVoidSent(string $transactionId = HOLD_TRANSACTION_ID): bool
{
    return Http::recorded(fn (Request $request) => $request->method() === 'PUT'
        && str_ends_with((string) parse_url($request->url(), PHP_URL_PATH), "/v1/transactions/{$transactionId}/void"))->isNotEmpty();
}

function holdOrderFor(Cart $cart): ?Order
{
    return Order::where('cart_id', $cart->id)->first();
}

function recordHoldOnOrder(Order $order, string $reference): void
{
    Transaction::create([
        'order_id' => $order->id,
        'success' => true,
        'type' => 'intent',
        'driver' => FortisPaymentType::PAYMENT_TYPE,
        'amount' => 1000,
        'reference' => $reference,
        'status' => 'authorized',
        'card_type' => 'visa',
        'last_four' => '4242',
    ]);
}

describe('a hold refused because the amount no longer matches the cart', function () {
    it('voids the hold when its transaction api id was issued for this cart', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds([holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])]);

        $result = authorizeHold($this->cart);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment amount does not match the order total.')
            ->and(holdVoidSent())->toBeTrue()
            ->and($this->binding->isIssued($this->cart, $transactionApiId))->toBeFalse()
            ->and(Transaction::where('reference', HOLD_TRANSACTION_ID)->count())->toBe(0);
    });

    it('voids the hold even when the binding check is turned off', function () {
        Config::set('lunar-fortis.elements.require_transaction_binding', false);
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds([holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])]);

        $result = authorizeHold($this->cart);

        expect($result->success)->toBeFalse()
            ->and(holdVoidSent())->toBeTrue();
    });

    it('leaves the hold alone when its transaction api id was issued for another cart', function () {
        $otherCart = $this->makeCart(1000);
        $this->binding->issue($this->cart);
        $foreignApiId = $this->binding->issue($otherCart);
        fakeHolds([holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $foreignApiId])]);

        $result = authorizeHold($this->cart);

        expect($result->success)->toBeFalse()
            ->and(holdVoidSent())->toBeFalse()
            ->and($this->binding->isIssued($otherCart, $foreignApiId))->toBeTrue();
    });

    it('leaves the hold alone when it carries no transaction api id', function () {
        Config::set('lunar-fortis.elements.require_transaction_binding', false);
        $this->binding->issue($this->cart);
        fakeHolds([holdRecord(['transaction_amount' => 900])]);

        $result = authorizeHold($this->cart);

        expect($result->message)->toBe('The payment amount does not match the order total.')
            ->and(holdVoidSent())->toBeFalse();
    });

    it('leaves a captured sale alone', function (int $statusCode) {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds([holdRecord([
            'status_code' => $statusCode,
            'transaction_amount' => 900,
            'transaction_api_id' => $transactionApiId,
        ])]);

        $result = authorizeHold($this->cart);

        expect($result->message)->toBe('The payment amount does not match the order total.')
            ->and(holdVoidSent())->toBeFalse()
            ->and($this->binding->isIssued($this->cart, $transactionApiId))->toBeTrue();
    })->with([
        'approved sale' => [101],
        'settled sale' => [191],
    ]);
});

describe('refusals that never void', function () {
    it('leaves a hold alone when the lookup returns a different transaction', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        Http::fake([
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'/void' => Http::response(['data' => ['status_code' => 201]]),
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'*' => Http::response(['data' => holdRecord([
                'id' => SECOND_HOLD_TRANSACTION_ID,
                'transaction_api_id' => $transactionApiId,
            ])]),
        ]);

        $result = authorizeHold($this->cart);

        expect($result->message)->toBe('The payment could not be verified.')
            ->and(holdVoidSent())->toBeFalse()
            ->and(holdVoidSent(SECOND_HOLD_TRANSACTION_ID))->toBeFalse();
    });

    it('leaves a hold alone when it is already recorded against another order', function () {
        $otherOrder = $this->makeCart(1000)->createOrder();
        recordHoldOnOrder($otherOrder, HOLD_TRANSACTION_ID);
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds([holdRecord(['transaction_api_id' => $transactionApiId])]);

        $result = authorizeHold($this->cart);

        expect($result->message)->toBe('This payment has already been used.')
            ->and(holdVoidSent())->toBeFalse();
    });

    it('leaves the payment alone when the same payment is submitted again', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds([holdRecord(['transaction_api_id' => $transactionApiId])]);
        authorizeHold($this->cart);

        $result = authorizeHold($this->cart);

        expect($result->message)->toBe('This order already has an authorized payment.')
            ->and(holdVoidSent())->toBeFalse()
            ->and(holdOrderFor($this->cart)->status)->toBe('payment-authorized')
            ->and(Http::recorded(fn (Request $request) => $request->method() === 'GET')->count())->toBe(1);
    });

    it('leaves a hold alone when it is recorded against another order and offered to an order that already has a payment', function () {
        $firstApiId = $this->binding->issue($this->cart);
        $secondApiId = $this->binding->issue($this->cart);
        fakeHolds([
            holdRecord(['transaction_api_id' => $firstApiId]),
            holdRecord(['id' => SECOND_HOLD_TRANSACTION_ID, 'transaction_api_id' => $secondApiId]),
        ]);
        authorizeHold($this->cart);
        recordHoldOnOrder($this->makeCart(1000)->createOrder(), SECOND_HOLD_TRANSACTION_ID);

        $result = authorizeHold($this->cart, SECOND_HOLD_TRANSACTION_ID);

        expect($result->message)->toBe('This order already has an authorized payment.')
            ->and(holdVoidSent(SECOND_HOLD_TRANSACTION_ID))->toBeFalse()
            ->and(Http::recorded(fn (Request $request) => str_contains($request->url(), SECOND_HOLD_TRANSACTION_ID)))->toHaveCount(0);
    });
});

describe('a hold recorded while it is being checked', function () {
    it('leaves the hold alone when another request records it during the lookup', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        $otherOrder = $this->makeCart(1000)->createOrder();

        Http::fake([
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'/void' => Http::response(['data' => ['id' => HOLD_TRANSACTION_ID, 'status_code' => 201]]),
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'*' => function () use ($otherOrder, $transactionApiId) {
                recordHoldOnOrder($otherOrder, HOLD_TRANSACTION_ID);

                return Http::response(['data' => holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])]);
            },
        ]);

        $result = authorizeHold($this->cart);

        expect($result->message)->toBe('The payment amount does not match the order total.')
            ->and(holdVoidSent())->toBeFalse();
    });

    it('leaves the hold alone when it is recorded under another payment driver', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        $otherOrder = $this->makeCart(1000)->createOrder();

        Http::fake([
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'/void' => Http::response(['data' => ['id' => HOLD_TRANSACTION_ID, 'status_code' => 201]]),
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'*' => function () use ($otherOrder, $transactionApiId) {
                Transaction::create([
                    'order_id' => $otherOrder->id,
                    'success' => true,
                    'type' => 'intent',
                    'driver' => 'imported-fortis',
                    'amount' => 1000,
                    'reference' => HOLD_TRANSACTION_ID,
                    'status' => 'authorized',
                    'card_type' => 'visa',
                    'last_four' => '4242',
                ]);

                return Http::response(['data' => holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])]);
            },
        ]);

        authorizeHold($this->cart);

        expect(holdVoidSent())->toBeFalse();
    });
});

describe('a hold refused because the cart can no longer become an order', function () {
    it('voids the hold when its transaction api id was issued for this cart', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        $this->cart->addresses()->delete();
        fakeHolds([holdRecord(['transaction_api_id' => $transactionApiId])]);

        $result = authorizeHold($this->cart->refresh());

        expect($result->success)->toBeFalse()
            ->and(holdVoidSent())->toBeTrue()
            ->and(holdOrderFor($this->cart))->toBeNull();
    });

    it('leaves the hold alone when its transaction api id was issued for another cart', function () {
        $this->binding->issue($this->cart);
        $foreignApiId = $this->binding->issue($this->makeCart(1000));
        $this->cart->addresses()->delete();
        fakeHolds([holdRecord(['transaction_api_id' => $foreignApiId])]);

        $result = authorizeHold($this->cart->refresh());

        expect($result->success)->toBeFalse()
            ->and(holdVoidSent())->toBeFalse();
    });
});

describe('a second hold for an order that already has a payment', function () {
    it('voids the second hold when it was issued for this cart', function () {
        $firstApiId = $this->binding->issue($this->cart);
        $secondApiId = $this->binding->issue($this->cart);
        fakeHolds([
            holdRecord(['transaction_api_id' => $firstApiId]),
            holdRecord(['id' => SECOND_HOLD_TRANSACTION_ID, 'transaction_api_id' => $secondApiId]),
        ]);
        authorizeHold($this->cart);

        $result = authorizeHold($this->cart, SECOND_HOLD_TRANSACTION_ID);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('This order already has an authorized payment.')
            ->and(holdVoidSent(SECOND_HOLD_TRANSACTION_ID))->toBeTrue()
            ->and(holdVoidSent())->toBeFalse()
            ->and(holdOrderFor($this->cart)->status)->toBe('payment-authorized')
            ->and(Cache::lock('lunar-fortis:elements-transaction:'.SECOND_HOLD_TRANSACTION_ID, 30)->get())->toBeTrue();
    });

    it('leaves the second hold alone when it was issued for another cart', function () {
        $firstApiId = $this->binding->issue($this->cart);
        $foreignApiId = $this->binding->issue($this->makeCart(1000));
        fakeHolds([
            holdRecord(['transaction_api_id' => $firstApiId]),
            holdRecord(['id' => SECOND_HOLD_TRANSACTION_ID, 'transaction_api_id' => $foreignApiId]),
        ]);
        authorizeHold($this->cart);

        authorizeHold($this->cart, SECOND_HOLD_TRANSACTION_ID);

        expect(holdVoidSent(SECOND_HOLD_TRANSACTION_ID))->toBeFalse();
    });

    it('does not look up the second transaction when no id was ever issued for this cart', function () {
        Config::set('lunar-fortis.elements.require_transaction_binding', false);
        fakeHolds([
            holdRecord(),
            holdRecord(['id' => SECOND_HOLD_TRANSACTION_ID]),
        ]);
        authorizeHold($this->cart);

        $result = authorizeHold($this->cart, SECOND_HOLD_TRANSACTION_ID);

        expect($result->message)->toBe('This order already has an authorized payment.')
            ->and(Http::recorded(fn (Request $request) => str_contains($request->url(), SECOND_HOLD_TRANSACTION_ID)))->toHaveCount(0);
    });

    it('leaves the second hold alone while another request is verifying it', function () {
        $firstApiId = $this->binding->issue($this->cart);
        $secondApiId = $this->binding->issue($this->cart);
        fakeHolds([
            holdRecord(['transaction_api_id' => $firstApiId]),
            holdRecord(['id' => SECOND_HOLD_TRANSACTION_ID, 'transaction_api_id' => $secondApiId]),
        ]);
        authorizeHold($this->cart);

        $lock = Cache::lock('lunar-fortis:elements-transaction:'.SECOND_HOLD_TRANSACTION_ID, 30);
        $lock->get();

        $result = authorizeHold($this->cart, SECOND_HOLD_TRANSACTION_ID);

        $lock->release();

        expect($result->message)->toBe('This order already has an authorized payment.')
            ->and(holdVoidSent(SECOND_HOLD_TRANSACTION_ID))->toBeFalse();
    });

    it('still refuses the second hold when Fortis cannot be reached to check it', function () {
        $firstApiId = $this->binding->issue($this->cart);
        $this->binding->issue($this->cart);
        Http::fake([
            HOLD_TRANSACTIONS_API.'/'.SECOND_HOLD_TRANSACTION_ID.'*' => Http::response(['detail' => 'Unavailable'], 500),
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'*' => Http::response(['data' => holdRecord(['transaction_api_id' => $firstApiId])]),
        ]);
        authorizeHold($this->cart);

        $result = authorizeHold($this->cart, SECOND_HOLD_TRANSACTION_ID);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('This order already has an authorized payment.')
            ->and(holdVoidSent(SECOND_HOLD_TRANSACTION_ID))->toBeFalse()
            ->and(Cache::lock('lunar-fortis:elements-transaction:'.SECOND_HOLD_TRANSACTION_ID, 30)->get())->toBeTrue();
    });
});

describe('the transaction lock', function () {
    it('is still held when a slow lookup returns within the HTTP budget', function (bool $exponentialBackoff, int $secondsPerCall) {
        Config::set('lunar-fortis.http.timeout', 30);
        Config::set('lunar-fortis.http.retry.attempts', 3);
        Config::set('lunar-fortis.http.retry.delay', 1000);
        Config::set('lunar-fortis.http.retry.exponential_backoff', $exponentialBackoff);
        Config::set('lunar-fortis.http.retry.max_delay', 60000);
        $transactionApiId = $this->binding->issue($this->cart);
        $heldDuringVoid = null;

        Http::fake([
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'/void' => function () use (&$heldDuringVoid, $secondsPerCall) {
                $this->travel($secondsPerCall)->seconds();
                $heldDuringVoid = ! Cache::lock('lunar-fortis:elements-transaction:'.HOLD_TRANSACTION_ID, 10)->get();

                return Http::response(['data' => ['id' => HOLD_TRANSACTION_ID, 'status_code' => 201]]);
            },
            HOLD_TRANSACTIONS_API.'/'.HOLD_TRANSACTION_ID.'*' => function () use ($transactionApiId, $secondsPerCall) {
                $this->travel($secondsPerCall)->seconds();

                return Http::response(['data' => holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])]);
            },
        ]);

        authorizeHold($this->cart);

        expect($heldDuringVoid)->toBeTrue();
    })->with([
        'fixed delay' => [false, 92],
        'exponential backoff' => [true, 210],
    ]);
});

describe('a void that goes through', function () {
    it('releases the issued id when Fortis reports the voided status as a string', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds(
            [holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])],
            fn () => Http::response(['data' => ['id' => HOLD_TRANSACTION_ID, 'status_code' => '201']]),
        );

        authorizeHold($this->cart);

        expect($this->binding->isIssued($this->cart, $transactionApiId))->toBeFalse();
    });
});

describe('a void that does not go through', function () {
    it('keeps the refusal and the issued id when Fortis rejects the void', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds(
            [holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])],
            fn () => Http::response(['detail' => 'Not voidable'], 412),
        );

        $result = authorizeHold($this->cart);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment amount does not match the order total.')
            ->and(holdVoidSent())->toBeTrue()
            ->and($this->binding->isIssued($this->cart, $transactionApiId))->toBeTrue();
    });

    it('keeps the refusal and the issued id when the connection to Fortis fails during the void', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds(
            [holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])],
            fn () => Http::failedConnection(),
        );

        $result = authorizeHold($this->cart);

        expect($result->message)->toBe('The payment amount does not match the order total.')
            ->and($this->binding->isIssued($this->cart, $transactionApiId))->toBeTrue();
    });

    it('keeps the issued id when the void response names another transaction', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds(
            [holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])],
            fn () => Http::response(['data' => ['id' => SECOND_HOLD_TRANSACTION_ID, 'status_code' => 201]]),
        );

        authorizeHold($this->cart);

        expect($this->binding->isIssued($this->cart, $transactionApiId))->toBeTrue();
    });

    it('keeps the refusal when Fortis answers the void with a malformed body', function (mixed $body) {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds(
            [holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])],
            fn () => Http::response($body),
        );

        $result = authorizeHold($this->cart);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment amount does not match the order total.')
            ->and($this->binding->isIssued($this->cart, $transactionApiId))->toBeTrue();
    })->with([
        'empty body' => [''],
        'scalar data' => [['data' => 'voided']],
    ]);

    it('keeps the refusal when the cache fails while checking the hold', function () {
        $binding = Mockery::mock(ElementsTransactionBinding::class);
        $binding->shouldReceive('isIssued')->andThrow(new RuntimeException('Cache unavailable'));
        $binding->shouldReceive('hasIssued')->andThrow(new RuntimeException('Cache unavailable'));
        app()->instance(ElementsTransactionBinding::class, $binding);
        Config::set('lunar-fortis.elements.require_transaction_binding', false);
        fakeHolds([holdRecord(['transaction_amount' => 900])]);

        $result = authorizeHold($this->cart);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment amount does not match the order total.')
            ->and(holdVoidSent())->toBeFalse();
    });

    it('keeps the issued id when Fortis does not report the hold as voided', function () {
        $transactionApiId = $this->binding->issue($this->cart);
        fakeHolds(
            [holdRecord(['transaction_amount' => 900, 'transaction_api_id' => $transactionApiId])],
            fn () => Http::response(['data' => ['id' => HOLD_TRANSACTION_ID, 'status_code' => 102]]),
        );

        $result = authorizeHold($this->cart);

        expect($result->message)->toBe('The payment amount does not match the order total.')
            ->and($this->binding->isIssued($this->cart, $transactionApiId))->toBeTrue();
    });
});
