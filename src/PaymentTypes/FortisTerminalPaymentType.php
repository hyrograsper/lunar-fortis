<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use Exception;
use Hyrograsper\LunarFortis\Enums\ReasonCode;
use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Events\PaymentAttemptEvent;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Exceptions\DisallowMultipleCartOrdersException;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\Models\Order;
use Lunar\Models\Transaction;
use Lunar\PaymentTypes\AbstractPayment;

class FortisTerminalPaymentType extends AbstractPayment
{
    use DescribesDeclines;
    use NormalizesIntegerFields;
    use RejectsRefundedTransactions;
    use ResetsAllowPartialPayment;

    protected string $policy;

    public const string PAYMENT_TYPE = 'fortis-terminal';

    private const int TERMINAL_LOCK_SECONDS = 60;

    public function __construct()
    {
        $this->policy = config('lunar-fortis.terminal_policy', config('lunar-fortis.policy', 'automatic'));
    }

    public function authorize(): ?PaymentAuthorize
    {
        try {
            return $this->authorizeCart();
        } finally {
            $this->resetPaymentState();
        }
    }

    private function authorizeCart(): PaymentAuthorize
    {
        if (! $this->cart) {
            return $this->dispatchAndReturn(false, 'No cart to authorize.');
        }

        $transactionId = $this->data['fortis_transaction_id'] ?? null;

        if (! is_string($transactionId) || $transactionId === '') {
            return $this->dispatchAndReturn(false, 'Missing Fortis transaction id.');
        }

        if (! preg_match(FortisPaymentType::TRANSACTION_ID_PATTERN, $transactionId)) {
            return $this->dispatchAndReturn(false, 'Invalid Fortis transaction id.');
        }

        $this->order = $this->order ?: ($this->cart->draftOrder()->first() ?: $this->cart->completedOrder()->first());

        if ($this->order instanceof Order && $this->hasAnotherAuthorizedPayment($this->order, $transactionId)) {
            return $this->dispatchAndReturn(false, 'This order already has an authorized payment.', $this->order->id);
        }

        if ($this->order instanceof Order && $this->order->placed_at) {
            return $this->dispatchAndReturn(false, 'This order has already been placed', $this->order->id);
        }

        if (! $this->order) {
            try {
                $this->order = $this->cart->createOrder();
            } catch (DisallowMultipleCartOrdersException|CartException $exception) {
                return $this->dispatchAndReturn(false, $exception->getMessage());
            }
        } else {
            $this->order = $this->cart->createOrder(orderIdToUpdate: $this->order->id);
        }

        $lock = Cache::lock("lunar-fortis:terminal-transaction:{$transactionId}", self::TERMINAL_LOCK_SECONDS);

        if (! $lock->get()) {
            return $this->dispatchAndReturn(false, 'This payment is already being processed.');
        }

        try {
            return $this->authorizeTerminalTransaction($this->order, $transactionId);
        } finally {
            $lock->release();
        }
    }

    private function authorizeTerminalTransaction(Order $order, string $transactionId): PaymentAuthorize
    {
        if ($this->recordedAgainstAnotherOrder($order, $transactionId)) {
            Log::warning('LunarFortis: Rejected a terminal transaction already recorded against another order', [
                'transaction_id' => $transactionId,
                'order_id' => $order->id,
            ]);

            return $this->dispatchAndReturn(false, 'This payment has already been used.');
        }

        $transaction = $this->recordedTransaction($order, $transactionId);

        if (! $transaction) {
            try {
                $data = $this->normalizeIntegerFields(
                    $this->withoutBlankTip(LunarFortis::getTransaction($transactionId, $this->verificationExpand())['data'] ?? []),
                    ['status_code', 'reason_code_id', 'transaction_amount', 'tip_amount'],
                );
            } catch (Exception $exception) {
                Log::error("LunarFortis: Failed to fetch fortis transaction: {$exception->getMessage()}", [
                    'error' => $exception->getMessage(),
                    'transaction_id' => $transactionId,
                    'order_id' => $order->id,
                ]);

                return $this->dispatchAndReturn(false, 'Unable to verify the payment.');
            }

            $rejection = $this->terminalTransactionRejection($transactionId, $data);

            if ($rejection !== null) {
                Log::warning("LunarFortis: Rejected terminal transaction: {$rejection}", [
                    'transaction_id' => $transactionId,
                    'order_id' => $order->id,
                ]);

                return $this->dispatchAndReturn(false, $rejection);
            }

            $transaction = $this->storeTerminalTransaction($data);
        }

        if (! $transaction->success) {
            return $this->dispatchAndReturn(false, $transaction->meta['errors'] ?? 'The payment was declined.');
        }

        if ($transaction->type === 'capture') {
            $this->markOrderPlaced();

            return $this->dispatchAndReturn(true, 'Terminal payment completed successfully');
        }

        $order->status = config('lunar-fortis.status_mapping.payment-authorized', 'payment-authorized');
        $order->save();

        if ($this->policy === 'automatic') {
            if (! $this->hasSuccessfulCapture($transaction)) {
                $captureResponse = $this->capture($transaction, $transaction->amount->value);

                if (! $captureResponse->success) {
                    return $this->dispatchAndReturn(false, $captureResponse->message);
                }
            }

            $this->markOrderPlaced();

            return $this->dispatchAndReturn(true, 'Terminal payment captured successfully');
        }

        return $this->dispatchAndReturn(true, 'Terminal payment authorized');
    }

