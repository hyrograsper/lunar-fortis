<?php

use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\Services\ElementsTransactionBinding;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Hyrograsper\LunarFortis\Tests\Live\LiveFortis;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

uses(InteractsWithLunarCarts::class)->group('live');

beforeEach(function () {
    if (! LiveFortis::enabled()) {
        $this->markTestSkipped(LiveFortis::skipReason());
    }

    $this->setUpLunar();

    LiveFortis::configure();
});

function liveOrderFor(Cart $cart): ?Order
{
    return Order::where('cart_id', $cart->id)->first();
}

function liveCartTotal(Cart $cart): int
{
    return $cart->calculate()->total->value;
}

function liveRowsFor(string $reference): int
{
    return Transaction::where('reference', $reference)->count();
}

describe('genuine payments', function () {
    it('1: authorizes a genuine auth-only for the cart total under the manual policy', function () {
        $cart = $this->makeCart(1310);
        $fortis = LiveFortis::keyedAuthOnly(liveCartTotal($cart));

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 102, 'reason_code_id' => 1000]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']], 'manual');

        $order = liveOrderFor($cart);
        $row = Transaction::where('reference', $fortis['id'])->sole();

        expect($result->success)->toBeTrue()
            ->and($result->message)->toBe('Payment Authorized')
            ->and($order->placed_at)->toBeNull()
            ->and($order->status)->toBe('payment-authorized')
            ->and($row->type)->toBe('intent')
            ->and((bool) $row->success)->toBeTrue()
            ->and($row->amount->value)->toBe(liveCartTotal($cart))
            ->and(LiveFortis::get($fortis['id']))->toMatchArray(['status_code' => 102]);
    });

    it('2: captures a genuine auth-only with a live auth-complete under the automatic policy', function () {
        $cart = $this->makeCart(1320);
        $fortis = LiveFortis::keyedAuthOnly(liveCartTotal($cart));

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 102]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']], 'automatic');

        $intent = Transaction::where('reference', $fortis['id'])->whereNull('parent_transaction_id')->sole();
        $capture = Transaction::where('parent_transaction_id', $intent->id)->sole();

        expect($result->success)->toBeTrue()
            ->and($result->message)->toBe('Payment Captured')
            ->and(liveOrderFor($cart)->placed_at)->not->toBeNull()
            ->and($intent->type)->toBe('intent')
            ->and($capture->type)->toBe('capture')
            ->and((bool) $capture->success)->toBeTrue()
            ->and($capture->amount->value)->toBe(liveCartTotal($cart))
            ->and(LiveFortis::get($fortis['id']))->toMatchArray([
                'status_code' => 101,
                'reason_code_id' => 1001,
                'transaction_amount' => liveCartTotal($cart),
            ]);
    });

    it('3: accepts a genuine sale as a capture without calling auth-complete', function () {
        $cart = $this->makeCart(1330);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 101, 'reason_code_id' => 1000]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']], 'automatic');

        $row = Transaction::where('reference', $fortis['id'])->sole();

        expect($result->success)->toBeTrue()
            ->and($result->message)->toBe('Payment Captured')
            ->and(liveOrderFor($cart)->placed_at)->not->toBeNull()
            ->and($row->type)->toBe('capture')
            ->and(Transaction::where('parent_transaction_id', $row->id)->count())->toBe(0)
            ->and(LiveFortis::get($fortis['id']))->toMatchArray(['status_code' => 101, 'reason_code_id' => 1000]);
    });
});

