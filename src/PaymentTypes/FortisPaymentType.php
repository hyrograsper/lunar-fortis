<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use FortisAPILib\Exceptions\ApiException;
use FortisAPILib\Models\ResponseTransaction;
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
            /** @var ResponseTransaction $response */
            $response = $this->fortis->capturePreviousTransaction($transaction, $amount);

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
            Log::error('Capture failed: ' . $e->getMessage());

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
        } catch (ApiException $exception) {
            Log::error('Unable to process refund: '.$exception->getMessage().' '.print_r($exception->getHttpResponse(), true));

            return new PaymentRefund(
                success: false,
                message: $exception->getMessage()
            );
        }

        if (! StatusCode::isRefunded($result->getData()->getStatusCode()) || ! ReasonCode::isApproved($result->getData()->getReasonCodeId())) {
            Transaction::create([
                'parent_transaction_id' => $transaction->id,
                'order_id' => $transaction->order->id,
                'success' => false,
                'type' => 'refund',
                'driver' => self::PAYMENT_TYPE,
                'amount' => $amount,
                'reference' => $result->getData()->getId() ?? now()->timestamp,
                'status' => 'declined',
                'notes' => $result->getData()->getReasonCode(),
                'card_type' => $transaction->card_type ?? '',
                'last_four' => $transaction->last_four ?? '',
                'meta' => [
                    'status_code' => $result->getData()->getStatusCode(),
                    'reason_code' => $result->getData()->getReasonCode(),
                    'description' => $result->getData()->getDescription(),
                    'verbiage' => $result->getData()->getVerbiage(),
                ],
            ]);

            return new PaymentRefund(
                success: false,
                message: $result->getData()->getReasonCode()
            );
        }

        Transaction::create([
            'parent_transaction_id' => $transaction->id,
            'order_id' => $transaction->order->id,
            'success' => true,
            'type' => 'refund',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $amount,
            'reference' => $result->getData()->getId() ?? now()->timestamp,
            'status' => 'refunded',
            'card_type' => $transaction->card_type ?? '',
            'last_four' => $transaction->last_four ?? '',
            'meta' => [
                'status_code' => $result->getData()->getStatusCode(),
                'reason_code' => $result->getData()->getReasonCode(),
                'description' => $result->getData()->getDescription(),
                'verbiage' => $result->getData()->getVerbiage(),
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

    private function storeResponseTransaction(ResponseTransaction $response, TransactionContract $parentTransaction): Transaction
    {
        $data = $response->getData();

        if (! $data) {
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

        $success = StatusCode::isSuccessful($data->getStatusCode());
        $meta = $this->buildResponseTransactionMetaArray($data);

        return Transaction::create([
            'parent_transaction_id' => $parentTransaction->id,
            'order_id' => $parentTransaction->order->id,
            'success' => $success,
            'type' => 'capture',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $data->getTransactionAmount() ?? 0,
            'reference' => $data->getId() ?? now()->timestamp,
            'status' => $success
                ? (StatusCode::isCaptured($data->getStatusCode())
                    ? 'approved'
                    : 'authorized'
                )
                : 'declined',
            'notes' => $success ? null : ($meta['errors'] ?? null),
            'card_type' => $data->getAccountType() ?? $parentTransaction->card_type ?? 'N/A',
            'last_four' => $data->getLastFour() ?? $parentTransaction->last_four ?? '',
            'captured_at' => StatusCode::isCaptured($data->getStatusCode()) ? now() : null,
            'meta' => $meta,
        ]);
    }

    private function buildResponseTransactionMetaArray($data): array
    {
        $meta = [];

        $errors = null;

        // AVS Check
        if ($data->getAvs()) {
            $avsCode = AvsResponseCode::fromCode($data->getAvs());
            if ($avsCode && $avsCode != AvsResponseCode::GOOD) {
                $errors = 'AVS Failed: ' . $avsCode->value;
            } elseif (! $avsCode) {
                $errors = "AVS Failed: Unknown code ({$data->getAvs()})";
            }
        }

        // CVV Check
        if ($data->getCvvResponse()) {
            $cvvCode = CvvResponseCode::fromCode($data->getCvvResponse());
            if ($cvvCode && $cvvCode == CvvResponseCode::N) {
                if ($errors) {
                    $errors .= '. ';
                }
                $errors .= 'CVV Failed: ' . $cvvCode->value;
            } elseif ($cvvCode === null && $data->getCvvResponse() !== null) {
                if ($errors) {
                    $errors .= '. ';
                }
                $errors .= "CVV Info: Unknown code ({$data->getCvvResponse()})";
            }
        }

        if (! $errors && StatusCode::isSuccessful($data->getStatusCode())) {
            $errors = ReasonCode::fromCode((int) $data->getReasonCodeId());

            if ($data->getVerbiage()) {
                $errors .= '. ' . $data->getVerbiage();
            }
        }

        if ($errors) {
            $meta['errors'] = $errors;
        }

        if ($data->getReasonCodeId()) {
            $meta['reason_code_message'] = ReasonCode::fromCode((int) $data->getReasonCodeId());
        }

        // List of potential meta fields from ResponseTransaction data
        $metaFields = [
            'status_code' => 'getStatusCode',
            'reason_code_id' => 'getReasonCodeId',
            'auth_code' => 'getAuthCode',
            'avs' => 'getAvs',
            'avs_enhanced' => 'getAvsEnhanced',
            'cvv_response' => 'getCvvResponse',
            'auth_amount' => 'getAuthAmount',
            'first_six' => 'getFirstSix',
            'account_holder_name' => 'getAccountHolderName',
            'payment_method' => 'getPaymentMethod',
            'par' => 'getPar',
            'entry_mode_id' => 'getEntryModeId',
            'customer_ip' => 'getCustomerIp',
            'transaction_batch_id' => 'getTransactionBatchId',
            'verbiage' => 'getVerbiage',
        ];

        // Add fields to meta array only if they exist and are not null
        foreach ($metaFields as $metaKey => $method) {
            if (method_exists($data, $method)) {
                $value = $data->$method();
                if ($value !== null) {
                    $meta[$metaKey] = $value;
                }
            }
        }

        return $meta;
    }
}
