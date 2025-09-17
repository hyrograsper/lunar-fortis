<?php

use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Set up test configuration
    Config::set('lunar-fortis.policy', 'automatic');
    Config::set('lunar-fortis.environment', 'sandbox');
    Config::set('lunar-fortis.success_event_class', null);
    Config::set('lunar-fortis.success_livewire_event', 'payment-success');
    Config::set('lunar-fortis.success_redirect', false);
    Config::set('lunar-fortis.elements.appearance', [
        'theme' => 'stripe',
        'variables' => ['colorPrimary' => '#0570de']
    ]);

    // Mock database tables for tests
    \Illuminate\Support\Facades\Schema::create('carts', function ($table) {
        $table->id();
        $table->foreignId('user_id')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
    });

    \Illuminate\Support\Facades\Schema::create('orders', function ($table) {
        $table->id();
        $table->string('reference');
        $table->string('status')->nullable();
        $table->timestamp('placed_at')->nullable();
        $table->timestamps();
    });

    \Illuminate\Support\Facades\Schema::create('order_addresses', function ($table) {
        $table->id();
        $table->foreignId('order_id');
        $table->string('type');
        $table->string('line_one');
        $table->string('city');
        $table->timestamps();
    });

    // Create test cart
    $this->cart = new class {
        public $id = 1;
        public $user_id = 123;
        public $total;
        public $billingAddress;

        public function __construct() {
            $this->total = new class {
                public $value = 1500;
            };
            $this->billingAddress = new OrderAddress([
                'type' => 'billing',
                'line_one' => '123 Test St',
                'city' => 'Test City',
            ]);
        }

        public function calculate() {
            // Mock implementation
            return $this;
        }
    };
});

describe('PaymentForm Component Mounting', function () {
    it('mounts with correct policy from config', function () {
        Config::set('lunar-fortis.policy', 'manual');

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        expect($component->get('policy'))->toBe('manual');
    });

    it('defaults to automatic policy when config is missing', function () {
        Config::set('lunar-fortis.policy', null);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        expect($component->get('policy'))->toBe('automatic');
    });
});

describe('Client Token Management', function () {
    it('generates client token for cart total', function () {
        LunarFortis::shouldReceive('getClientTokenForSaleAmount')
            ->once()
            ->with(1500, 'auth-only')
            ->andReturn('test-client-token-123');

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $token = $component->call('clientToken');

        expect($token)->toBe('test-client-token-123');
    });

    it('caches client token for performance', function () {
        LunarFortis::shouldReceive('getClientTokenForSaleAmount')
            ->once()
            ->with(1500, 'auth-only')
            ->andReturn('cached-token-456');

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        // First call should hit the API
        $token1 = $component->call('clientToken');
        // Second call should use cache
        $token2 = $component->call('clientToken');

        expect($token1)->toBe('cached-token-456');
        expect($token2)->toBe('cached-token-456');
    });

    it('generates correct cache key for logged in user', function () {
        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $cacheKey = $component->call('clientTokenCacheKey');

        expect($cacheKey)->toBe('fortis_client_token:123');
    });

    it('generates correct cache key for guest cart', function () {
        $guestCart = clone $this->cart;
        $guestCart->user_id = null;

        $component = Livewire::test(PaymentForm::class, ['cart' => $guestCart]);

        $cacheKey = $component->call('clientTokenCacheKey');

        expect($cacheKey)->toBe('fortis_client_token:1');
    });

    it('regenerates client token when requested', function () {
        Cache::shouldReceive('forget')
            ->once()
            ->with('fortis_client_token:123');

        LunarFortis::shouldReceive('getClientTokenForSaleAmount')
            ->once()
            ->with(1500, 'auth-only')
            ->andReturn('regenerated-token-789');

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $component->call('regenerateClientToken')
            ->assertDispatched('token-regenerated', 'regenerated-token-789');
    });
});