    private function hasAnotherAuthorizedPayment(Order $order, string $transactionId): bool
    {
        return Transaction::query()
            ->where('order_id', $order->id)
            ->whereIn('type', ['intent', 'capture'])
            ->where('success', true)
            ->where('reference', '!=', $transactionId)
            ->exists();
    }

    private function recordedAgainstAnotherOrder(Order $order, string $transactionId): bool
    {
        return Transaction::query()
            ->whereIn('driver', [FortisPaymentType::PAYMENT_TYPE, self::PAYMENT_TYPE])
            ->where('reference', $transactionId)
            ->where('order_id', '!=', $order->id)
            ->exists();
    }

    private function recordedTransaction(Order $order, string $transactionId): ?Transaction
    {
        return Transaction::query()
            ->whereIn('driver', [FortisPaymentType::PAYMENT_TYPE, self::PAYMENT_TYPE])
            ->where('reference', $transactionId)
            ->where('order_id', $order->id)
            ->whereNull('parent_transaction_id')
            ->oldest('id')
            ->first();
    }

    private function hasSuccessfulCapture(Transaction $transaction): bool
    {
        return Transaction::query()
            ->where('parent_transaction_id', $transaction->id)
            ->where('type', 'capture')
            ->where('success', true)
            ->exists();
    }

    private function withoutBlankTip(array $data): array
    {
        if (($data['tip_amount'] ?? null) === null || $data['tip_amount'] === '') {
            unset($data['tip_amount']);
        }

        return $data;
    }

    private function terminalTransactionRejection(string $transactionId, array $data): ?string
    {
        if (($data['id'] ?? null) !== $transactionId) {
            return 'The payment could not be verified.';
        }

        $terminalId = $data['terminal_id'] ?? null;

        if (! is_string($terminalId) || $terminalId === '') {
            return 'The payment was not taken on a terminal.';
        }

        $statusCode = $data['status_code'] ?? null;

        if (! is_int($statusCode)) {
            return 'The payment could not be verified.';
        }

        if (StatusCode::isUnsuccessful($statusCode)) {
            return null;
        }

        if ($statusCode !== StatusCode::AuthOnly->value && ! StatusCode::isCaptured($statusCode)) {
            return 'The payment is not in a state that can be accepted.';
        }

        if ($this->hasRefund($data)) {
            return 'This payment has been refunded.';
        }

        $amount = $data['transaction_amount'] ?? null;

        if (! is_int($amount)) {
            return 'The payment amount could not be verified.';
        }

        if (! $this->cart instanceof Cart) {
            return 'The payment amount could not be verified.';
        }

        $tip = array_key_exists('tip_amount', $data) ? $data['tip_amount'] : 0;

        if (! is_int($tip)) {
            return 'The payment amount could not be verified.';
        }

        if ($tip < 0) {
            return 'The payment amount could not be verified.';
        }

        if ($amount - $tip !== $this->cart->total->value) {
            return 'The payment amount does not match the order total.';
        }

        return null;
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
                'order_id' => $transaction->order_id ?? null,
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
                'order_id' => $transaction->order_id ?? null,
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

    private function resetPaymentState(): void
    {
        $this->cart = null;
        $this->order = null;
        $this->data = [];
        $this->resetAllowPartialPayment();
    }

    private function storeTerminalTransaction(array $data): Transaction
    {
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
                'status' => $this->resolveTransactionStatus($success, $statusCode),
                'notes' => $success ? null : $meta['errors'],
                'card_type' => $data['account_type'] ?? 'credit',
                'last_four' => $data['last_four'] ?? '',
                'captured_at' => StatusCode::isCaptured($statusCode) ? now() : null,
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
            'reason_code_id' => $data['reason_code_id'] ?? null,
            'fortis_transaction_id' => $data['id'] ?? null,
            'description' => $data['description'] ?? null,
            'verbiage' => $data['verbiage'] ?? null,
        ];

        if (StatusCode::isUnsuccessful($data['status_code'] ?? null)) {
            $meta['errors'] = $this->declineMessage($data);
        }

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
        $terminalId = $transaction->meta['terminal_id'] ?? null;

        return is_string($terminalId) ? $terminalId : null;
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
