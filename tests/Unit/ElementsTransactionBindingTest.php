<?php

use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\Services\ElementsTransactionBinding;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Cart;
use Lunar\Models\Order;

uses(InteractsWithLunarCarts::class);

const BINDING_TRANSACTIONS_API = 'api.sandbox.fortis.tech/v1/transactions';

const BINDING_INTENTION_API = 'api.sandbox.fortis.tech/v1/elements/transaction/intention';

const BINDING_TRANSACTION_ID = 'txnbinding0000000000000001';

beforeEach(function () {
    $this->setUpLunar();

    Http::preventStrayRequests();

    Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    Config::set('lunar-fortis.policy', 'manual');

    $this->cart = $this->makeCart(1000);
});

function fakeClientTokens(): void
{
    Http::fake([
        BINDING_INTENTION_API => Http::sequence()
            ->push(['data' => ['client_token' => 'first-client-token']])
            ->push(['data' => ['client_token' => 'second-client-token']])
            ->push(['data' => ['client_token' => 'third-client-token']]),
    ]);
}

function fakeBoundFortisRecord(array $overrides = []): void
{
    Http::fake([
        BINDING_TRANSACTIONS_API.'/'.BINDING_TRANSACTION_ID.'*' => Http::response(['data' => array_merge([
            'id' => BINDING_TRANSACTION_ID,
            'status_code' => 102,
            'transaction_amount' => 1000,
            'account_type' => 'visa',
            'last_four' => '4242',
        ], $overrides)]),
    ]);
}

function authorizeBoundPayment(Cart $cart): PaymentAuthorize
{
    return app(FortisPaymentType::class)
        ->cart($cart)
        ->withData(['id' => BINDING_TRANSACTION_ID])
        ->authorize();
}

function issuedTransactionApiIdFor(Cart $cart): string
{
    return Livewire::test(PaymentForm::class, ['cart' => $cart])
        ->instance()->issuedClientToken()['transaction_api_id'];
}

function authorizedOrderFor(Cart $cart): ?Order
{
    return Order::where('cart_id', $cart->id)
        ->where('status', 'payment-authorized')
        ->first();
}

describe('with the binding turned off', function () {
    it('accepts a record whatever its transaction api id', function (?string $transactionApiId) {
        Config::set('lunar-fortis.elements.require_transaction_binding', false);
        fakeBoundFortisRecord(['transaction_api_id' => $transactionApiId]);

        $result = authorizeBoundPayment($this->cart);

        expect($result->success)->toBeTrue()
            ->and(authorizedOrderFor($this->cart))->not->toBeNull();
    })->with([
        'no id' => [null],
        'an id never issued' => ['lf-0123456789abcdef0123456789abcdef'],
    ]);

    it('accepts a record carrying an id issued for another cart', function () {
        Config::set('lunar-fortis.elements.require_transaction_binding', false);
        fakeClientTokens();

        $otherCartsTransactionApiId = issuedTransactionApiIdFor($this->makeCart(1000));

        fakeBoundFortisRecord(['transaction_api_id' => $otherCartsTransactionApiId]);

        $result = authorizeBoundPayment($this->cart);

        expect($result->success)->toBeTrue()
            ->and(authorizedOrderFor($this->cart))->not->toBeNull();
    });
});

