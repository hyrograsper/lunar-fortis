<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use Hyrograsper\LunarFortis\Enums\AvsResponseCode;
use Hyrograsper\LunarFortis\Enums\CvvResponseCode;
use Hyrograsper\LunarFortis\Enums\ReasonCode;
use Hyrograsper\LunarFortis\Enums\StatusCode;
use Hyrograsper\LunarFortis\LunarFortis;
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

class FortisPaymentType extends AbstractPayment
{
    protected string $policy;

    public const string PAYMENT_TYPE = 'fortis';

    public function __construct(protected LunarFortis $fortis)
    {
        $this->policy = config('lunar-fortis.policy', 'automatic');
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
            // Order has already been placed - prevent duplicate processing
            $failedResponse = new PaymentAuthorize(
                success: false,
                message: 'This order has already been placed',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        $transaction = $this->storeElementsTransaction($this->data);

        if (! $transaction->success) {
            $failedResponse = new PaymentAuthorize(
                success: false,
                message: $transaction->meta['errors'] ?? 'Unknown Error.',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($failedResponse);

            return $failedResponse;
        }

        if ($transaction->type == 'capture') {
            $this->order->placed_at = now();
            $this->order->status = config('lunar-fortis.status_mapping.payment-received', 'payment-received');
            $this->order->save();

            $paymentAuthorize = new PaymentAuthorize(
                success: true,
                message: 'Payment Captured',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($paymentAuthorize);

            return $paymentAuthorize;
        }

        $this->order->status = config('lunar-fortis.status_mapping.payment-authorized', 'payment-authorized');
        $this->order->save();

        if ($this->policy == 'automatic') {
            $captureResponse = $this->capture($transaction, $transaction->amount->value);

            if (! $captureResponse->success) {
                $paymentAuthorize = new PaymentAuthorize(
                    success: false,
                    message: $captureResponse->message,
                    orderId: $this->order->id,
                    paymentType: self::PAYMENT_TYPE,
                );

                PaymentAttemptEvent::dispatch($paymentAuthorize);

                return $paymentAuthorize;
            }

            $this->order->placed_at = now();
            $this->order->status = config('lunar-fortis.status_mapping.payment-received', 'payment-received');
            $this->order->save();

            $paymentAuthorize = new PaymentAuthorize(
                success: true,
                message: 'Payment Captured',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($paymentAuthorize);

            return $paymentAuthorize;
        }

        $paymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Payment Authorized',
            orderId: $this->order->id,
            paymentType: self::PAYMENT_TYPE,
        );

        PaymentAttemptEvent::dispatch($paymentAuthorize);

        return $paymentAuthorize;
    }

    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        try {
            $response = $this->fortis->completeAuthorizedTransaction($transaction, $amount);

            $captureTransaction = $this->storeResponseTransaction($response, $transaction);

            if (! $captureTransaction->success) {
                return new PaymentCapture(
                    success: false,
                    message: $captureTransaction->notes ?? 'Capture failed'
                );
            }

            return new PaymentCapture(
                success: true,
                message: 'Payment captured successfully'
            );
        } catch (\Exception $e) {
            Log::error('LunarFortis: Capture failed: '.$e->getMessage());

            return new PaymentCapture(
                success: false,
                message: $e->getMessage()
            );
        }
    }

    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        try {
            $result = $this->fortis->refund($transaction, $amount);
        } catch (\Exception $exception) {
            Log::error('LunarFortis: Unable to process refund: '.$exception->getMessage());

            return new PaymentRefund(
                success: false,
                message: $exception->getMessage()
            );
        }

        $resultData = $result['data'] ?? [];
        $statusCode = $resultData['status_code'] ?? null;
        $reasonCodeId = $resultData['reason_code_id'] ?? null;

        if (! StatusCode::isRefunded($statusCode) || ! ReasonCode::isApproved($reasonCodeId)) {
            Transaction::create([
                'parent_transaction_id' => $transaction->id,
                'order_id' => $transaction->order->id,
                'success' => false,
                'type' => 'refund',
                'driver' => self::PAYMENT_TYPE,
                'amount' => $amount,
                'reference' => $resultData['id'] ?? now()->timestamp,
                'status' => 'declined',
                'notes' => $resultData['reason_code'] ?? null,
                'card_type' => $transaction->card_type ?? '',
                'last_four' => $transaction->last_four ?? '',
                'meta' => [
                    'status_code' => $resultData['status_code'] ?? null,
                    'reason_code' => $resultData['reason_code'] ?? null,
                    'description' => $resultData['description'] ?? null,
                    'verbiage' => $resultData['verbiage'] ?? null,
                ],
            ]);

            return new PaymentRefund(
                success: false,
                message: $resultData['reason_code'] ?? 'Refund failed'
            );
        }

        Transaction::create([
            'parent_transaction_id' => $transaction->id,
            'order_id' => $transaction->order->id,
            'success' => true,
            'type' => 'refund',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $amount,
            'reference' => $resultData['id'] ?? now()->timestamp,
            'status' => 'refunded',
            'card_type' => $transaction->card_type ?? '',
            'last_four' => $transaction->last_four ?? '',
            'meta' => [
                'status_code' => $resultData['status_code'] ?? null,
                'reason_code' => $resultData['reason_code'] ?? null,
                'description' => $resultData['description'] ?? null,
                'verbiage' => $resultData['verbiage'] ?? null,
            ],
        ]);

        return new PaymentRefund(
            success: true,
        );
    }

    private function storeElementsTransaction(array $data): Transaction
    {
        // Determine success based on status_code in data
        $success = StatusCode::isSuccessful($data['status_code']);

        $meta = $this->buildElementsMetaArray($data);

        return Transaction::create([
            'order_id' => $this->order->id,
            'success' => $success,
            'type' => $data['@action'] == 'sale' ? 'capture' : 'intent',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $data['transaction_amount'] ?? $this->cart->total->value,
            'reference' => $data['id'] ?? now()->timestamp,
            'status' => $success
                ? (StatusCode::isCaptured($data['status_code'])
                    ? 'approved'
                    : 'authorized'
                )
                : 'declined',
            'notes' => $success ? null : ($meta['errors'] ?? null),
            'card_type' => $data['account_type'] ?? 'N/A',
            'last_four' => $data['last_four'] ?? '',
            'captured_at' => StatusCode::isCaptured($data['status_code']) ? now() : null,
            'meta' => $meta,
        ]);
    }

    /**
     * Build the meta array for transaction data.
     * Only includes keys when their values are set (not null).
     *
     * @param  array  $data  The transaction data
     * @return array The meta array with only set values
     */
    private function buildElementsMetaArray(array $data): array
    {
        $meta = [];

        $errors = null;

        // AVS Check
        if (isset($data['avs'])) {
            $avsCode = AvsResponseCode::fromCode($data['avs']);
            if ($avsCode && $avsCode != AvsResponseCode::GOOD) {
                $errors = 'AVS Failed: '.$avsCode->value;
            } elseif (! $avsCode) {
                $errors = "AVS Failed: Unknown code ({$data['avs']})";
            }
        }

        // CVV Check
        if (isset($data['cvv_response'])) {
            $cvvCode = CvvResponseCode::fromCode($data['cvv_response']);
            if ($cvvCode && $cvvCode == CvvResponseCode::N) { // Only 'N' is typically a hard failure for CVV
                if ($errors) {
                    $errors .= '. ';
                }
                $errors .= 'CVV Failed: '.$cvvCode->value;
            } elseif ($cvvCode === null && $data['cvv_response'] !== null) { // If tryFrom returns null but there was a value
                if ($errors) {
                    $errors .= '. ';
                }
                $errors .= "CVV Info: Unknown code ({$data['cvv_response']})";
            }
        }

        if (! $errors && StatusCode::isUnsuccessful($data['status_code'])) {
            $errors = ReasonCode::fromCode((int) $data['reason_code_id']);

            if (isset($data['verbiage'])) {
                $errors .= '. '.$data['verbiage'];
            }
        }

        if ($errors) {
            $meta['errors'] = $errors;
        }

        if (isset($data['reason_code_id'])) {
            $meta['reason_code_message'] = ReasonCode::fromCode((int) $data['reason_code_id']);
        }

        // List of potential meta fields
        $metaFields = [
            '@action',
            'status_code',
            'reason_code_id',
            'auth_code',
            'avs',
            'avs_enhanced',
            'cvv_response',
            'auth_amount',
            'first_six',
            'account_holder_name',
            'payment_method',
            'billing_zip',
            'wallet_type',
            'par',
            'entry_mode_id',
            'customer_ip',
            'transaction_batch_id',
        ];

        // Add fields to meta array only if they exist and are not null
        foreach ($metaFields as $field) {
            if (isset($data[$field])) {
                $meta[$field] = $data[$field];
            }
        }

        return $meta;
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
        $meta = $this->buildResponseTransactionMetaArray($data);

        return Transaction::create([
            'parent_transaction_id' => $parentTransaction->id,
            'order_id' => $parentTransaction->order->id,
            'success' => $success,
            'type' => 'capture',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $data['transaction_amount'] ?? 0,
            'reference' => $data['id'] ?? now()->timestamp,
            'status' => $success
                ? (StatusCode::isCaptured($statusCode)
                    ? 'approved'
                    : 'authorized'
                )
                : 'declined',
            'notes' => $success ? null : ($meta['errors'] ?? null),
            'card_type' => $data['account_type'] ?? $parentTransaction->card_type ?? 'N/A',
            'last_four' => $data['last_four'] ?? $parentTransaction->last_four ?? '',
            'captured_at' => StatusCode::isCaptured($statusCode) ? now() : null,
            'meta' => $meta,
        ]);
    }

    private function buildResponseTransactionMetaArray(array $data): array
    {
        $meta = [];

        $errors = null;

        // AVS Check
        if (isset($data['avs'])) {
            $avsCode = AvsResponseCode::fromCode($data['avs']);
            if ($avsCode && $avsCode != AvsResponseCode::GOOD) {
                $errors = 'AVS Failed: '.$avsCode->value;
            } elseif (! $avsCode) {
                $errors = "AVS Failed: Unknown code ({$data['avs']})";
            }
        }

        // CVV Check
        if (isset($data['cvv_response'])) {
            $cvvCode = CvvResponseCode::fromCode($data['cvv_response']);
            if ($cvvCode && $cvvCode == CvvResponseCode::N) {
                if ($errors) {
                    $errors .= '. ';
                }
                $errors .= 'CVV Failed: '.$cvvCode->value;
            } elseif ($cvvCode === null && $data['cvv_response'] !== null) {
                if ($errors) {
                    $errors .= '. ';
                }
                $errors .= "CVV Info: Unknown code ({$data['cvv_response']})";
            }
        }

        if (! $errors && StatusCode::isUnsuccessful($data['status_code'] ?? null)) {
            $errors = ReasonCode::fromCode((int) ($data['reason_code_id'] ?? 0));

            if (isset($data['verbiage'])) {
                $errors .= '. '.$data['verbiage'];
            }
        }

        if ($errors) {
            $meta['errors'] = $errors;
        }

        if (isset($data['reason_code_id'])) {
            $meta['reason_code_message'] = ReasonCode::fromCode((int) $data['reason_code_id']);
        }

        // List of potential meta fields from response data
        $metaFields = [
            'status_code',
            'reason_code_id',
            'auth_code',
            'avs',
            'avs_enhanced',
            'cvv_response',
            'auth_amount',
            'first_six',
            'account_holder_name',
            'payment_method',
            'par',
            'entry_mode_id',
            'customer_ip',
            'transaction_batch_id',
            'verbiage',
        ];

        // Add fields to meta array only if they exist and are not null
        foreach ($metaFields as $field) {
            if (isset($data[$field])) {
                $meta[$field] = $data[$field];
            }
        }

        return $meta;
    }
}
