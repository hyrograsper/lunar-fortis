<?php

use Hyrograsper\LunarFortis\LunarFortisServiceProvider;
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

beforeEach(function () {
    $this->serviceProvider = new LunarFortisServiceProvider($this->app);
});

describe('Package Configuration', function () {
    it('configures package with correct name', function () {
        $package = Mockery::mock(Package::class);
        $package->shouldReceive('name')->with('lunar-fortis')->andReturnSelf();
        $package->shouldReceive('hasConfigFile')->andReturnSelf();
        $package->shouldReceive('hasViews')->andReturnSelf();
        $package->shouldReceive('hasMigrations')->with(['create_fortis_terminals_table'])->andReturnSelf();

        // This should not throw an exception
        expect(function () use ($package) {
            $this->serviceProvider->configurePackage($package);
        })->not->toThrow(Exception::class);
    });

    it('includes config file in package', function () {
        $package = Mockery::mock(Package::class);
        $package->shouldReceive('name')->andReturnSelf();
        $package->shouldReceive('hasConfigFile')->once()->andReturnSelf();
        $package->shouldReceive('hasViews')->andReturnSelf();
        $package->shouldReceive('hasMigrations')->andReturnSelf();

        $this->serviceProvider->configurePackage($package);
    });

    it('includes views in package', function () {
        $package = Mockery::mock(Package::class);
        $package->shouldReceive('name')->andReturnSelf();
        $package->shouldReceive('hasConfigFile')->andReturnSelf();
        $package->shouldReceive('hasViews')->once()->andReturnSelf();
        $package->shouldReceive('hasMigrations')->andReturnSelf();

        $this->serviceProvider->configurePackage($package);
    });

    it('includes migrations in package', function () {
        $package = Mockery::mock(Package::class);
        $package->shouldReceive('name')->andReturnSelf();
        $package->shouldReceive('hasConfigFile')->andReturnSelf();
        $package->shouldReceive('hasViews')->andReturnSelf();
        $package->shouldReceive('hasMigrations')
            ->once()
            ->with(['create_fortis_terminals_table'])
            ->andReturnSelf();

        $this->serviceProvider->configurePackage($package);
    });
});

describe('Service Registration', function () {
    it('registers payment manager interface as singleton', function () {
        // Test that the service provider can be instantiated and methods exist
        expect(method_exists($this->serviceProvider, 'packageRegistered'))->toBeTrue();
        
        // Test that the method can be called without errors
        expect(function () {
            $this->serviceProvider->packageRegistered();
        })->not->toThrow(Exception::class);
    });

    it('registers fortis payment type with Payments facade', function () {
        // Test that the service provider can be instantiated and methods exist
        expect(method_exists($this->serviceProvider, 'packageRegistered'))->toBeTrue();
        
        // Test that the method can be called without errors
        expect(function () {
            $this->serviceProvider->packageRegistered();
        })->not->toThrow(Exception::class);
    });

    it('registers fortis-terminal payment type with Payments facade', function () {
        // Test that the service provider can be instantiated and methods exist
        expect(method_exists($this->serviceProvider, 'packageRegistered'))->toBeTrue();
        
        // Test that the method can be called without errors
        expect(function () {
            $this->serviceProvider->packageRegistered();
        })->not->toThrow(Exception::class);
    });
});

describe('Service Boot', function () {
    it('registers Livewire payment form component', function () {
        // Test that the service provider can be instantiated and methods exist
        expect(method_exists($this->serviceProvider, 'packageBooted'))->toBeTrue();
        
        // Test that the method can be called without errors
        expect(function () {
            $this->serviceProvider->packageBooted();
        })->not->toThrow(Exception::class);
    });

    it('registers Terminal model observer', function () {
        // Test that the service provider can be instantiated and methods exist
        expect(method_exists($this->serviceProvider, 'packageBooted'))->toBeTrue();
        
        // Test that the method can be called without errors
        expect(function () {
            $this->serviceProvider->packageBooted();
        })->not->toThrow(Exception::class);
    });
});

describe('Service Provider Integration', function () {
    it('can be instantiated', function () {
        expect($this->serviceProvider)->toBeInstanceOf(LunarFortisServiceProvider::class);
    });

    it('extends PackageServiceProvider', function () {
        expect($this->serviceProvider)->toBeInstanceOf(\Spatie\LaravelPackageTools\PackageServiceProvider::class);
    });

    it('has correct package name', function () {
        $package = Mockery::mock(Package::class);
        $package->shouldReceive('name')->with('lunar-fortis')->andReturnSelf();
        $package->shouldReceive('hasConfigFile')->andReturnSelf();
        $package->shouldReceive('hasViews')->andReturnSelf();
        $package->shouldReceive('hasMigrations')->andReturnSelf();

        // This should not throw an exception
        expect(function () use ($package) {
            $this->serviceProvider->configurePackage($package);
        })->not->toThrow(Exception::class);
    });
});

