<?php

use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Illuminate\Support\Facades\Config;
use Lunar\Models\Contracts\Transaction as TransactionContract;

beforeEach(function () {
    Config::set('lunar-fortis.policy', 'manual');
    Config::set('lunar-fortis.status_mapping', [
        'payment-authorized' => 'payment-authorized',
        'payment-received' => 'payment-received',
    ]);

    $this->mockFortis = Mockery::mock(LunarFortis::class);
    $this->paymentType = new FortisPaymentType($this->mockFortis);
});

describe('FortisPaymentType Configuration', function () {
    it('sets payment type constant correctly', function () {
        expect(FortisPaymentType::PAYMENT_TYPE)->toBe('fortis');
    });

    it('uses manual policy by default', function () {
        Config::set('lunar-fortis.policy', 'manual');
        $paymentType = new FortisPaymentType($this->mockFortis);

        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('manual');
    });

    it('uses automatic policy when configured', function () {
        Config::set('lunar-fortis.policy', 'automatic');
        $paymentType = new FortisPaymentType($this->mockFortis);

        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('automatic');
    });
});

describe('FortisPaymentType::capture()', function () {
    it('handles exception during capture', function () {
        $parentTransaction = Mockery::mock(TransactionContract::class);

        $this->mockFortis->shouldReceive('completeAuthorizedTransaction')
            ->once()
            ->andThrow(new Exception('Network error'));

        $result = $this->paymentType->capture($parentTransaction, 500);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('Network error');
    });
});

describe('FortisPaymentType::refund()', function () {
    it('handles exception during refund', function () {
        $parentTransaction = Mockery::mock(TransactionContract::class);

        $this->mockFortis->shouldReceive('refund')
            ->once()
            ->andThrow(new Exception('Refund API error'));

        $result = $this->paymentType->refund($parentTransaction, 250);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('Refund API error');
    });
});

afterEach(function () {
    Mockery::close();
});
