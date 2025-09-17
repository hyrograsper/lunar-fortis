<?php

use Hyrograsper\LunarFortis\Facades\LunarFortis as LunarFortisFacade;
use Hyrograsper\LunarFortis\LunarFortis;
use Illuminate\Support\Facades\Facade;

describe('LunarFortis Facade', function () {
    it('is a valid facade', function () {
        expect(LunarFortisFacade::class)->toBeSubclassOf(Facade::class);
    });

    it('returns correct facade accessor', function () {
        $reflection = new ReflectionClass(LunarFortisFacade::class);
        $method = $reflection->getMethod('getFacadeAccessor');
        $method->setAccessible(true);

        $accessor = $method->invoke(null);

        expect($accessor)->toBe(LunarFortis::class);
    });

    it('can be resolved from container', function () {
        $instance = app(LunarFortis::class);

        expect($instance)->toBeInstanceOf(LunarFortis::class);
    });

    it('facade methods proxy to underlying class', function () {
        // Test that facade methods exist and can be called
        expect(method_exists(LunarFortis::class, 'getClientTokenForSaleAmount'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'completeAuthorizedTransaction'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'refund'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'getTransaction'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'createTerminal'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'listTerminals'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'getTerminal'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'updateTerminal'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'authorizeTerminalCreditCard'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'checkTerminalTransactionStatus'))->toBeTrue();
    });

    it('provides terminal helper methods', function () {
        expect(method_exists(LunarFortis::class, 'createSimpleTerminal'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'getActiveTerminals'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'setTerminalStatus'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'processTerminalCreditCardAuth'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'waitForTerminalTransaction'))->toBeTrue();
        expect(method_exists(LunarFortis::class, 'captureTerminalTransaction'))->toBeTrue();
    });
});