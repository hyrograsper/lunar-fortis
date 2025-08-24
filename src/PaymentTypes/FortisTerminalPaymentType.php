<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use Exception;
use FortisAPILib\Exceptions\ApiException;
use Hyrograsper\LunarFortis\Enums\ReasonCode;
use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Support\Facades\Log;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;

class FortisTerminalPaymentType extends AbstractPayment
{
    protected string $policy = 'automatic';

    public const string PAYMENT_TYPE = 'fortis-terminal';

    public function __construct(protected LunarFortis $fortis)
    {
        // Terminal payments are always automatic capture (in-person transactions)
        $this->policy = 'automatic';
    }

    public function authorize(): ?PaymentAuthorize
    {
        $this->order = $this->order ?: ($this->cart->draftOrder ?: $this->cart->completedOrder);

        if (! $this->order) {
            try {
                $this->order = $this->cart->createOrder();
            } catch (DisallowMultipleCartOrdersException|CartException $e) {
                $failure = new PaymentAuthorize(
                    success: false,
                    message: $e->getMessage(),
                    orderId: $this->order?->id,
                    paymentType: self::PAYMENT_TYPE,
                );
                PaymentAttemptEvent::dispatch($failure);

                return $failure;
            }
        } else {
            $this->order = $this->cart->createOrder(orderIdToUpdate: $this->order->id);
        }

        if ($this->order->placed_at) {
            $failedResponse = new PaymentAuthorize(
                success: false,
                message: 'This order has already been placed',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        // Validate required terminal data
        if (empty($this->data['terminal_id'])) {
            $failedResponse = new PaymentAuthorize(
                success: false,
                message: 'Terminal ID is required for terminal payments',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        // Find and validate terminal
        $terminal = Terminal::where('fortis_id', $this->data['terminal_id'])->first();
        if (! $terminal) {
            $failedResponse = new PaymentAuthorize(
                success: false,
                message: 'Terminal not found or not available',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        if (! $terminal->isReadyForPayments()) {
            $failedResponse = new PaymentAuthorize(
                success: false,
                message: 'Terminal is not ready for payments (inactive)',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        // Process terminal payment
        $transaction = $this->storeTerminalTransaction($this->data, $terminal);

        if (! $transaction->success) {
            $failedResponse = new PaymentAuthorize(
                success: false,
                message: $transaction->notes ?? 'Terminal payment failed',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        // Terminal payments are always automatically captured
        $this->order->placed_at = now();
        $this->order->status = config('lunar-fortis.status_mapping.payment-received', 'payment-received');
        $this->order->save();

        $paymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Terminal payment completed successfully',
            orderId: $this->order->id,
            paymentType: self::PAYMENT_TYPE,
        );

        PaymentAttemptEvent::dispatch($paymentAuthorize);

        return $paymentAuthorize;
    }

    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        // Terminal payments are automatically captured during authorization
        // This method should not normally be called for terminal payments
        return new PaymentCapture(
            success: false,
            message: 'Terminal payments are automatically captured during authorization'
        );
    }

    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        try {
            $result = $this->fortis->refund($transaction, $amount);
        } catch (ApiException $exception) {
            Log::error('Unable to process terminal refund: '.$exception->getMessage().' '.print_r($exception->getHttpResponse(), true));

            return new PaymentRefund(
                success: false,
                message: $exception->getMessage()
            );
        }

        $data = $result->getData();
        if (! StatusCode::isRefunded($data->getStatusCode()) || ! ReasonCode::isApproved($data->getReasonCodeId())) {
            Transaction::create([
                'parent_transaction_id' => $transaction->id,
                'order_id' => $transaction->order->id,
                'success' => false,
                'type' => 'refund',
                'driver' => self::PAYMENT_TYPE,
                'amount' => $amount,
                'reference' => $data->getId() ?? now()->timestamp,
                'status' => 'declined',
                'notes' => $data->getReasonCode(),
                'card_type' => $transaction->card_type ?? '',
                'last_four' => $transaction->last_four ?? '',
                'meta' => [
                    'status_code' => $data->getStatusCode(),
                    'reason_code' => $data->getReasonCode(),
                    'description' => $data->getDescription(),
                    'verbiage' => $data->getVerbiage(),
                    'terminal_id' => $this->getTerminalIdFromMeta($transaction),
                ],
            ]);

            return new PaymentRefund(
                success: false,
                message: $data->getReasonCode()
            );
        }

        Transaction::create([
            'parent_transaction_id' => $transaction->id,
            'order_id' => $transaction->order->id,
            'success' => true,
            'type' => 'refund',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $amount,
            'reference' => $data->getId() ?? now()->timestamp,
            'status' => 'refunded',
            'card_type' => $transaction->card_type ?? '',
            'last_four' => $transaction->last_four ?? '',
            'meta' => [
                'status_code' => $data->getStatusCode(),
                'reason_code' => $data->getReasonCode(),
                'description' => $data->getDescription(),
                'verbiage' => $data->getVerbiage(),
                'terminal_id' => $this->getTerminalIdFromMeta($transaction),
            ],
        ]);

        return new PaymentRefund(success: true);
    }

    private function storeTerminalTransaction(array $data, Terminal $terminal): Transaction
    {
        try {
            // Prepare transaction options
            $options = array_merge([
                'order_number' => $this->order->reference,
                'customer_id' => $this->order->customer_id,
                'description' => $data['description'] ?? "Order {$this->order->reference}",
            ], $data['options'] ?? []);

            // Process the terminal payment
            $result = $terminal->processPayment(
                amount: $this->cart->total->value,
                options: $options
            );

            // Build transaction meta data
            $meta = [
                'terminal_id' => $terminal->fortis_id,
                'terminal_title' => $terminal->title,
                'terminal_serial' => $terminal->serial_number,
                'async_status_code' => $result['status_code'] ?? null,
                'fortis_transaction_id' => $result['transaction_id'] ?? null,
                'progress' => $result['progress'] ?? 0,
                'completed' => $result['completed'] ?? false,
                'timed_out' => $result['timed_out'] ?? false,
            ];

            if ($result['error']) {
                $meta['errors'] = $result['error'];
            }

            // Add additional transaction details if available
            if (isset($data['tip_amount'])) {
                $meta['tip_amount'] = $data['tip_amount'];
            }

            if (isset($data['clerk_number'])) {
                $meta['clerk_number'] = $data['clerk_number'];
            }

            // Determine card details from terminal capabilities or data
            $cardType = $this->determineCardType($terminal, $data);
            $lastFour = $data['last_four'] ?? '';

            return Transaction::create([
                'order_id' => $this->order->id,
                'success' => $result['success'],
                'type' => 'capture', // Terminal payments are always captures
                'driver' => self::PAYMENT_TYPE,
                'amount' => $this->cart->total->value,
                'reference' => $result['transaction_id'] ?? now()->timestamp,
                'status' => $result['success'] ? 'approved' : 'declined',
                'notes' => $result['success'] ? null : ($result['error'] ?? 'Terminal payment failed'),
                'card_type' => $cardType,
                'last_four' => $lastFour,
                'captured_at' => $result['success'] ? now() : null,
                'meta' => $meta,
            ]);

        } catch (Exception $e) {
            Log::error('Terminal payment processing failed', [
                'terminal_id' => $terminal->fortis_id,
                'order_id' => $this->order->id,
                'amount' => $this->cart->total->value,
                'error' => $e->getMessage(),
            ]);

            return Transaction::create([
                'order_id' => $this->order->id,
                'success' => false,
                'type' => 'capture',
                'driver' => self::PAYMENT_TYPE,
                'amount' => $this->cart->total->value,
                'reference' => now()->timestamp,
                'status' => 'failed',
                'notes' => $e->getMessage(),
                'card_type' => 'unknown',
                'last_four' => '',
                'meta' => [
                    'terminal_id' => $terminal->fortis_id,
                    'terminal_title' => $terminal->title,
                    'errors' => $e->getMessage(),
                ],
            ]);
        }
    }

    private function determineCardType(Terminal $terminal, array $data): string
    {
        // If card type is provided in data, use it
        if (! empty($data['card_type'])) {
            return $data['card_type'];
        }

        // Default to credit card
        return 'credit';
    }

    private function getTerminalIdFromMeta(TransactionContract $transaction): ?string
    {
        $meta = is_array($transaction->meta) ? $transaction->meta : [];

        return $meta['terminal_id'] ?? null;
    }
}