describe('with the binding turned on', function () {
    beforeEach(function () {
        Config::set('lunar-fortis.elements.require_transaction_binding', true);
    });

    it('accepts a record carrying the id issued with the client token for this cart', function () {
        fakeClientTokens();

        $transactionApiId = issuedTransactionApiIdFor($this->cart);

        fakeBoundFortisRecord(['transaction_api_id' => $transactionApiId]);

        $result = authorizeBoundPayment($this->cart);

        expect($result->success)->toBeTrue()
            ->and(authorizedOrderFor($this->cart))->not->toBeNull();
    });

    it('rejects a record whose transaction api id was not issued for this cart', function (mixed $transactionApiId) {
        fakeClientTokens();
        issuedTransactionApiIdFor($this->cart);

        fakeBoundFortisRecord(['transaction_api_id' => $transactionApiId]);

        $result = authorizeBoundPayment($this->cart);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment could not be verified.')
            ->and(authorizedOrderFor($this->cart))->toBeNull()
            ->and(Order::where('cart_id', $this->cart->id)->whereNotNull('placed_at')->exists())->toBeFalse();
    })->with([
        'missing' => [null],
        'empty' => [''],
        'not a string' => [12345],
        'unknown' => ['lf-ffffffffffffffffffffffffffffffff'],
    ]);

    it('rejects a record carrying an id issued for another cart', function () {
        $otherCart = $this->makeCart(1000);
        fakeClientTokens();

        $otherCartsTransactionApiId = issuedTransactionApiIdFor($otherCart);

        fakeBoundFortisRecord(['transaction_api_id' => $otherCartsTransactionApiId]);

        $result = authorizeBoundPayment($this->cart);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('The payment could not be verified.')
            ->and(authorizedOrderFor($this->cart))->toBeNull();
    });

    it('rejects a declined record from another cart without recording it', function () {
        $otherCart = $this->makeCart(1000);
        fakeClientTokens();

        $otherCartsTransactionApiId = issuedTransactionApiIdFor($otherCart);

        fakeBoundFortisRecord([
            'status_code' => 301,
            'reason_code_id' => 1655,
            'transaction_api_id' => $otherCartsTransactionApiId,
        ]);

        $result = authorizeBoundPayment($this->cart);

        expect($result->message)->toBe('The payment could not be verified.')
            ->and(Order::where('cart_id', $this->cart->id)->first()?->transactions()->count() ?? 0)->toBe(0);
    });

    it('forgets the id once the payment it bound has been authorized', function () {
        fakeClientTokens();

        $transactionApiId = issuedTransactionApiIdFor($this->cart);

        fakeBoundFortisRecord(['transaction_api_id' => $transactionApiId]);

        authorizeBoundPayment($this->cart);

        expect(app(ElementsTransactionBinding::class)->isIssued($this->cart, $transactionApiId))->toBeFalse();
    });
});

