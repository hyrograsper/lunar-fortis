<?php

use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

uses(InteractsWithLunarCarts::class);

const TERMINAL_FORTIS_API = 'api.sandbox.fortis.tech/v1/transactions';

const TERMINAL_TXN = 'term0000000000000000000001';

beforeEach(function () {
    $this->setUpLunar();

    Http::preventStrayRequests();

    $this->cart = $this->makeCart(1000);
});

function authorizeTerminalPayment(Cart $cart, string $transactionId = TERMINAL_TXN, string $policy = 'automatic'): PaymentAuthorize
{
    Config::set('lunar-fortis.terminal_policy', $policy);

    return app(FortisTerminalPaymentType::class)
        ->cart($cart)
        ->withData(['fortis_transaction_id' => $transactionId])
        ->authorize();
}

function terminalRecord(array $overrides = []): array
{
    return array_merge([
        'id' => TERMINAL_TXN,
        'status_code' => 102,
        'transaction_amount' => 1000,
        'terminal_id' => 'term0000000000000000000100',
        'account_type' => 'visa',
        'last_four' => '4242',
    ], $overrides);
}

function fakeTerminalLookup(array $record): void
{
    $id = $record['id'];

    Http::fake([
        TERMINAL_FORTIS_API."/{$id}/auth-complete" => Http::response([
            'data' => array_merge($record, ['status_code' => 101]),
        ]),
        TERMINAL_FORTIS_API."/{$id}*" => Http::response(['data' => $record]),
    ]);
}

function terminalOrderFor(Cart $cart): ?Order
{
    return Order::where('cart_id', $cart->id)->first();
}

function terminalSuccessfulTransactionsFor(Cart $cart): int
{
    $order = terminalOrderFor($cart);

    if (! $order) {
        return 0;
    }

    return Transaction::where('order_id', $order->id)->where('success', true)->count();
}

function recordTransactionOnAnotherOrder(string $driver, string $reference = TERMINAL_TXN): Order
{
    $otherOrder = test()->makeCart(1000)->createOrder();

    Transaction::create([
        'order_id' => $otherOrder->id,
        'success' => true,
        'type' => 'intent',
        'driver' => $driver,
        'amount' => 1000,
        'reference' => $reference,
        'status' => 'authorized',
        'card_type' => 'visa',
        'last_four' => '4242',
    ]);

    return $otherOrder;
}

describe('terminal amount verification', function () {
    it('rejects a genuine transaction for less than the cart total', function (string $policy, int $statusCode) {
        fakeTerminalLookup(terminalRecord(['transaction_amount' => 100, 'status_code' => $statusCode]));

        $result = authorizeTerminalPayment($this->cart, policy: $policy);

        expect($result->success)->toBeFalse()
            ->and(terminalOrderFor($this->cart)?->placed_at)->toBeNull()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);

        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/auth-complete'));
    })->with([
        'authorization, automatic' => ['automatic', 102],
        'authorization, manual' => ['manual', 102],
        'sale' => ['automatic', 101],
    ]);

    it('rejects a transaction for more than the cart total when it carries no tip', function () {
        fakeTerminalLookup(terminalRecord(['transaction_amount' => 1500]));

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        expect($result->success)->toBeFalse()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);
    });

    it('accepts a tipped payment whose amount is the cart total plus the tip', function () {
        fakeTerminalLookup(terminalRecord(['transaction_amount' => 1200, 'tip_amount' => 200]));

        $result = authorizeTerminalPayment($this->cart);

        $order = terminalOrderFor($this->cart);
        $intent = Transaction::where('order_id', $order->id)->where('type', 'intent')->sole();

        expect($result->success)->toBeTrue()
            ->and($order->placed_at)->not->toBeNull()
            ->and($intent->amount->value)->toBe(1200)
            ->and($intent->meta['tip_amount'])->toBe(200);
    });

    it('accepts a payment that carries a zero tip at the cart total', function () {
        fakeTerminalLookup(terminalRecord(['tip_amount' => 0]));

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        expect($result->success)->toBeTrue();
    });

    it('rejects a tipped payment whose amount does not equal the total plus the tip', function (int $amount) {
        fakeTerminalLookup(terminalRecord(['transaction_amount' => $amount, 'tip_amount' => 200]));

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        expect($result->success)->toBeFalse()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);
    })->with([
        'tip swallowed into a short payment' => [1100],
        'tip added twice' => [1400],
    ]);

    it('rejects a tip that is not a whole number', function (mixed $tip) {
        fakeTerminalLookup(terminalRecord(['transaction_amount' => 1200, 'tip_amount' => $tip]));

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        expect($result->success)->toBeFalse()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);
    })->with([
        'decimal string' => ['2.00'],
        'negative' => [-200],
        'array' => [[200]],
    ]);

    it('accepts a status code and amount returned as integer strings', function () {
        fakeTerminalLookup(terminalRecord(['status_code' => '102', 'transaction_amount' => '1000']));

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        $intent = Transaction::where('order_id', terminalOrderFor($this->cart)->id)->sole();

        expect($result->success)->toBeTrue()
            ->and($intent->amount->value)->toBe(1000)
            ->and($intent->status)->toBe('authorized');
    });

    it('rejects an amount that is not a whole number of minor units', function (mixed $amount) {
        fakeTerminalLookup(terminalRecord(['transaction_amount' => $amount]));

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        expect($result->success)->toBeFalse()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);
    })->with([
        'decimal string' => ['10.50'],
        'decimal string of the total' => ['1000.00'],
        'float' => [10.0],
        'negative' => [-1000],
        'missing' => [null],
    ]);

    it('rejects a transaction in a status that is neither a sale nor an authorization', function (mixed $statusCode) {
        fakeTerminalLookup(terminalRecord(['status_code' => $statusCode]));

        $result = authorizeTerminalPayment($this->cart);

        expect($result->success)->toBeFalse()
            ->and(terminalOrderFor($this->cart)?->placed_at)->toBeNull()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);
    })->with([
        'unknown' => [999],
        'non numeric' => ['approved'],
    ]);

    it('rejects a lookup that returns a different transaction than the one requested', function () {
        Http::fake([
            TERMINAL_FORTIS_API.'/'.TERMINAL_TXN.'*' => Http::response([
                'data' => terminalRecord(['id' => 'term0000000000000000000999']),
            ]),
        ]);

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        expect($result->success)->toBeFalse()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);
    });

    it('does not place an order when Fortis does not know the transaction', function () {
        Http::fake([
            TERMINAL_FORTIS_API.'/*' => Http::response(['detail' => 'Not found'], 404),
        ]);

        $result = authorizeTerminalPayment($this->cart);

        expect($result->success)->toBeFalse()
            ->and(terminalOrderFor($this->cart)?->placed_at)->toBeNull()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);
    });
});

