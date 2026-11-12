<?php

namespace Hyrograsper\LunarFortis\Tests\Concerns;

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;
use RuntimeException;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

trait InteractsWithLunarCarts
{
    protected function setUpLunar(): void
    {
        config()->set('activitylog.enabled', false);
        config()->set('lunar-fortis.http.retry.attempts', 1);

        $this->app->register($this->nestedSetProvider());

        if (class_exists(ConverterServiceProvider::class)) {
            $this->app->register(ConverterServiceProvider::class);
        }

        $this->app->register(BlinkServiceProvider::class);
        $this->app->register(ActivitylogServiceProvider::class);
        $this->app->register(MediaLibraryServiceProvider::class);
        $this->app->register(LunarServiceProvider::class);

        $this->loadLaravelMigrations();
        $this->artisan('migrate')->run();

        Language::factory()->create(['default' => true]);
        Currency::factory()->create([
            'code' => 'USD',
            'default' => true,
            'decimal_places' => 2,
            'exchange_rate' => 1,
        ]);
        Channel::factory()->create(['default' => true]);
        CustomerGroup::factory()->create(['default' => true]);
        TaxClass::factory()->create(['default' => true]);
        TaxZone::factory()->create(['default' => true]);
    }

    /** @return class-string */
    protected function nestedSetProvider(): string
    {
        $candidates = [
            'Lunar\\Nestedset\\NestedSetServiceProvider',
            'Kalnoy\\Nestedset\\NestedSetServiceProvider',
        ];

        foreach ($candidates as $candidate) {
            if (class_exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException('No nested set service provider found.');
    }

    protected function makeCart(int $amount): Cart
    {
        $currency = Currency::getDefault();

        $variant = ProductVariant::factory()->create([
            'tax_class_id' => TaxClass::getDefault()->id,
            'shippable' => false,
        ]);

        Price::factory()->create([
            'price' => $amount,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
            'channel_id' => Channel::getDefault()->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'quantity' => 1,
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
            'country_id' => Country::factory()->create()->id,
        ]);

        return $cart->refresh();
    }
}
