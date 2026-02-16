<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use Exception;
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
    protected string $policy;

    public const string PAYMENT_TYPE = 'fortis-terminal';

    public function __construct()
    {
        $this->policy = config('lunar-fortis.terminal_policy', config('lunar-fortis.policy', 'automatic'));
    }

    public function authorize(): ?PaymentAuthorize
    {
        $this->order = $this->order ?: ($this->cart->draftOrder ?: $this->cart->completedOrder);

        if (! $this->order) {
            try {
                $this->order = $this->cart->createOrder();
            } catch (DisallowMultipleCartOrdersException|CartException $exception) {
                return $this->dispatchAndReturn(false, $exception->getMessage(), $this->order?->id);
            }
        } else {
            $this->order = $this->cart->createOrder(orderIdToUpdate: $this->order->id);
        }

        if ($this->order->placed_at) {
            return $this->dispatchAndReturn(false, 'This order has already been placed');
        }

        try {
            $transaction = $this->storeTerminalTransaction(
                LunarFortis::getTransaction($this->data['fortis_transaction_id'])
            );
        } catch (Exception $exception) {
            Log::error("LunarFortis: Failed to fetch fortis transaction and store data: {$exception->getMessage()}", [
                'error' => $exception->getMessage(),
                'transaction_id' => $this->data['fortis_transaction_id'] ?? null,
                'order_id' => $this->order->id ?? null,
                'error_trace' => $exception->getTraceAsString(),
            ]);

            return $this->dispatchAndReturn(
                false,
                "Failed to fetch fortis transaction and store data: {$exception->getMessage()}"
            );
        }

        if (! $transaction->success) {
            return $this->dispatchAndReturn(false, $transaction->meta['errors'] ?? 'Unknown Error.');
        }

        if ($transaction->type === 'capture') {
            $this->markOrderPlaced();

            return $this->dispatchAndReturn(true, 'Terminal payment completed successfully');
        }

        $this->order->status = config('lunar-fortis.status_mapping.payment-authorized', 'payment-authorized');
        $this->order->save();

        if ($this->policy === 'automatic') {
            $captureResponse = $this->capture($transaction, $transaction->amount->value);

            if (! $captureResponse->success) {
                return $this->dispatchAndReturn(false, $captureResponse->message);
            }

            $this->markOrderPlaced();

            return $this->dispatchAndReturn(true, 'Terminal payment captured successfully');
        }

        return $this->dispatchAndReturn(true, 'Terminal payment authorized');
    }

    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        try {
            $response = LunarFortis::completeAuthorizedTransaction($transaction, $amount);

            $captureTransaction = $this->storeResponseTransaction($response, $transaction);

            if (! $captureTransaction->success) {
                return new PaymentCapture(
                    success: false,
                    message: $captureTransaction->notes ?? 'Capture failed'
                );
            }

            return new PaymentCapture(
                success: true,
                message: 'Terminal payment captured successfully'
            );
        } catch (Exception $exception) {
            Log::error('LunarFortis: Terminal capture failed', [
                'error' => $exception->getMessage(),
                'transaction_id' => $transaction->id ?? null,
                'order_id' => $this->order->id ?? null,
                'amount' => $amount,
                'error_trace' => $exception->getTraceAsString(),
            ]);

            return new PaymentCapture(
                success: false,
                message: $exception->getMessage()
            );
        }
    }

    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        try {
            $result = LunarFortis::refund($transaction, $amount);
        } catch (Exception $exception) {
            Log::error("LunarFortis: Unable to process terminal refund: {$exception->getMessage()}", [
                'error' => $exception->getMessage(),
                'transaction_id' => $transaction->id ?? null,
                'order_id' => $this->order->id ?? null,
                'amount' => $amount,
                'error_trace' => $exception->getTraceAsString(),
            ]);

            return new PaymentRefund(
                success: false,
                message: $exception->getMessage()
            );
        }

        $data = $result['data'] ?? [];
        $statusCode = $data['status_code'] ?? null;
        $reasonCodeId = $data['reason_code_id'] ?? null;
        $isSuccessful = StatusCode::isRefunded($statusCode) && ReasonCode::isApproved($reasonCodeId);

        $this->createRefundTransaction($transaction, $data, $amount, $isSuccessful);

        if (! $isSuccessful) {
            return new PaymentRefund(
                success: false,
                message: $data['reason_code'] ?? 'Refund failed'
            );
        }

        return new PaymentRefund(success: true);
    }

    private function storeTerminalTransaction(array $response): Transaction
    {
        $data = $response['data'] ?? [];

        if (empty($data)) {
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
            $statusCode = $data['status_code'] ?? null;
            $success = StatusCode::isSuccessful($statusCode);
            $meta = $this->buildTerminalMeta($data);

            $transactionType = $this->determineTransactionType($data);

            return Transaction::create([
                'order_id' => $this->order->id,
                'success' => $success,
                'type' => $transactionType,
                'driver' => self::PAYMENT_TYPE,
                'amount' => $data['transaction_amount'] ?? $this->cart->total->value,
                'reference' => $data['id'] ?? now()->timestamp,
                'status' => $this->resolveTransactionStatus($success, $data['status_code']),
                'notes' => $success ? null : ($data['verbiage'] ?? $data['reason_code'] ?? null),
                'card_type' => $data['account_type'] ?? 'credit',
                'last_four' => $data['last_four'] ?? '',
                'captured_at' => StatusCode::isCaptured($data['status_code']) ? now() : null,
                'meta' => $meta,
            ]);

        } catch (Exception $exception) {
            Log::error('LunarFortis: Terminal payment processing failed', [
                'transaction_id' => $data['id'] ?? null,
                'order_id' => $this->order->id,
                'amount' => $this->cart->total->value,
                'error' => $exception->getMessage(),
            ]);

            return Transaction::create([
                'order_id' => $this->order->id,
                'success' => false,
                'type' => 'capture',
                'driver' => self::PAYMENT_TYPE,
                'amount' => $this->cart->total->value,
                'reference' => $data['id'] ?? now()->timestamp,
                'status' => 'failed',
                'notes' => $exception->getMessage(),
                'card_type' => 'unknown',
                'last_four' => '',
                'meta' => [
                    'terminal_id' => $data['terminal_id'] ?? null,
                    'errors' => $exception->getMessage(),
                ],
            ]);
        }
    }

    private function buildTerminalMeta(array $data): array
    {
        $meta = [
            'status_code' => $data['status_code'] ?? null,
            'reason_code' => $data['reason_code'] ?? null,
            'fortis_transaction_id' => $data['id'] ?? null,
            'description' => $data['description'] ?? null,
            'verbiage' => $data['verbiage'] ?? null,
        ];

        if (isset($data['terminal_id'])) {
            $meta['terminal_id'] = $data['terminal_id'];

            if ($terminal = Terminal::where('fortis_id', $data['terminal_id'])->first()) {
                $meta['terminal_title'] = $terminal->title;
                $meta['terminal_serial'] = $terminal->serial_number;
            }
        }

        $optionalFields = ['tip_amount', 'clerk_number'];

        foreach ($optionalFields as $field) {
            if (isset($data[$field])) {
                $meta[$field] = $data[$field];
            }
        }

        return $meta;
    }

    private function getTerminalIdFromMeta(TransactionContract $transaction): ?string
    {
        $meta = is_array($transaction->meta) ? $transaction->meta : [];

        return $meta['terminal_id'] ?? null;
    }

    private function determineTransactionType(array $data): string
    {
        if (isset($data['@action'])) {
            return $data['@action'] === 'sale' ? 'capture' : 'intent';
        }

        if (StatusCode::isCaptured($data['status_code'] ?? null)) {
            return 'capture';
        }

        return 'intent';
    }

    private function storeResponseTransaction(array $response, TransactionContract $parentTransaction): Transaction
    {
        $data = $response['data'] ?? [];

        if (empty($data)) {
            return Transaction::create([
                'parent_transaction_id' => $parentTransaction->id,
                'order_id' => $parentTransaction->order->id,
                'success' => false,
                'type' => 'capture',
                'driver' => self::PAYMENT_TYPE,
                'amount' => 0,
                'reference' => now()->timestamp,
                'status' => 'failed',
                'notes' => 'No response data received',
                'card_type' => $parentTransaction->card_type ?? 'N/A',
                'last_four' => $parentTransaction->last_four ?? '',
                'meta' => [],
            ]);
        }

        $statusCode = $data['status_code'] ?? null;
        $success = StatusCode::isSuccessful($statusCode);
        $meta = $this->buildTerminalMeta($data);

        return Transaction::create([
            'parent_transaction_id' => $parentTransaction->id,
            'order_id' => $parentTransaction->order->id,
            'success' => $success,
            'type' => 'capture',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $data['transaction_amount'] ?? 0,
            'reference' => $data['id'] ?? now()->timestamp,
            'status' => $this->resolveTransactionStatus($success, $statusCode),
            'notes' => $success ? null : ($data['verbiage'] ?? $data['reason_code'] ?? null),
            'card_type' => $data['account_type'] ?? $parentTransaction->card_type ?? 'N/A',
            'last_four' => $data['last_four'] ?? $parentTransaction->last_four ?? '',
            'captured_at' => StatusCode::isCaptured($statusCode) ? now() : null,
            'meta' => $meta,
        ]);
    }

    private function resolveTransactionStatus(bool $success, ?int $statusCode): string
    {
        if (! $success) {
            return 'declined';
        }

        return StatusCode::isCaptured($statusCode) ? 'approved' : 'authorized';
    }

    private function dispatchAndReturn(bool $success, string $message, ?int $orderId = null): PaymentAuthorize
    {
        $paymentAuthorize = new PaymentAuthorize(
            success: $success,
            message: $message,
            orderId: $orderId ?? $this->order?->id,
            paymentType: self::PAYMENT_TYPE,
        );

        PaymentAttemptEvent::dispatch($paymentAuthorize);

        return $paymentAuthorize;
    }

    private function markOrderPlaced(): void
    {
        $this->order->placed_at = now();
        $this->order->status = config('lunar-fortis.status_mapping.payment-received', 'payment-received');
        $this->order->save();
    }

    private function createRefundTransaction(
        TransactionContract $transaction,
        array $data,
        int $amount,
        bool $success
    ): Transaction {
        return Transaction::create([
            'parent_transaction_id' => $transaction->id,
            'order_id' => $transaction->order->id,
            'success' => $success,
            'type' => 'refund',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $amount,
            'reference' => $data['id'] ?? now()->timestamp,
            'status' => $success ? 'refunded' : 'declined',
            'notes' => $success ? null : ($data['reason_code'] ?? null),
            'card_type' => $transaction->card_type ?? '',
            'last_four' => $transaction->last_four ?? '',
            'meta' => [
                'status_code' => $data['status_code'] ?? null,
                'reason_code' => $data['reason_code'] ?? null,
                'description' => $data['description'] ?? null,
                'verbiage' => $data['verbiage'] ?? null,
                'terminal_id' => $this->getTerminalIdFromMeta($transaction),
            ],
        ]);
    }
}