describe('terminal replay protection', function () {
    it('rejects a transaction id already recorded against another order by either driver', function (string $driver) {
        $otherOrder = recordTransactionOnAnotherOrder($driver);
        fakeTerminalLookup(terminalRecord());

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        expect($result->success)->toBeFalse()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0)
            ->and(Transaction::where('reference', TERMINAL_TXN)->count())->toBe(1)
            ->and(Transaction::where('order_id', $otherOrder->id)->count())->toBe(1);

        Http::assertNothingSent();
    })->with([
        'terminal' => [FortisTerminalPaymentType::PAYMENT_TYPE],
        'elements' => [FortisPaymentType::PAYMENT_TYPE],
    ]);

    it('does not let a cheap order reuse the transaction that paid for an expensive one', function () {
        fakeTerminalLookup(terminalRecord(['transaction_amount' => 1000]));
        $paidOrderCart = $this->makeCart(1000);
        expect(authorizeTerminalPayment($paidOrderCart)->success)->toBeTrue();

        $result = authorizeTerminalPayment($this->makeCart(1000));

        expect($result->success)->toBeFalse()
            ->and(Transaction::where('reference', TERMINAL_TXN)->where('type', 'intent')->count())->toBe(1);
    });

    it('rejects a transaction id that is being processed by another request', function () {
        fakeTerminalLookup(terminalRecord());

        $lock = Cache::lock('lunar-fortis:terminal-transaction:'.TERMINAL_TXN, 30);
        $lock->get();

        $result = authorizeTerminalPayment($this->cart, policy: 'manual');

        $lock->release();

        expect($result->success)->toBeFalse()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);

        Http::assertNothingSent();
    });

    it('releases the lock so the same id can be retried afterwards', function () {
        fakeTerminalLookup(terminalRecord());

        authorizeTerminalPayment($this->cart, policy: 'manual');

        $lock = Cache::lock('lunar-fortis:terminal-transaction:'.TERMINAL_TXN, 30);

        expect($lock->get())->toBeTrue();

        $lock->release();
    });
});