describe('issuing transaction api ids', function () {
    it('issues a prefixed id with 128 bits of randomness that fits Fortis', function () {
        fakeClientTokens();

        $transactionApiId = issuedTransactionApiIdFor($this->cart);

        expect($transactionApiId)->toMatch('/^lf-[0-9a-f]{32}$/')
            ->and(strlen($transactionApiId))->toBeLessThanOrEqual(36);
    });

    it('returns the same token and id while the client token is cached', function () {
        fakeClientTokens();

        $first = Livewire::test(PaymentForm::class, ['cart' => $this->cart])->instance()->issuedClientToken();
        $second = Livewire::test(PaymentForm::class, ['cart' => $this->cart])->instance()->issuedClientToken();

        expect($second)->toBe($first)
            ->and($first['token'])->toBe('first-client-token');
    });

    it('gives a regenerated token a new id and keeps both ids valid for the cart', function () {
        fakeClientTokens();
        $binding = app(ElementsTransactionBinding::class);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);
        $firstTransactionApiId = $component->instance()->issuedClientToken()['transaction_api_id'];

        $component->call('regenerateClientToken');

        $secondTransactionApiId = $component->instance()->issuedClientToken()['transaction_api_id'];

        $component->assertDispatched('token-regenerated', 'second-client-token', $secondTransactionApiId);

        expect($secondTransactionApiId)->not->toBe($firstTransactionApiId)
            ->and($binding->isIssued($this->cart, $firstTransactionApiId))->toBeTrue()
            ->and($binding->isIssued($this->cart, $secondTransactionApiId))->toBeTrue();
    });

    it('keeps only the fifty most recent ids for a cart', function () {
        $binding = app(ElementsTransactionBinding::class);

        $issued = collect(range(1, 51))->map(fn () => $binding->issue($this->cart));

        expect($binding->isIssued($this->cart, $issued->first()))->toBeFalse()
            ->and($issued->skip(1)->every(fn (string $id) => $binding->isIssued($this->cart, $id)))->toBeTrue();
    });

    it('reuses the token and id when the form re-renders within ten minutes', function () {
        fakeClientTokens();

        $first = Livewire::test(PaymentForm::class, ['cart' => $this->cart])->instance()->issuedClientToken();

        $this->travel(9)->minutes();

        $second = Livewire::test(PaymentForm::class, ['cart' => $this->cart])->instance()->issuedClientToken();

        expect($second)->toBe($first);
        Http::assertSentCount(1);
    });

    it('issues a new token and id once the cached token is ten minutes old', function () {
        fakeClientTokens();

        $first = Livewire::test(PaymentForm::class, ['cart' => $this->cart])->instance()->issuedClientToken();

        $this->travel(11)->minutes();

        $second = Livewire::test(PaymentForm::class, ['cart' => $this->cart])->instance()->issuedClientToken();

        expect($second['token'])->toBe('second-client-token')
            ->and($second['transaction_api_id'])->not->toBe($first['transaction_api_id']);
    });

    it('issues a new token and id after a declined attempt, even when the page is reloaded', function () {
        fakeClientTokens();
        fakeBoundFortisRecord(['status_code' => 301]);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);
        $usedUp = $component->instance()->issuedClientToken();

        $component->instance()->handlePaymentResponse(['data' => ['id' => BINDING_TRANSACTION_ID]]);

        $afterReload = Livewire::test(PaymentForm::class, ['cart' => $this->cart])->instance()->issuedClientToken();

        expect($afterReload['token'])->toBe('second-client-token')
            ->and($afterReload['transaction_api_id'])->not->toBe($usedUp['transaction_api_id']);
    });

    it('waits for another request issuing an id for the same cart', function () {
        $binding = new ElementsTransactionBinding(lockWaitSeconds: 2);
        $heldByAnotherRequest = Cache::lock($binding->cacheKey($this->cart).':lock', 10);
        $heldByAnotherRequest->get();

        $startedAt = microtime(true);
        $transactionApiId = $binding->issue($this->cart);
        $waited = microtime(true) - $startedAt;

        $heldByAnotherRequest->release();

        expect($waited)->toBeGreaterThanOrEqual(1.0)
            ->and($binding->isIssued($this->cart, $transactionApiId))->toBeTrue();
    });

    it('does not leave the cart lock held after issuing or releasing an id', function () {
        $binding = app(ElementsTransactionBinding::class);

        $transactionApiId = $binding->issue($this->cart);
        $binding->release($this->cart, $transactionApiId);

        $lock = Cache::lock($binding->cacheKey($this->cart).':lock', 10);

        expect($lock->get())->toBeTrue();

        $lock->release();
    });

    it('does not issue an id for a zero dollar cart', function () {
        Http::fake();
        $freeCart = $this->makeCart(0);

        Livewire::test(PaymentForm::class, ['cart' => $freeCart])
            ->assertSeeHtml('transactionApiId: null')
            ->assertDontSeeHtml("transactionApiId: 'lf-");

        Http::assertNothingSent();
    });
});

describe('the payment form view', function () {
    it('renders the hidden transaction api id field with the current id', function (bool $requireBinding) {
        Config::set('lunar-fortis.elements.require_transaction_binding', $requireBinding);
        fakeClientTokens();

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);
        $transactionApiId = $component->instance()->issuedClientToken()['transaction_api_id'];

        $component->assertSeeHtml("transactionApiId: '{$transactionApiId}'")
            ->assertSeeHtml("additional: [{name: 'transaction_api_id', value: this.transactionApiId, hidden: true}]");
    })->with([
        'binding on' => [true],
        'binding off' => [false],
    ]);
});
