<?php

use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('lunar-fortis.policy', 'automatic');
    Config::set('lunar-fortis.environment', 'sandbox');
    Config::set('lunar-fortis.elements.appearance', [
        'theme' => 'stripe',
        'variables' => ['colorPrimary' => '#0570de'],
    ]);

    $this->paymentForm = new PaymentForm;
});

describe('PaymentForm Configuration', function () {
    it('sets policy from config on mount', function () {
        Config::set('lunar-fortis.policy', 'manual');

        $form = new PaymentForm;
        $form->mount();

        expect($form->policy)->toBe('manual');
    });

    it('defaults to automatic policy when config is not set', function () {
        Config::set('lunar-fortis.policy');

        $form = new PaymentForm;
        $form->mount();

        expect($form->policy)->toBe('automatic');
    });
});

describe('Component Properties', function () {
    it('returns elements appearance settings as JSON', function () {
        $settings = $this->paymentForm->getElementsAppearanceSettingsProperty();

        expect($settings)->toBe('{"theme":"stripe","variables":{"colorPrimary":"#0570de"}}');
    });

    it('returns correct fortis environment', function () {
        expect($this->paymentForm->getFortisEnvironmentProperty())->toBe('sandbox');
    });

    it('returns correct sandbox JavaScript URL', function () {
        Config::set('lunar-fortis.environment', 'sandbox');

        $form = new PaymentForm;

        expect($form->getFortisJSUrlProperty())
            ->toBe('https://js.sandbox.fortis.tech/commercejs-v1.0.0.min.js');
    });

    it('returns correct production JavaScript URL', function () {
        Config::set('lunar-fortis.environment', 'production');

        $form = new PaymentForm;

        expect($form->getFortisJSUrlProperty())
            ->toBe('https://js.fortis.tech/commercejs-v1.0.0.min.js');
    });
});

describe('Component Rendering', function () {
    it('renders the correct view', function () {
        $view = $this->paymentForm->render();

        expect($view->name())->toBe('lunar-fortis::components.payment-form');
    });
});

describe('Methods Availability', function () {
    it('has required public methods', function () {
        expect(method_exists($this->paymentForm, 'mount'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'isZeroDollarCart'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'clientToken'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'clientTokenCacheKey'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'handlePaymentResponse'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'regenerateClientToken'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'render'))->toBeTrue();
    });

    it('validates handlePaymentResponse accepts one parameter', function () {
        $reflection = new ReflectionMethod($this->paymentForm, 'handlePaymentResponse');

        expect($reflection->isPublic())->toBeTrue()
            ->and($reflection->getNumberOfParameters())->toBe(1);
    });

    it('validates regenerateClientToken accepts no parameters', function () {
        $reflection = new ReflectionMethod($this->paymentForm, 'regenerateClientToken');

        expect($reflection->isPublic())->toBeTrue()
            ->and($reflection->getNumberOfParameters())->toBe(0);
    });
});

describe('Configuration Properties', function () {
    it('has environment property methods', function () {
        expect(method_exists($this->paymentForm, 'getFortisEnvironmentProperty'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'getFortisJSUrlProperty'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'getElementsAppearanceSettingsProperty'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'getBillingProperty'))->toBeTrue();
    });
});

describe('Edge Cases and Error Handling', function () {
    it('handles empty payment response without throwing', function () {
        expect(fn () => $this->paymentForm->handlePaymentResponse([]))
            ->not->toThrow(Exception::class);
    });

    // TODO: Add integration test for zero-dollar cart scenario.
    // Unit testing requires complex mocking of Lunar\DataTypes\Price.
    // - PaymentForm::isZeroDollarCart() returns true when cart total < 1
    // - PaymentForm::clientToken() returns null for zero-dollar carts
    // - Blade view shows "Payment Not Required" message accordingly
});
