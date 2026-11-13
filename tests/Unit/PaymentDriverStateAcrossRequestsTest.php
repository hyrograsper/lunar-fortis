<?php

use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Lunar\Facades\Payments;
use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Models\Order;

uses(InteractsWithLunarCarts::class);

function cartThatCreatesOrder(int $orderId): CartContract
{
    $order = Mockery::mock(Order::class)->makePartial();
    $order->id = $orderId;
    $order->placed_at = null;

    $cart = Mockery::mock(CartContract::class);
    $cart->draftOrder = null;
    $cart->completedOrder = null;
    $cart->shouldReceive('createOrder')->andReturn($order);

    return $cart;
}

function requestedTransactionIds(): array
{
    return Http::recorded()
        ->map(fn (array $pair): string => $pair[0] instanceof Request ? $pair[0]->url() : '')
        ->filter(fn (string $url): bool => str_contains($url, '/transactions/'))
        ->map(fn (string $url): string => basename(parse_url($url, PHP_URL_PATH)))
        ->values()
        ->all();
}

beforeEach(function () {
    $this->setUpLunar();

    Http::fake(['*' => Http::response(['data' => []], 200)]);
    config()->set('lunar.payments.types.fortis-terminal', ['driver' => 'fortis-terminal']);
});

it('resolves the same driver instance for consecutive requests on one container', function () {
    $first = Payments::driver('fortis-terminal');
    $second = Payments::driver('fortis-terminal');

    expect($first)->toBeInstanceOf(FortisTerminalPaymentType::class)
        ->and($second)->toBe($first);
});

it('does not read the previous buyers transaction id when the next request omits data', function () {
    Payments::driver('fortis-terminal')
        ->cart(cartThatCreatesOrder(1))
        ->withData(['fortis_transaction_id' => 'buyer-a-txn'])
        ->authorize();

    Payments::driver('fortis-terminal')
        ->cart(cartThatCreatesOrder(2))
        ->authorize();

    expect(requestedTransactionIds())->toBe(['buyer-a-txn']);
});

it('does not read the previous buyers transaction id once drivers are flushed between requests', function () {
    Payments::driver('fortis-terminal')
        ->cart(cartThatCreatesOrder(1))
        ->withData(['fortis_transaction_id' => 'buyer-a-txn'])
        ->authorize();

    Payments::forgetDrivers();

    Payments::driver('fortis-terminal')
        ->cart(cartThatCreatesOrder(2))
        ->authorize();

    expect(requestedTransactionIds())->toBe(['buyer-a-txn']);
});

it('does not reuse the previous buyers cart when the next request omits the cart', function () {
    Payments::driver('fortis-terminal')
        ->cart(cartThatCreatesOrder(1))
        ->withData(['fortis_transaction_id' => 'buyer-a-txn'])
        ->authorize();

    $result = Payments::driver('fortis-terminal')
        ->withData(['fortis_transaction_id' => 'buyer-b-txn'])
        ->authorize();

    expect($result->success)->toBeFalse()
        ->and(requestedTransactionIds())->toBe(['buyer-a-txn']);
});