describe('attacks', function () {
    it('4: rejects a forged browser payload sent to PaymentForm::handlePaymentResponse', function () {
        $cart = $this->makeCart(1340);
        $forgedId = '31f1df09'.bin2hex(random_bytes(8));

        Livewire::test(PaymentForm::class, ['cart' => $cart])
            ->call('handlePaymentResponse', ['data' => [
                'id' => $forgedId,
                '@action' => 'sale',
                'status_code' => 101,
                'reason_code_id' => 1000,
                'transaction_amount' => liveCartTotal($cart),
                'account_type' => 'visa',
                'last_four' => '1111',
            ]])
            ->assertDispatched('payment-error', 'Unable to verify the payment.');

        expect(liveOrderFor($cart)?->placed_at)->toBeNull()
            ->and(liveRowsFor($forgedId))->toBe(0)
            ->and(Transaction::count())->toBe(0);
    });

    it('5: rejects a real approved sale for less than the cart total', function () {
        $cart = $this->makeCart(1350);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart) - 100);

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 101]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment amount does not match the order total.')
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull()
            ->and(liveRowsFor($fortis['id']))->toBe(0);
    });

    it('6: rejects a real approved auth-only for more than the cart total', function () {
        $cart = $this->makeCart(1360);
        $fortis = LiveFortis::keyedAuthOnly(liveCartTotal($cart) + 100);

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 102]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment amount does not match the order total.')
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull()
            ->and(liveRowsFor($fortis['id']))->toBe(0)
            ->and(LiveFortis::get($fortis['id']))->toMatchArray(['status_code' => 102]);
    });

    it('7: rejects an id already used for cart A when presented for cart B with the same total', function () {
        $cartA = $this->makeCart(1370);
        $cartB = $this->makeCart(1370);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cartA));

        expect(liveCartTotal($cartB))->toBe(liveCartTotal($cartA))
            ->and($fortis)->toMatchArray(['http' => 201, 'status_code' => 101]);

        $first = LiveFortis::authorizeElements($cartA, ['id' => $fortis['id']]);
        $replay = LiveFortis::authorizeElements($cartB, ['id' => $fortis['id']]);

        expect($first->success)->toBeTrue()
            ->and($replay->success)->toBeFalse()
            ->and($replay->message)->toBe('This payment has already been used.')
            ->and(liveOrderFor($cartB)?->placed_at)->toBeNull()
            ->and(liveRowsFor($fortis['id']))->toBe(1);
    });

    it('8a: rejects the same captured sale presented twice for the same cart', function () {
        $cart = $this->makeCart(1380);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));

        $first = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);
        $second = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($first->success)->toBeTrue()
            ->and($second->success)->toBeFalse()
            ->and($second->message)->toBe('This order already has an authorized payment.')
            ->and(liveRowsFor($fortis['id']))->toBe(1)
            ->and(Order::where('cart_id', $cart->id)->count())->toBe(1);
    });

    it('8b: rejects the same manual-policy auth-only presented twice for the same cart', function () {
        $cart = $this->makeCart(1385);
        $fortis = LiveFortis::keyedAuthOnly(liveCartTotal($cart));

        $first = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']], 'manual');
        $second = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']], 'manual');

        expect($first->success)->toBeTrue()
            ->and($second->success)->toBeFalse()
            ->and($second->message)->toBe('This order already has an authorized payment.')
            ->and(liveRowsFor($fortis['id']))->toBe(1)
            ->and(Transaction::count())->toBe(1);
    });

    it('9: records an AVS-declined sale as a failed payment and places no order', function () {
        $cart = $this->makeCart(1390);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart), zip: LiveFortis::AVS_DECLINE_ZIP);

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 301, 'reason_code_id' => 1655]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        $row = Transaction::where('reference', $fortis['id'])->sole();

        expect($result->success)->toBeFalse()
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull()
            ->and((bool) $row->success)->toBeFalse()
            ->and($row->status)->toBe('declined');
    });
});

