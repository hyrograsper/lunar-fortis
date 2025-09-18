<?php

use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Illuminate\Support\Facades\Config;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;

beforeEach(function () {
    Config::set('lunar-fortis.policy', 'manual');
    Config::set('lunar-fortis.status_mapping', [
        'payment-authorized' => 'payment-authorized',
        'payment-received' => 'payment-received',
    ]);

    $this->paymentType = new FortisPaymentType(app(\Hyrograsper\LunarFortis\LunarFortis::class));
});

describe('FortisPaymentType Configuration', function () {
    it('sets payment type constant correctly', function () {
        expect(FortisPaymentType::PAYMENT_TYPE)->toBe('fortis');
    });

    it('sets manual policy by default', function () {
        $paymentType = new FortisPaymentType(app(\Hyrograsper\LunarFortis\LunarFortis::class));

        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('manual');
    });

    it('uses automatic policy when configured', function () {
        Config::set('lunar-fortis.policy', 'automatic');

        $paymentType = new FortisPaymentType(app(\Hyrograsper\LunarFortis\LunarFortis::class));
        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('automatic');
    });
});

describe('PaymentAuthorize Creation', function () {
    it('creates successful payment authorize', function () {
        $authorize = new PaymentAuthorize(
            success: true,
            message: 'Payment authorized',
            orderId: 123,
            paymentType: FortisPaymentType::PAYMENT_TYPE,
        );

        expect($authorize->success)->toBeTrue();
        expect($authorize->message)->toBe('Payment authorized');
        expect($authorize->orderId)->toBe(123);
        expect($authorize->paymentType)->toBe('fortis');
    });

    it('creates failed payment authorize', function () {
        $authorize = new PaymentAuthorize(
            success: false,
            message: 'Payment declined',
            orderId: null,
            paymentType: FortisPaymentType::PAYMENT_TYPE,
        );

        expect($authorize->success)->toBeFalse();
        expect($authorize->message)->toBe('Payment declined');
        expect($authorize->orderId)->toBeNull();
    });
});

describe('PaymentCapture Creation', function () {
    it('creates successful payment capture', function () {
        $capture = new PaymentCapture(
            success: true,
            message: 'Payment captured'
        );

        expect($capture->success)->toBeTrue();
        expect($capture->message)->toBe('Payment captured');
    });

    it('creates failed payment capture', function () {
        $capture = new PaymentCapture(
            success: false,
            message: 'Capture failed'
        );

        expect($capture->success)->toBeFalse();
        expect($capture->message)->toBe('Capture failed');
    });
});

describe('PaymentRefund Creation', function () {
    it('creates successful payment refund', function () {
        $refund = new PaymentRefund(success: true);

        expect($refund->success)->toBeTrue();
    });

    it('creates failed payment refund', function () {
        $refund = new PaymentRefund(
            success: false,
            message: 'Refund failed'
        );

        expect($refund->success)->toBeFalse();
        expect($refund->message)->toBe('Refund failed');
    });
});

describe('StatusCode Integration', function () {
    it('works with status code enum', function () {
        expect(StatusCode::isSuccessful(101))->toBeTrue(); // Approved
        expect(StatusCode::isSuccessful(102))->toBeTrue(); // Auth Only
        expect(StatusCode::isSuccessful(301))->toBeFalse(); // Declined
    });

    it('identifies captured transactions', function () {
        expect(StatusCode::isCaptured(101))->toBeTrue(); // Approved
        expect(StatusCode::isCaptured(102))->toBeFalse(); // Auth Only
    });
});

describe('LunarFortis Facade Integration', function () {
    it('can access facade methods', function () {
        expect(method_exists(\Hyrograsper\LunarFortis\LunarFortis::class, 'getClientTokenForSaleAmount'))->toBeTrue();
        expect(method_exists(\Hyrograsper\LunarFortis\LunarFortis::class, 'completeAuthorizedTransaction'))->toBeTrue();
        expect(method_exists(\Hyrograsper\LunarFortis\LunarFortis::class, 'refund'))->toBeTrue();
    });
});