describe('terminal retries for the same order', function () {
    it('does not duplicate transactions when a manual authorization is retried', function () {
        fakeTerminalLookup(terminalRecord());

        $first = authorizeTerminalPayment($this->cart, policy: 'manual');
        $second = authorizeTerminalPayment($this->cart, policy: 'manual');

        $order = terminalOrderFor($this->cart);

        expect($first->success)->toBeTrue()
            ->and($second->success)->toBeTrue()
            ->and($second->orderId)->toBe($order->id)
            ->and(Transaction::where('order_id', $order->id)->count())->toBe(1)
            ->and(Order::where('cart_id', $this->cart->id)->count())->toBe(1)
            ->and($order->status)->toBe('payment-authorized');
    });

    it('captures an authorization on retry when the first capture failed', function () {
        Http::fake([
            TERMINAL_FORTIS_API.'/'.TERMINAL_TXN.'/auth-complete' => Http::sequence()
                ->push(['detail' => 'Gateway down'], 500)
                ->push(['data' => terminalRecord(['status_code' => 101])]),
            TERMINAL_FORTIS_API.'/'.TERMINAL_TXN.'*' => Http::response(['data' => terminalRecord()]),
        ]);

        $first = authorizeTerminalPayment($this->cart);
        $second = authorizeTerminalPayment($this->cart);

        $order = terminalOrderFor($this->cart);

        expect($first->success)->toBeFalse()
            ->and($second->success)->toBeTrue()
            ->and($order->placed_at)->not->toBeNull()
            ->and(Transaction::where('order_id', $order->id)->where('type', 'intent')->count())->toBe(1)
            ->and(Transaction::where('order_id', $order->id)->where('type', 'capture')->where('success', true)->count())->toBe(1);
    });

    it('refuses to place an already placed order again without recording anything new', function () {
        fakeTerminalLookup(terminalRecord());

        authorizeTerminalPayment($this->cart);
        $order = terminalOrderFor($this->cart);
        $recorded = Transaction::where('order_id', $order->id)->count();

        $retry = authorizeTerminalPayment($this->cart);

        expect($retry->success)->toBeFalse()
            ->and(Transaction::where('order_id', $order->id)->count())->toBe($recorded)
            ->and($order->fresh()->placed_at)->not->toBeNull();
    });

    it('does not strand a retried sale that was recorded but not yet placed', function () {
        fakeTerminalLookup(terminalRecord(['status_code' => 101]));

        $order = $this->cart->createOrder();

        Transaction::create([
            'order_id' => $order->id,
            'success' => true,
            'type' => 'capture',
            'driver' => FortisTerminalPaymentType::PAYMENT_TYPE,
            'amount' => 1000,
            'reference' => TERMINAL_TXN,
            'status' => 'approved',
            'card_type' => 'visa',
            'last_four' => '4242',
        ]);

        $result = authorizeTerminalPayment($this->cart);

        expect($result->success)->toBeTrue()
            ->and($order->fresh()->placed_at)->not->toBeNull()
            ->and(Transaction::where('order_id', $order->id)->count())->toBe(1);
    });
});

describe('terminal genuine payments', function () {
    it('verifies and captures an authorization under the automatic policy', function () {
        fakeTerminalLookup(terminalRecord());

        $result = authorizeTerminalPayment($this->cart);

        $order = terminalOrderFor($this->cart);

        expect($result->success)->toBeTrue()
            ->and($order->placed_at)->not->toBeNull()
            ->and($order->status)->toBe('payment-received')
            ->and(Transaction::where('order_id', $order->id)->where('type', 'intent')->sole()->reference)->toBe(TERMINAL_TXN)
            ->and(Transaction::where('order_id', $order->id)->where('type', 'capture')->where('success', true)->count())->toBe(1);
    });

    it('places the order for a verified sale without capturing again', function () {
        fakeTerminalLookup(terminalRecord(['status_code' => 101, '@action' => 'sale']));

        $result = authorizeTerminalPayment($this->cart);

        $order = terminalOrderFor($this->cart);

        expect($result->success)->toBeTrue()
            ->and($order->placed_at)->not->toBeNull()
            ->and(Transaction::where('order_id', $order->id)->sole()->type)->toBe('capture');

        Http::assertNotSent(fn (Request $request) => str_ends_with($request->url(), '/auth-complete'));
    });

    it('records a declined transaction without placing the order', function () {
        fakeTerminalLookup(terminalRecord(['status_code' => 301]));

        $result = authorizeTerminalPayment($this->cart);

        expect($result->success)->toBeFalse()
            ->and(terminalOrderFor($this->cart)->placed_at)->toBeNull()
            ->and(terminalSuccessfulTransactionsFor($this->cart))->toBe(0);
    });

    it('still rejects a malformed transaction id without calling Fortis', function () {
        Http::fake();

        $result = authorizeTerminalPayment($this->cart, '../terminals');

        expect($result->success)->toBeFalse();

        Http::assertNothingSent();
    });
});