describe('10: transaction binding enabled', function () {
    beforeEach(function () {
        Config::set('lunar-fortis.elements.require_transaction_binding', true);
    });

    it('rejects a transaction whose api id was issued for another cart', function () {
        $cart = $this->makeCart(1410);
        $otherCart = $this->makeCart(1410);
        $foreignApiId = app(ElementsTransactionBinding::class)->issue($otherCart);
        app(ElementsTransactionBinding::class)->issue($cart);

        $fortis = LiveFortis::keyedSale(liveCartTotal($cart), $foreignApiId);

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 101, 'transaction_api_id' => $foreignApiId]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment could not be verified.')
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull()
            ->and(liveRowsFor($fortis['id']))->toBe(0);
    });

    it('rejects a transaction with no api id', function () {
        $cart = $this->makeCart(1420);
        app(ElementsTransactionBinding::class)->issue($cart);

        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 101, 'transaction_api_id' => null]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment could not be verified.')
            ->and(liveRowsFor($fortis['id']))->toBe(0);
    });

    it('rejects a transaction whose api id was never issued', function () {
        $cart = $this->makeCart(1430);
        app(ElementsTransactionBinding::class)->issue($cart);
        $unissuedApiId = 'lf-'.bin2hex(random_bytes(16));

        $fortis = LiveFortis::keyedSale(liveCartTotal($cart), $unissuedApiId);

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 101, 'transaction_api_id' => $unissuedApiId]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment could not be verified.')
            ->and(liveRowsFor($fortis['id']))->toBe(0);
    });

    it('accepts a transaction carrying the api id PaymentForm issued for this cart', function () {
        $cart = $this->makeCart(1440);
        $component = Livewire::test(PaymentForm::class, ['cart' => $cart]);
        $issuedApiId = $component->instance()->issuedClientToken()['transaction_api_id'];

        $fortis = LiveFortis::keyedAuthOnly(liveCartTotal($cart), $issuedApiId);

        expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 102, 'transaction_api_id' => $issuedApiId]);

        $component->call('handlePaymentResponse', ['data' => ['id' => $fortis['id']]])
            ->assertNotDispatched('payment-error');

        expect(liveOrderFor($cart)->placed_at)->not->toBeNull()
            ->and(Transaction::where('reference', $fortis['id'])->where('type', 'capture')->where('success', true)->count())->toBe(1)
            ->and(app(ElementsTransactionBinding::class)->isIssued($cart->refresh(), $issuedApiId))->toBeFalse()
            ->and(LiveFortis::get($fortis['id']))->toMatchArray(['status_code' => 101, 'reason_code_id' => 1001]);
    });

    it('accepts a transaction carrying an api id from ElementsTransactionBinding::issue', function () {
        $cart = $this->makeCart(1450);
        $issuedApiId = app(ElementsTransactionBinding::class)->issue($cart);

        $fortis = LiveFortis::keyedSale(liveCartTotal($cart), $issuedApiId);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeTrue()
            ->and($result->message)->toBe('Payment Captured')
            ->and(liveOrderFor($cart)->placed_at)->not->toBeNull();
    });
});

describe('12: other card products', function () {
    it('treats a keyed sale on the terminal product like any other location transaction', function () {
        $terminalProduct = getenv('FORTIS_TERMINAL_PRODUCT_TRANSACTION_ID');

        if (! is_string($terminalProduct) || $terminalProduct === '') {
            $this->markTestSkipped('No terminal product transaction id configured.');
        }

        $cart = $this->makeCart(1460);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart), productTransactionId: $terminalProduct);

        if (($fortis['http'] ?? null) !== 201) {
            $this->markTestSkipped("The sandbox refused a keyed sale on the terminal product (HTTP {$fortis['http']}).");
        }

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($fortis['status_code'])->toBe(101)
            ->and($result->success)->toBeTrue();
    });
});

describe('13: refunds through the driver', function () {
    it('sends two partial refunds of the same amount when refund() is called twice', function () {
        $cart = $this->makeCart(2400);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));

        expect(LiveFortis::authorizeElements($cart, ['id' => $fortis['id']])->success)->toBeTrue();

        $capture = Transaction::where('reference', $fortis['id'])->sole();
        $driver = app(FortisPaymentType::class);

        $first = $driver->refund($capture, 700);
        $second = $driver->refund($capture, 700);

        $parent = LiveFortis::get($fortis['id'], 'transaction_references');

        expect($first->success)->toBeTrue()
            ->and($second->success)->toBeTrue()
            ->and(Transaction::where('parent_transaction_id', $capture->id)->where('type', 'refund')->where('success', true)->count())->toBe(2)
            ->and(collect($parent['transaction_references'])->where('reference_type', 'refund')->pluck('transaction_amount')->all())->toBe([700, 700]);
    });

    it('fails a second full refund cleanly without leaking an exception', function () {
        $cart = $this->makeCart(1600);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));

        expect(LiveFortis::authorizeElements($cart, ['id' => $fortis['id']])->success)->toBeTrue();

        $capture = Transaction::where('reference', $fortis['id'])->sole();
        $driver = app(FortisPaymentType::class);

        $first = $driver->refund($capture, liveCartTotal($cart));
        $second = $driver->refund($capture, liveCartTotal($cart));

        expect($first->success)->toBeTrue()
            ->and($second->success)->toBeFalse()
            ->and($second->message)->toContain('422')
            ->and(LiveFortis::get($fortis['id'], 'is_refundable')['is_refundable'])->toBeFalse()
            ->and(Transaction::where('parent_transaction_id', $capture->id)->where('type', 'refund')->count())->toBe(1);
    });
});

