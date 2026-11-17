<?php

use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(InteractsWithLunarCarts::class);

beforeEach(function () {
    $this->setUpLunar();

    Http::preventStrayRequests();
    Http::fake([
        'api.sandbox.fortis.tech/v1/elements/transaction/intention' => Http::response(['data' => ['client_token' => 'view-client-token']]),
    ]);

    Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));
});

function paymentFormRootElements(string $html): array
{
    $document = new DOMDocument;
    libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="utf-8"?><html><body>'.$html.'</body></html>');
    libxml_clear_errors();

    $body = $document->getElementsByTagName('body')->item(0);

    return array_values(array_filter(
        iterator_to_array($body->childNodes),
        fn (DOMNode $node) => $node instanceof DOMElement,
    ));
}

it('renders a single root element so Alpine and $wire live inside the component', function () {
    $html = Livewire::test(PaymentForm::class, ['cart' => $this->makeCart(1500)])->html();

    $roots = paymentFormRootElements($html);

    expect($roots)->toHaveCount(1)
        ->and($roots[0]->tagName)->toBe('div')
        ->and((new DOMXPath($roots[0]->ownerDocument))->query('.//*[@x-data]', $roots[0])->length)->toBe(1);
});

it('declares the shared Elements handle without a top-level let that fails when the script runs twice', function () {
    $html = Livewire::test(PaymentForm::class, ['cart' => $this->makeCart(1500)])->html();

    expect($html)->not->toContain('let elements')
        ->and($html)->toContain('window.elements ??= null');
});

it('passes the client token as a data attribute so a new token does not restart the Alpine component', function () {
    $html = Livewire::test(PaymentForm::class, ['cart' => $this->makeCart(1500)])->html();

    $root = paymentFormRootElements($html)[0];
    $component = (new DOMXPath($root->ownerDocument))->query('.//*[@x-data]', $root)->item(0);

    expect($component->getAttribute('x-data'))->not->toContain('view-client-token')
        ->and($component->getAttribute('data-token'))->toBe('view-client-token')
        ->and($component->getAttribute('data-transaction-api-id'))->toStartWith('lf-');
});