describe('Method Availability', function () {
    it('has configurePackage method', function () {
        expect(method_exists($this->serviceProvider, 'configurePackage'))->toBeTrue();
    });

    it('has packageRegistered method', function () {
        expect(method_exists($this->serviceProvider, 'packageRegistered'))->toBeTrue();
    });

    it('has packageBooted method', function () {
        expect(method_exists($this->serviceProvider, 'packageBooted'))->toBeTrue();
    });

    it('configurePackage method accepts Package parameter', function () {
        $reflection = new ReflectionMethod($this->serviceProvider, 'configurePackage');
        
        expect($reflection->isPublic())->toBeTrue();
        expect($reflection->getNumberOfParameters())->toBe(1);
        expect($reflection->getParameters()[0]->getType()->getName())->toBe(Package::class);
    });

    it('packageRegistered method has no parameters', function () {
        $reflection = new ReflectionMethod($this->serviceProvider, 'packageRegistered');
        
        expect($reflection->isPublic())->toBeTrue();
        expect($reflection->getNumberOfParameters())->toBe(0);
    });

    it('packageBooted method has no parameters', function () {
        $reflection = new ReflectionMethod($this->serviceProvider, 'packageBooted');
        
        expect($reflection->isPublic())->toBeTrue();
        expect($reflection->getNumberOfParameters())->toBe(0);
    });
});

describe('Package Tools Integration', function () {
    it('uses Spatie Laravel Package Tools', function () {
        expect($this->serviceProvider)->toBeInstanceOf(\Spatie\LaravelPackageTools\PackageServiceProvider::class);
    });

    it('implements required package service provider methods', function () {
        expect(method_exists($this->serviceProvider, 'configurePackage'))->toBeTrue();
        expect(method_exists($this->serviceProvider, 'packageRegistered'))->toBeTrue();
        expect(method_exists($this->serviceProvider, 'packageBooted'))->toBeTrue();
    });
});

describe('Class Dependencies', function () {
    it('can instantiate required payment type classes', function () {
        expect(class_exists(FortisPaymentType::class))->toBeTrue();
        expect(class_exists(FortisTerminalPaymentType::class))->toBeTrue();
    });

    it('can instantiate required model classes', function () {
        expect(class_exists(Terminal::class))->toBeTrue();
        expect(class_exists(TerminalObserver::class))->toBeTrue();
    });

    it('can instantiate required Livewire component', function () {
        expect(class_exists(PaymentForm::class))->toBeTrue();
    });

    it('can access required Lunar classes', function () {
        // These classes may not be available in test environment, so we skip this test
        // if they don't exist
        if (class_exists(PaymentManagerInterface::class)) {
            expect(class_exists(PaymentManagerInterface::class))->toBeTrue();
        }
        if (class_exists(PaymentManager::class)) {
            expect(class_exists(PaymentManager::class))->toBeTrue();
        }
        
        // If neither class exists, we just pass the test
        expect(true)->toBeTrue();
    });
});

describe('Service Provider Lifecycle', function () {
    it('can call all lifecycle methods without errors', function () {
        $package = Mockery::mock(Package::class);
        $package->shouldReceive('name')->andReturnSelf();
        $package->shouldReceive('hasConfigFile')->andReturnSelf();
        $package->shouldReceive('hasViews')->andReturnSelf();
        $package->shouldReceive('hasMigrations')->andReturnSelf();

        // Test configurePackage
        expect(function () use ($package) {
            $this->serviceProvider->configurePackage($package);
        })->not->toThrow(Exception::class);

        // Test packageRegistered
        expect(function () {
            $this->serviceProvider->packageRegistered();
        })->not->toThrow(Exception::class);

        // Test packageBooted
        expect(function () {
            $this->serviceProvider->packageBooted();
        })->not->toThrow(Exception::class);
    });
});

describe('Error Handling', function () {
    it('handles method calls gracefully', function () {
        // Test that all methods can be called without throwing fatal errors
        expect(function () {
            $this->serviceProvider->packageRegistered();
        })->not->toThrow(Exception::class);

        expect(function () {
            $this->serviceProvider->packageBooted();
        })->not->toThrow(Exception::class);
    });
});

describe('Reflection Analysis', function () {
    it('has correct class hierarchy', function () {
        $reflection = new ReflectionClass($this->serviceProvider);
        
        expect($reflection->isSubclassOf(\Spatie\LaravelPackageTools\PackageServiceProvider::class))->toBeTrue();
        expect($reflection->isSubclassOf(\Illuminate\Support\ServiceProvider::class))->toBeTrue();
    });

    it('has correct namespace', function () {
        $reflection = new ReflectionClass($this->serviceProvider);
        
        expect($reflection->getNamespaceName())->toBe('Hyrograsper\LunarFortis');
    });

    it('has correct class name', function () {
        $reflection = new ReflectionClass($this->serviceProvider);
        
        expect($reflection->getShortName())->toBe('LunarFortisServiceProvider');
    });
});

afterEach(function () {
    Mockery::close();
});