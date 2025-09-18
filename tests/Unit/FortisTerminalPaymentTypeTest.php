<?php

use Hyrograsper\LunarFortis\Enums\ReasonCode;
use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Set up test configuration
    Config::set('lunar-fortis.terminal_policy', 'automatic');
    Config::set('lunar-fortis.status_mapping', [
        'payment-received' => 'payment-received',
        'payment-authorized' => 'payment-authorized',
    ]);

    // Create test terminal
    $this->terminal = Terminal::create([
        'fortis_id' => 'terminal-123',
        'title' => 'Test Terminal',
        'serial_number' => 'SN12345',
    ]);

    $this->paymentType = new FortisTerminalPaymentType;
});

describe('FortisTerminalPaymentType Configuration', function () {
    it('sets payment type constant correctly', function () {
        expect(FortisTerminalPaymentType::PAYMENT_TYPE)->toBe('fortis-terminal');
    });

    it('sets automatic policy by default', function () {
        $paymentType = new FortisTerminalPaymentType;

        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('automatic');
    });

    it('uses terminal-specific policy when configured', function () {
        Config::set('lunar-fortis.terminal_policy', 'manual');

        $paymentType = new FortisTerminalPaymentType;
        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('manual');
    });
});

describe('Payment Methods', function () {
    it('has authorize method available', function () {
        expect(method_exists($this->paymentType, 'authorize'))->toBeTrue();

        $reflection = new ReflectionMethod($this->paymentType, 'authorize');
        expect($reflection->getReturnType()->getName())->toBe('Lunar\Base\DataTransferObjects\PaymentAuthorize');
    });

    it('has capture method available', function () {
        expect(method_exists($this->paymentType, 'capture'))->toBeTrue();

        $reflection = new ReflectionMethod($this->paymentType, 'capture');
        expect($reflection->getNumberOfParameters())->toBe(2);
    });

    it('has refund method available', function () {
        expect(method_exists($this->paymentType, 'refund'))->toBeTrue();

        $reflection = new ReflectionMethod($this->paymentType, 'refund');
        expect($reflection->getNumberOfParameters())->toBe(3);
    });
});

describe('Transaction Storage', function () {
    it('has transaction storage methods available', function () {
        // Check that the class has the necessary methods for transaction handling
        $reflection = new ReflectionClass($this->paymentType);
        $privateMethods = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PRIVATE) as $method) {
            $privateMethods[] = $method->getName();
        }

        expect(in_array('storeTerminalTransaction', $privateMethods))->toBeTrue();
        expect(in_array('determineTransactionType', $privateMethods))->toBeTrue();
        expect(in_array('storeResponseTransaction', $privateMethods))->toBeTrue();
    });
});

describe('Status Code Integration', function () {
    it('works with status code enum', function () {
        expect(StatusCode::isSuccessful(101))->toBeTrue(); // Approved
        expect(StatusCode::isSuccessful(102))->toBeTrue(); // Auth Only
        expect(StatusCode::isSuccessful(301))->toBeFalse(); // Declined
    });

    it('works with reason code enum', function () {
        expect(ReasonCode::isApproved(1000))->toBeTrue(); // CC - Approved
        expect(ReasonCode::isApproved(1500))->toBeFalse(); // Generic Decline
    });
});

afterEach(function () {
    Mockery::close();
});
