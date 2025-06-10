<?php

namespace Hyrograsper\LunarFortis;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\Base\PaymentManagerInterface;
use Lunar\Facades\Payments;
use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Lunar\Managers\PaymentManager;

class LunarFortisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge package config with application's config
        $this->mergeConfigFrom(__DIR__.'/../config/fortis.php', 'lunar.fortis');
        $this->mergeConfigFrom(__DIR__.'/../config/fortis_services.php', 'services.fortis');

        $this->app->singleton(PaymentManagerInterface::class, function ($app) {
            return $app->make(PaymentManager::class);
        });

        // Register the payment type with Lunar
        Payments::extend('fortis', function ($app) {
            return $app->make(FortisPaymentType::class);
        });
    }

    public function boot(): void
    {
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lunar-fortis');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/lunar-fortis'),
        ], 'lunar-fortis-views');

        // Publish config files
        $this->publishes([
            __DIR__.'/../config/fortis.php' => config_path('lunar/fortis.php'),
            __DIR__.'/../config/fortis_services.php' => config_path('services.php'),
        ], 'lunar-fortis-config');

        // Register Livewire components
        Livewire::component('lunar-fortis.payment-form', PaymentForm::class);
    }
}