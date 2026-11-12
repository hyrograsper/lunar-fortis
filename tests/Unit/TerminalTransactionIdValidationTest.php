<?php

use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Support\Facades\Http;

uses(InteractsWithLunarCarts::class);

beforeEach(function () {
    $this->setUpLunar();

    Http::preventStrayRequests();
    Http::fake();

    $this->cart = $this->makeCart(1000);
});

it('rejects a malformed terminal transaction id without calling Fortis', function (string $transactionId) {
    $result = app(FortisTerminalPaymentType::class)
        ->cart($this->cart)
        ->withData(['fortis_transaction_id' => $transactionId])
        ->authorize();

    expect($result->success)->toBeFalse();
    expect($result->message)->toBe('Invalid Fortis transaction id.');

    Http::assertNothingSent();
})->with([
    'path traversal' => ['../terminals'],
    'query string' => ['abc?expand=all'],
    'slash' => ['abc/auth-complete'],
    'whitespace' => ['abc def'],
    'too long' => [str_repeat('a', 65)],
]);

it('clears the partial payment flag after authorizing when Lunar defines it', function () {
    $paymentType = app(FortisTerminalPaymentType::class);
    $reflection = new ReflectionObject($paymentType);

    if (! $reflection->hasProperty('allowPartialPayment')) {
        $this->markTestSkipped('This Lunar version has no allowPartialPayment property.');
    }

    $property = $reflection->getProperty('allowPartialPayment');
    $property->setValue($paymentType, true);

    $paymentType->cart($this->cart)->withData(['fortis_transaction_id' => 'bad id'])->authorize();

    expect($property->getValue($paymentType))->toBeFalse();
});
