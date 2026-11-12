<?php

use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;

uses(InteractsWithLunarCarts::class);

beforeEach(function () {
    $this->setUpLunar();
});

function paymentFormFor(Cart $cart): PaymentForm
{
    $form = new PaymentForm;
    $form->cart = $cart;

    return $form;
}

it('never shares a token key between two carts of the same user', function () {
    $first = $this->makeCart(1000);
    $second = $this->makeCart(1000);

    $first->update(['user_id' => 5]);
    $second->update(['user_id' => 5]);

    expect(paymentFormFor($first->refresh())->clientTokenCacheKey())
        ->not->toBe(paymentFormFor($second->refresh())->clientTokenCacheKey());
});

it('never shares a token key between a user and a guest cart with the same id', function () {
    $userCart = $this->makeCart(1000);
    $guestCart = $this->makeCart(1000);

    $userCart->update(['user_id' => $guestCart->id]);

    expect(paymentFormFor($userCart->refresh())->clientTokenCacheKey())
        ->not->toBe(paymentFormFor($guestCart)->clientTokenCacheKey());
});

it('changes the token key when the cart total changes', function () {
    $cart = $this->makeCart(1000);
    $before = paymentFormFor($cart)->clientTokenCacheKey();

    CartLine::where('cart_id', $cart->id)->update(['quantity' => 2]);

    expect(paymentFormFor(Cart::find($cart->id))->clientTokenCacheKey())->not->toBe($before);
});

it('changes the token key when the Fortis environment changes', function () {
    $cart = $this->makeCart(1000);

    Config::set('lunar-fortis.environment', 'sandbox');
    $sandbox = paymentFormFor($cart)->clientTokenCacheKey();

    Config::set('lunar-fortis.environment', 'production');

    expect(paymentFormFor($cart)->clientTokenCacheKey())->not->toBe($sandbox);
});

it('requests a new client token for a second cart of the same user', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.sandbox.fortis.tech/v1/elements/transaction/intention' => Http::sequence()
            ->push(['data' => ['client_token' => 'token-for-first-cart']])
            ->push(['data' => ['client_token' => 'token-for-second-cart']]),
    ]);

    $first = $this->makeCart(1000);
    $second = $this->makeCart(2500);

    $first->update(['user_id' => 5]);
    $second->update(['user_id' => 5]);

    expect(paymentFormFor($first->refresh())->clientToken())->toBe('token-for-first-cart')
        ->and(paymentFormFor($second->refresh())->clientToken())->toBe('token-for-second-cart');

    Http::assertSent(fn (Request $request) => $request['amount'] === 2500);
});
