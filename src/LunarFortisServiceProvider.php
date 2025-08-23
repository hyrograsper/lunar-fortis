<?php

namespace Hyrograsper\LunarFortis;

use Hyrograsper\LunarFortis\Livewire\PaymentForm;
use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\Observers\TerminalObserver;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Livewire\Livewire;
use Lunar\Base\PaymentManagerInterface;
use Lunar\Facades\Payments;
use Lunar\Managers\PaymentManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LunarFortisServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('lunar-fortis')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_fortis_terminals_table',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PaymentManagerInterface::class, function ($app) {
            return $app->make(PaymentManager::class);
        });

        // Register the payment types with Lunar
        Payments::extend('fortis', function ($app) {
            return $app->make(FortisPaymentType::class);
        });

        Payments::extend('fortis-terminal', function ($app) {
            return $app->make(FortisTerminalPaymentType::class);
        });
    }

    public function packageBooted(): void
    {
        // Register Livewire components
        Livewire::component('lunar-fortis.payment-form', PaymentForm::class);

        // Register model observers
        Terminal::observe(TerminalObserver::class);
    }
}
