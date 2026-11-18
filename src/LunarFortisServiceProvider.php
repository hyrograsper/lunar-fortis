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
    }

    public function packageBooted(): void
    {
        // Drivers are registered in boot (per the Lunar docs): extending during
        // the register phase resolves the manager before other providers can
        // rebind it, silently losing the drivers in some contexts (queue workers).
        Payments::extend('fortis', function ($app) {
            return $app->make(FortisPaymentType::class);
        });

        Payments::extend('fortis-terminal', function ($app) {
            return $app->make(FortisTerminalPaymentType::class);
        });

        Livewire::component('lunar-fortis.payment-form', PaymentForm::class);

        Terminal::observe(TerminalObserver::class);
    }
}
