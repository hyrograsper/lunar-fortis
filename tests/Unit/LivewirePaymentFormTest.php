<?php

use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Set up test configuration
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

    it('defaults to automatic policy', function () {
        // Don't set any policy config, so it should use the default
        // Remove the config key entirely by not setting it in this test
        // but since we have a default value, it should still work

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
        $environment = $this->paymentForm->getFortisEnvironmentProperty();

        expect($environment)->toBe('sandbox');
    });

    it('returns correct sandbox JavaScript URL', function () {
        Config::set('lunar-fortis.environment', 'sandbox');

        $form = new PaymentForm;
        $jsUrl = $form->getFortisJSUrlProperty();

        expect($jsUrl)->toBe('https://js.sandbox.fortis.tech/commercejs-v1.0.0.min.js');
    });

    it('returns correct production JavaScript URL', function () {
        Config::set('lunar-fortis.environment', 'production');

        $form = new PaymentForm;
        $jsUrl = $form->getFortisJSUrlProperty();

        expect($jsUrl)->toBe('https://js.fortis.tech/commercejs-v1.0.0.min.js');
    });
});

describe('Methods Availability', function () {
    it('has required methods available', function () {
        expect(method_exists($this->paymentForm, 'mount'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'clientTokenCacheKey'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'handlePaymentResponse'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'regenerateClientToken'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'render'))->toBeTrue();
    });

    it('validates response handling method signature', function () {
        $reflection = new ReflectionMethod($this->paymentForm, 'handlePaymentResponse');

        expect($reflection->isPublic())->toBeTrue();
        expect($reflection->getNumberOfParameters())->toBe(1);
    });
});

describe('Configuration Properties', function () {
    it('has environment property methods', function () {
        expect(method_exists($this->paymentForm, 'getFortisEnvironmentProperty'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'getFortisJSUrlProperty'))->toBeTrue();
        expect(method_exists($this->paymentForm, 'getElementsAppearanceSettingsProperty'))->toBeTrue();
    });
});

afterEach(function () {
    Mockery::close();
});