describe('14: spent transactions', function () {
    it('rejects a fully refunded sale presented for authorize', function () {
        $cart = $this->makeCart(1470);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));
        $refund = LiveFortis::refund($fortis['id'], liveCartTotal($cart));

        expect($refund)->toMatchArray(['http' => 200, 'status_code' => 111]);

        $parent = LiveFortis::get($fortis['id'], 'transaction_references,is_refundable');

        expect($parent)->toMatchArray([
            'status_code' => 101,
            'transaction_amount' => liveCartTotal($cart),
            'is_refundable' => false,
        ])
            ->and(collect($parent['transaction_references'])->sum('transaction_amount'))->toBe(liveCartTotal($cart));

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull();
    });

    it('rejects a partially refunded sale presented for authorize', function () {
        $cart = $this->makeCart(1475);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));
        $refund = LiveFortis::refund($fortis['id'], 700);

        expect($refund)->toMatchArray(['http' => 200, 'status_code' => 111]);

        $parent = LiveFortis::get($fortis['id'], 'transaction_references');

        expect($parent)->toMatchArray(['status_code' => 101, 'transaction_amount' => liveCartTotal($cart)])
            ->and(collect($parent['transaction_references'])->sum('transaction_amount'))->toBe(700);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull();
    });

    it('rejects the refund child of a sale presented for authorize', function () {
        $cart = $this->makeCart(1480);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));
        $refund = LiveFortis::refund($fortis['id'], liveCartTotal($cart));

        expect($refund)->toMatchArray(['http' => 200, 'status_code' => 111, 'transaction_amount' => liveCartTotal($cart)]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $refund['id']]);

        expect($result->success)->toBeFalse()
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull()
            ->and(Transaction::where('success', true)->count())->toBe(0);
    });

    it('rejects a voided sale presented for authorize', function () {
        $cart = $this->makeCart(1490);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));
        $void = LiveFortis::void($fortis['id']);

        expect($void['http'])->toBe(200)
            ->and(LiveFortis::get($fortis['id']))->toMatchArray(['status_code' => 201]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull()
            ->and(Transaction::where('success', true)->count())->toBe(0);
    });

    it('rejects a voided auth-only presented for authorize', function () {
        $cart = $this->makeCart(1495);
        $fortis = LiveFortis::keyedAuthOnly(liveCartTotal($cart));
        $void = LiveFortis::void($fortis['id']);

        expect($void['http'])->toBe(200)
            ->and(LiveFortis::get($fortis['id']))->toMatchArray(['status_code' => 201]);

        $result = LiveFortis::authorizeElements($cart, ['id' => $fortis['id']], 'manual');

        expect($result->success)->toBeFalse()
            ->and(liveOrderFor($cart)?->placed_at)->toBeNull()
            ->and(Transaction::where('success', true)->count())->toBe(0);
    });
});