describe('Payment Response Handling', function () {
    it('handles successful payment response', function () {
        $order = Order::create([
            'reference' => 'ORD-SUCCESS-123',
            'status' => 'payment-received',
        ]);

        $mockPaymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Payment successful',
            orderId: $order->id,
            paymentType: FortisPaymentType::PAYMENT_TYPE,
        );

        Payments::shouldReceive('driver')
            ->with(FortisPaymentType::PAYMENT_TYPE)
            ->andReturnSelf();
        Payments::shouldReceive('cart')
            ->with($this->cart)
            ->andReturnSelf();
        Payments::shouldReceive('withData')
            ->andReturnSelf();
        Payments::shouldReceive('authorize')
            ->andReturn($mockPaymentAuthorize);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $response = [
            'data' => [
                'client_token' => 'token-123',
                'transaction_id' => 'trans-456'
            ]
        ];

        $component->call('handlePaymentResponse', $response)
            ->assertDispatched('payment-success', function ($order) {
                return $order->reference === 'ORD-SUCCESS-123';
            });
    });

    it('handles payment response with missing data key', function () {
        Log::shouldReceive('error')
            ->once()
            ->with('LunarFortis: Fortis Payment response missing "data" key.', ['invalid' => 'response']);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $component->call('handlePaymentResponse', ['invalid' => 'response'])
            ->assertDispatched('payment-error', 'Invalid payment response from gateway.');
    });

    it('handles failed payment authorization', function () {
        $mockPaymentAuthorize = new PaymentAuthorize(
            success: false,
            message: 'Payment declined',
            orderId: null,
            paymentType: FortisPaymentType::PAYMENT_TYPE,
        );

        Payments::shouldReceive('driver')
            ->with(FortisPaymentType::PAYMENT_TYPE)
            ->andReturnSelf();
        Payments::shouldReceive('cart')
            ->andReturnSelf();
        Payments::shouldReceive('withData')
            ->andReturnSelf();
        Payments::shouldReceive('authorize')
            ->andReturn($mockPaymentAuthorize);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $response = [
            'data' => [
                'client_token' => 'token-failed',
                'error' => 'Card declined'
            ]
        ];

        $component->call('handlePaymentResponse', $response)
            ->assertDispatched('payment-error', 'Payment declined');
    });

    it('dispatches custom success event class when configured', function () {
        Config::set('lunar-fortis.success_event_class', 'App\\Events\\PaymentSuccess');

        $order = Order::create([
            'reference' => 'ORD-EVENT-123',
            'status' => 'payment-received',
        ]);

        $mockPaymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Payment successful',
            orderId: $order->id,
            paymentType: FortisPaymentType::PAYMENT_TYPE,
        );

        Payments::shouldReceive('driver')
            ->with(FortisPaymentType::PAYMENT_TYPE)
            ->andReturnSelf();
        Payments::shouldReceive('cart')
            ->andReturnSelf();
        Payments::shouldReceive('withData')
            ->andReturnSelf();
        Payments::shouldReceive('authorize')
            ->andReturn($mockPaymentAuthorize);

        // Since we can't easily test the actual event class dispatch without creating the class,
        // we'll mock the scenario where the class doesn't exist and logs an error
        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::pattern('/Unable to dispatch Success Event Class/'));

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $response = ['data' => ['token' => 'success']];

        $component->call('handlePaymentResponse', $response);
    });
});

