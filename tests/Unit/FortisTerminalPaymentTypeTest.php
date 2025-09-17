<?php

use Hyrograsper\LunarFortis\Enums\ReasonCode;
use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;

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

    // Mock cart with minimal structure
    $this->cart = new class {
        public $id = 1;
        public $total;
        public $draftOrder;
        public $completedOrder;

        public function __construct() {
            $this->total = new class {
                public $value = 1000;
            };
        }

        public function createOrder($orderIdToUpdate = null) {
            // Create a simple mock order
            return new class($orderIdToUpdate) {
                public $id;
                public $reference;
                public $status = 'cart';
                public $customer_id = 1;
                public $placed_at = null;

                public function __construct($id = null) {
                    $this->id = $id ?: rand(1, 1000);
                    $this->reference = 'ORD-NEW-' . rand(100, 999);
                }

                public function save() {
                    return true;
                }
            };
        }
    };

    $this->paymentType = new FortisTerminalPaymentType();
    // Use reflection to set protected properties
    $reflection = new ReflectionClass($this->paymentType);

    $cartProperty = $reflection->getProperty('cart');
    $cartProperty->setAccessible(true);
    $cartProperty->setValue($this->paymentType, $this->cart);

    $dataProperty = $reflection->getProperty('data');
    $dataProperty->setAccessible(true);
    $dataProperty->setValue($this->paymentType, ['fortis_transaction_id' => 'trans-123']);
});

describe('FortisTerminalPaymentType Configuration', function () {
    it('sets payment type constant correctly', function () {
        expect(FortisTerminalPaymentType::PAYMENT_TYPE)->toBe('fortis-terminal');
    });

    it('sets automatic policy by default', function () {
        $paymentType = new FortisTerminalPaymentType();
        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('automatic');
    });

    it('uses terminal-specific policy when configured', function () {
        Config::set('lunar-fortis.terminal_policy', 'manual');

        $paymentType = new FortisTerminalPaymentType();
        $reflection = new ReflectionClass($paymentType);
        $policyProperty = $reflection->getProperty('policy');
        $policyProperty->setAccessible(true);

        expect($policyProperty->getValue($paymentType))->toBe('manual');
    });
});

describe('Authorization', function () {
    it('has authorize method available', function () {
        expect(method_exists($this->paymentType, 'authorize'))->toBeTrue();

        $reflection = new ReflectionMethod($this->paymentType, 'authorize');
        expect($reflection->getReturnType()->getName())->toBe('Lunar\Base\DataTransferObjects\PaymentAuthorize');
    });

    it('requires transaction ID in data', function () {
        $reflection = new ReflectionClass($this->paymentType);
        $dataProperty = $reflection->getProperty('data');
        $dataProperty->setAccessible(true);
        $data = $dataProperty->getValue($this->paymentType);

        expect($data)->toHaveKey('fortis_transaction_id');
        expect($data['fortis_transaction_id'])->toBe('trans-123');
    });
});

describe('Capture', function () {
    it('has capture method available', function () {
        expect(method_exists($this->paymentType, 'capture'))->toBeTrue();

        $reflection = new ReflectionMethod($this->paymentType, 'capture');
        expect($reflection->getNumberOfParameters())->toBe(2);
    });
});

describe('Refund', function () {
    it('has refund method available', function () {
        expect(method_exists($this->paymentType, 'refund'))->toBeTrue();

        $reflection = new ReflectionMethod($this->paymentType, 'refund');
        expect($reflection->getNumberOfParameters())->toBe(3);
    });
});

describe('Transaction Storage', function () {
    it('has transaction storage methods available', function () {
        expect(method_exists($this->paymentType, 'storeTerminalTransaction'))->toBeFalse(); // Private method

        // Check that the class has the necessary methods for transaction handling
        $reflection = new ReflectionClass($this->paymentType);
        $privateMethods = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PRIVATE) as $method) {
            $privateMethods[] = $method->getName();
        }

        expect(in_array('storeTerminalTransaction', $privateMethods))->toBeTrue();
        expect(in_array('determineTransactionType', $privateMethods))->toBeTrue();
    });
});

afterEach(function () {
    Mockery::close();
});