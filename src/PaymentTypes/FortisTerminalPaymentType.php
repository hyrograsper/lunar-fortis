<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use Exception;
use FortisAPILib\Exceptions\ApiException;
use FortisAPILib\Models\ResponseTransaction;
use Hyrograsper\LunarFortis\Enums\ReasonCode;
use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
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
    // Terminal payments are always automatic capture (in-person transactions)
    protected string $policy = 'automatic';

    public const string PAYMENT_TYPE = 'fortis-terminal';

    public function __construct()
    {
        //
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

        try {
            $transaction = $this->storeTerminalTransaction(
                LunarFortis::getTransaction($this->data['fortis_transaction_id'])
            );
        } catch (ApiException|Exception $e) {
            Log::error('LunarFortis: Failed to fetch fortis transaction and store data. Error: '.$e->getMessage());
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
            $result = LunarFortis::refund($transaction, $amount);
        } catch (ApiException $exception) {
            Log::error('LunarFortis: Unable to process terminal refund: '.$exception->getMessage().' '.print_r($exception->getHttpResponse(), true));

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

    private function storeTerminalTransaction(ResponseTransaction $response): Transaction
    {
        $data = $response->getData();

        if (! $data) {
            return Transaction::create([
                'order_id' => $this->order->id,
                'success' => false,
                'type' => 'capture',
                'driver' => self::PAYMENT_TYPE,
                'amount' => $this->cart->total->value,
                'reference' => now()->timestamp,
                'status' => 'failed',
                'notes' => 'No response data received',
                'card_type' => 'unknown',
                'last_four' => '',
                'meta' => [],
            ]);
        }

        try {
            $success = StatusCode::isSuccessful($data->getStatusCode());

            // Build transaction meta data from ResponseTransaction
            $meta = [
                'status_code' => $data->getStatusCode(),
                'reason_code' => $data->getReasonCode(),
                'fortis_transaction_id' => $data->getId(),
                'description' => $data->getDescription(),
                'verbiage' => $data->getVerbiage(),
            ];

            // Add terminal ID if available
            if ($data->getTerminalId()) {
                $meta['terminal_id'] = $data->getTerminalId();

                // Try to get terminal details from the database
                if ($terminal = Terminal::where('fortis_id', $data->getTerminalId())->first()) {
                    $meta['terminal_title'] = $terminal->title;
                    $meta['terminal_serial'] = $terminal->serial_number;
                }
            }

            // Add additional transaction details if available
            if ($data->getTipAmount()) {
                $meta['tip_amount'] = $data->getTipAmount();
            }

            if (method_exists($data, 'getClerkNumber') && $data->getClerkNumber()) {
                $meta['clerk_number'] = $data->getClerkNumber();
            }

            // Determine card details from response data
            $cardType = $data->getAccountType() ?? 'credit';
            $lastFour = $data->getLastFour() ?? '';

            return Transaction::create([
                'order_id' => $this->order->id,
                'success' => $success,
                'type' => 'capture', // Terminal payments are always captures
                'driver' => self::PAYMENT_TYPE,
                'amount' => $data->getTransactionAmount() ?? $this->cart->total->value,
                'reference' => $data->getId() ?? now()->timestamp,
                'status' => $success ? 'approved' : 'declined',
                'notes' => $success ? '' : ($data->getVerbiage() ?? $data->getReasonCode()),
                'card_type' => $cardType,
                'last_four' => $lastFour,
                'captured_at' => $success ? now() : null,
                'meta' => $meta,
            ]);

        } catch (Exception $e) {
            Log::error('LunarFortis: LunarFortis: Terminal payment processing failed', [
                'transaction_id' => $data?->getId(),
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
                'reference' => $data?->getId() ?? now()->timestamp,
                'status' => 'failed',
                'notes' => $e->getMessage(),
                'card_type' => 'unknown',
                'last_four' => '',
                'meta' => [
                    'terminal_id' => $data?->getTerminalId(),
                    'errors' => $e->getMessage(),
                ],
            ]);
        }
    }

    private function getTerminalIdFromMeta(TransactionContract $transaction): ?string
    {
        $meta = is_array($transaction->meta) ? $transaction->meta : [];

        return $meta['terminal_id'] ?? null;
    }
}