describe('Success Redirect Handling', function () {
    beforeEach(function () {
        // Set up a test route for redirect testing
        Route::get('/success/{reference}', function ($reference) {
            return "Success: {$reference}";
        })->name('payment.success');
    });

    it('redirects to signed route when configured', function () {
        Config::set('lunar-fortis.success_redirect', [
            'route_name' => 'payment.success',
            'use_signed_route' => true,
        ]);

        $order = Order::create([
            'reference' => 'ORD-REDIRECT-123',
            'status' => 'payment-received',
        ]);

        $mockPaymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Payment successful',
            orderId: $order->id,
            paymentType: FortisPaymentType::PAYMENT_TYPE,
        );

        Payments::shouldReceive('driver')
            ->andReturnSelf();
        Payments::shouldReceive('cart')
            ->andReturnSelf();
        Payments::shouldReceive('withData')
            ->andReturnSelf();
        Payments::shouldReceive('authorize')
            ->andReturn($mockPaymentAuthorize);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $response = ['data' => ['token' => 'redirect-test']];

        $component->call('handlePaymentResponse', $response)
            ->assertRedirect(); // Will redirect to signed route
    });

    it('redirects to regular route when signed routes disabled', function () {
        Config::set('lunar-fortis.success_redirect', [
            'route_name' => 'payment.success',
            'use_signed_route' => false,
        ]);

        $order = Order::create([
            'reference' => 'ORD-REGULAR-123',
            'status' => 'payment-received',
        ]);

        $mockPaymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Payment successful',
            orderId: $order->id,
            paymentType: FortisPaymentType::PAYMENT_TYPE,
        );

        Payments::shouldReceive('driver')
            ->andReturnSelf();
        Payments::shouldReceive('cart')
            ->andReturnSelf();
        Payments::shouldReceive('withData')
            ->andReturnSelf();
        Payments::shouldReceive('authorize')
            ->andReturn($mockPaymentAuthorize);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $response = ['data' => ['token' => 'regular-redirect']];

        $component->call('handlePaymentResponse', $response)
            ->assertRedirect(route('payment.success', ['reference' => 'ORD-REGULAR-123']));
    });

    it('redirects to custom URI when configured', function () {
        Config::set('lunar-fortis.success_redirect', [
            'uri' => 'https://example.com/success',
        ]);

        $order = Order::create([
            'reference' => 'ORD-URI-123',
            'status' => 'payment-received',
        ]);

        $mockPaymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Payment successful',
            orderId: $order->id,
            paymentType: FortisPaymentType::PAYMENT_TYPE,
        );

        Payments::shouldReceive('driver')
            ->andReturnSelf();
        Payments::shouldReceive('cart')
            ->andReturnSelf();
        Payments::shouldReceive('withData')
            ->andReturnSelf();
        Payments::shouldReceive('authorize')
            ->andReturn($mockPaymentAuthorize);

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $response = ['data' => ['token' => 'uri-redirect']];

        $component->call('handlePaymentResponse', $response)
            ->assertRedirect('https://example.com/success?reference=ORD-URI-123');
    });
});

describe('Component Properties', function () {
    it('returns billing address property', function () {
        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $billing = $component->get('billing');

        expect($billing)->toBeInstanceOf(OrderAddress::class);
        expect($billing->line_one)->toBe('123 Test St');
    });

    it('returns elements appearance settings as JSON', function () {
        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $settings = $component->get('elementsAppearanceSettings');

        expect($settings)->toBe('{"theme":"stripe","variables":{"colorPrimary":"#0570de"}}');
    });

    it('returns correct fortis environment', function () {
        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $environment = $component->get('fortisEnvironment');

        expect($environment)->toBe('sandbox');
    });

    it('returns correct sandbox JavaScript URL', function () {
        Config::set('lunar-fortis.environment', 'sandbox');

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $jsUrl = $component->get('fortisJSUrl');

        expect($jsUrl)->toBe('https://js.sandbox.fortis.tech/commercejs-v1.0.0.min.js');
    });

    it('returns correct production JavaScript URL', function () {
        Config::set('lunar-fortis.environment', 'production');

        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $jsUrl = $component->get('fortisJSUrl');

        expect($jsUrl)->toBe('https://js.fortis.tech/commercejs-v1.0.0.min.js');
    });
});

describe('Component Rendering', function () {
    it('renders with correct view', function () {
        $component = Livewire::test(PaymentForm::class, ['cart' => $this->cart]);

        $component->assertViewIs('lunar-fortis::components.payment-form');
    });
});

afterEach(function () {
    Mockery::close();
});