describe('cross-driver', function () {
    it('refuses a keyed card-not-present sale on the terminal driver', function () {
        $cart = $this->makeCart(1510);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cart));

        $result = LiveFortis::authorizeTerminal($cart, $fortis['id']);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment was not taken on a terminal.')
            ->and(liveRowsFor($fortis['id']))->toBe(0);
    });

    it('rejects an Elements-recorded id on the terminal driver for another cart', function () {
        $cartA = $this->makeCart(1520);
        $cartB = $this->makeCart(1520);
        $fortis = LiveFortis::keyedSale(liveCartTotal($cartA));

        expect(LiveFortis::authorizeElements($cartA, ['id' => $fortis['id']])->success)->toBeTrue();

        $result = LiveFortis::authorizeTerminal($cartB, $fortis['id']);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('This payment has already been used.')
            ->and(liveOrderFor($cartB)?->placed_at)->toBeNull();
    });

    it('rejects a terminal-recorded id on the Elements driver for another cart', function () {
        $cartA = $this->makeCart(1530);
        $cartB = $this->makeCart(1530);
        $fortis = LiveFortis::terminalAuthOnly(liveCartTotal($cartA));

        expect(LiveFortis::authorizeTerminal($cartA, $fortis['id'])->success)->toBeTrue();

        $result = LiveFortis::authorizeElements($cartB, ['id' => $fortis['id']]);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('This payment has already been used.');
    });
});

describe('15/16: a second genuine payment for an order that already has one', function () {
    it('15: refuses a second genuine auth-only on the Elements driver once the order is authorized', function () {
        $cart = $this->makeCart(1810);
        $first = LiveFortis::keyedAuthOnly(liveCartTotal($cart));
        $second = LiveFortis::keyedAuthOnly(liveCartTotal($cart));

        expect($second)->toMatchArray(['http' => 201, 'status_code' => 102]);

        $accepted = LiveFortis::authorizeElements($cart, ['id' => $first['id']], 'manual');
        $refused = LiveFortis::authorizeElements($cart, ['id' => $second['id']], 'manual');

        expect($accepted->success)->toBeTrue()
            ->and($refused->success)->toBeFalse()
            ->and($refused->message)->toBe('This order already has an authorized payment.')
            ->and(liveOrderFor($cart)->status)->toBe('payment-authorized')
            ->and(liveOrderFor($cart)->placed_at)->toBeNull()
            ->and(liveRowsFor($second['id']))->toBe(0);
    });

    it('15b: refuses a second genuine sale on the Elements driver once the order is placed', function () {
        $cart = $this->makeCart(1815);
        $first = LiveFortis::keyedSale(liveCartTotal($cart));
        $second = LiveFortis::keyedSale(liveCartTotal($cart));

        $accepted = LiveFortis::authorizeElements($cart, ['id' => $first['id']]);
        $refused = LiveFortis::authorizeElements($cart, ['id' => $second['id']]);

        expect($accepted->success)->toBeTrue()
            ->and($refused->success)->toBeFalse()
            ->and($refused->message)->toBe('This order already has an authorized payment.')
            ->and(liveRowsFor($second['id']))->toBe(0);
    });

    it('16a: refuses a second genuine transaction on the terminal driver once the order is authorized', function () {
        $cart = $this->makeCart(1820);
        $first = LiveFortis::terminalAuthOnly(liveCartTotal($cart));
        $second = LiveFortis::terminalAuthOnly(liveCartTotal($cart));

        expect($second['status_code'])->toBe(102);

        $accepted = LiveFortis::authorizeTerminal($cart, $first['id'], 'manual');

        expect($accepted->success)->toBeTrue()
            ->and(liveOrderFor($cart)->status)->toBe('payment-authorized');

        $refused = LiveFortis::authorizeTerminal($cart, $second['id'], 'manual');

        expect($refused->success)->toBeFalse()
            ->and(liveOrderFor($cart)->status)->toBe('payment-authorized')
            ->and(liveRowsFor($second['id']))->toBe(0);
    });

    it('16b: refuses a terminal transaction for an order the Elements driver already authorized', function () {
        $cart = $this->makeCart(1830);
        $first = LiveFortis::keyedAuthOnly(liveCartTotal($cart));
        $second = LiveFortis::terminalAuthOnly(liveCartTotal($cart));

        expect(LiveFortis::authorizeElements($cart, ['id' => $first['id']], 'manual')->success)->toBeTrue();

        $refused = LiveFortis::authorizeTerminal($cart, $second['id'], 'manual');

        expect($refused->success)->toBeFalse()
            ->and(liveOrderFor($cart)->status)->toBe('payment-authorized')
            ->and(liveRowsFor($second['id']))->toBe(0);
    });
});
