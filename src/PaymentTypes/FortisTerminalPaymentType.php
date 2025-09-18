<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use Exception;
<<<<<<< HEAD
=======
use FortisAPILib\Exceptions\ApiException;
use FortisAPILib\Models\ResponseTransaction;
>>>>>>> origin/main
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
<<<<<<< HEAD
    protected string $policy;
=======
    // Terminal payments are always automatic capture (in-person transactions)
    protected string $policy = 'automatic';
>>>>>>> origin/main

    public const string PAYMENT_TYPE = 'fortis-terminal';

    public function __construct()
    {
<<<<<<< HEAD
        $this->policy = config('lunar-fortis.terminal_policy', config('lunar-fortis.policy', 'automatic'));
=======
        //
>>>>>>> origin/main
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
<<<<<<< HEAD
        } catch (Exception $e) {
=======
        } catch (ApiException|Exception $e) {
>>>>>>> origin/main
            Log::error('LunarFortis: Failed to fetch fortis transaction and store data. Error: '.$e->getMessage());

            $paymentAuthorize = new PaymentAuthorize(
                success: false,
                message: 'Failed to fetch fortis transaction and store data. Error: '.$e->getMessage(),
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($paymentAuthorize);

            return $paymentAuthorize;
        }

        if (! $transaction->success) {
            $paymentAuthorize = new PaymentAuthorize(
                success: false,
                message: $transaction->meta['errors'] ?? 'Unknown Error.',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($paymentAuthorize);

            return $paymentAuthorize;
        }

<<<<<<< HEAD
        // Check if this was already captured (sale transaction)
        if ($transaction->type == 'capture') {
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

        // Handle authorization-only transaction
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
                message: 'Terminal payment captured successfully',
                orderId: $this->order->id,
                paymentType: self::PAYMENT_TYPE,
            );

            PaymentAttemptEvent::dispatch($paymentAuthorize);

            return $paymentAuthorize;
        }

        $paymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Terminal payment authorized',
=======
        // Terminal payments are always automatically captured
        $this->order->placed_at = now();
        $this->order->status = config('lunar-fortis.status_mapping.payment-received', 'payment-received');
        $this->order->save();

        $paymentAuthorize = new PaymentAuthorize(
            success: true,
            message: 'Terminal payment completed successfully',
>>>>>>> origin/main
            orderId: $this->order->id,
            paymentType: self::PAYMENT_TYPE,
        );

        PaymentAttemptEvent::dispatch($paymentAuthorize);

        return $paymentAuthorize;
    }

    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
<<<<<<< HEAD
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
        } catch (Exception $e) {
            Log::error('LunarFortis: Terminal capture failed: '.$e->getMessage());

            return new PaymentCapture(
                success: false,
                message: $e->getMessage()
            );
        }
=======
        // Terminal payments are automatically captured during authorization
        // This method should not normally be called for terminal payments
        return new PaymentCapture(
            success: false,
            message: 'Terminal payments are automatically captured during authorization'
        );
>>>>>>> origin/main
    }

    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        try {
            $result = LunarFortis::refund($transaction, $amount);
<<<<<<< HEAD
        } catch (Exception $exception) {
            Log::error('LunarFortis: Unable to process terminal refund: '.$exception->getMessage());
=======
        } catch (ApiException $exception) {
            Log::error('LunarFortis: Unable to process terminal refund: '.$exception->getMessage().' '.print_r($exception->getHttpResponse(), true));
>>>>>>> origin/main

            return new PaymentRefund(
                success: false,
                message: $exception->getMessage()
            );
        }

<<<<<<< HEAD
        $data = $result['data'] ?? [];
        $statusCode = $data['status_code'] ?? null;
        $reasonCodeId = $data['reason_code_id'] ?? null;

        if (! StatusCode::isRefunded($statusCode) || ! ReasonCode::isApproved($reasonCodeId)) {
=======
        $data = $result->getData();
        if (! StatusCode::isRefunded($data->getStatusCode()) || ! ReasonCode::isApproved($data->getReasonCodeId())) {
>>>>>>> origin/main
            Transaction::create([
                'parent_transaction_id' => $transaction->id,
                'order_id' => $transaction->order->id,
                'success' => false,
                'type' => 'refund',
                'driver' => self::PAYMENT_TYPE,
                'amount' => $amount,
<<<<<<< HEAD
                'reference' => $data['id'] ?? now()->timestamp,
                'status' => 'declined',
                'notes' => $data['reason_code'] ?? null,
                'card_type' => $transaction->card_type ?? '',
                'last_four' => $transaction->last_four ?? '',
                'meta' => [
                    'status_code' => $data['status_code'] ?? null,
                    'reason_code' => $data['reason_code'] ?? null,
                    'description' => $data['description'] ?? null,
                    'verbiage' => $data['verbiage'] ?? null,
=======
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
>>>>>>> origin/main
                    'terminal_id' => $this->getTerminalIdFromMeta($transaction),
                ],
            ]);

            return new PaymentRefund(
                success: false,
<<<<<<< HEAD
                message: $data['reason_code'] ?? 'Refund failed'
=======
                message: $data->getReasonCode()
>>>>>>> origin/main
            );
        }

        Transaction::create([
            'parent_transaction_id' => $transaction->id,
            'order_id' => $transaction->order->id,
            'success' => true,
            'type' => 'refund',
            'driver' => self::PAYMENT_TYPE,
            'amount' => $amount,
<<<<<<< HEAD
            'reference' => $data['id'] ?? now()->timestamp,
=======
            'reference' => $data->getId() ?? now()->timestamp,
>>>>>>> origin/main
            'status' => 'refunded',
            'card_type' => $transaction->card_type ?? '',
            'last_four' => $transaction->last_four ?? '',
            'meta' => [
<<<<<<< HEAD
                'status_code' => $data['status_code'] ?? null,
                'reason_code' => $data['reason_code'] ?? null,
                'description' => $data['description'] ?? null,
                'verbiage' => $data['verbiage'] ?? null,
=======
                'status_code' => $data->getStatusCode(),
                'reason_code' => $data->getReasonCode(),
                'description' => $data->getDescription(),
                'verbiage' => $data->getVerbiage(),
>>>>>>> origin/main
                'terminal_id' => $this->getTerminalIdFromMeta($transaction),
            ],
        ]);

        return new PaymentRefund(success: true);
    }

<<<<<<< HEAD
    private function storeTerminalTransaction(array $response): Transaction
    {
        $data = $response['data'] ?? [];

        if (empty($data)) {
=======
    private function storeTerminalTransaction(ResponseTransaction $response): Transaction
    {
        $data = $response->getData();

        if (! $data) {
>>>>>>> origin/main
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
<<<<<<< HEAD
            $statusCode = $data['status_code'] ?? null;
            $success = StatusCode::isSuccessful($statusCode);

            // Build transaction meta data from response
            $meta = [
                'status_code' => $data['status_code'] ?? null,
                'reason_code' => $data['reason_code'] ?? null,
                'fortis_transaction_id' => $data['id'] ?? null,
                'description' => $data['description'] ?? null,
                'verbiage' => $data['verbiage'] ?? null,
            ];

            // Add terminal ID if available
            if (isset($data['terminal_id'])) {
                $meta['terminal_id'] = $data['terminal_id'];

                // Try to get terminal details from the database
                if ($terminal = Terminal::where('fortis_id', $data['terminal_id'])->first()) {
=======
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
>>>>>>> origin/main
                    $meta['terminal_title'] = $terminal->title;
                    $meta['terminal_serial'] = $terminal->serial_number;
                }
            }

            // Add additional transaction details if available
<<<<<<< HEAD
            if (isset($data['tip_amount'])) {
                $meta['tip_amount'] = $data['tip_amount'];
            }

            if (isset($data['clerk_number'])) {
                $meta['clerk_number'] = $data['clerk_number'];
            }

            // Determine transaction type based on action or status
            $transactionType = $this->determineTransactionType($data);

            // Determine card details from response data
            $cardType = $data['account_type'] ?? 'credit';
            $lastFour = $data['last_four'] ?? '';
=======
            if ($data->getTipAmount()) {
                $meta['tip_amount'] = $data->getTipAmount();
            }

            if (method_exists($data, 'getClerkNumber') && $data->getClerkNumber()) {
                $meta['clerk_number'] = $data->getClerkNumber();
            }

            // Determine card details from response data
            $cardType = $data->getAccountType() ?? 'credit';
            $lastFour = $data->getLastFour() ?? '';
>>>>>>> origin/main

            return Transaction::create([
                'order_id' => $this->order->id,
                'success' => $success,
<<<<<<< HEAD
                'type' => $transactionType,
                'driver' => self::PAYMENT_TYPE,
                'amount' => $data['transaction_amount'] ?? $this->cart->total->value,
                'reference' => $data['id'] ?? now()->timestamp,
                'status' => $success
                    ? (StatusCode::isCaptured($data['status_code'])
                        ? 'approved'
                        : 'authorized'
                    )
                    : 'declined',
                'notes' => $success ? null : ($data['verbiage'] ?? $data['reason_code'] ?? null),
                'card_type' => $cardType,
                'last_four' => $lastFour,
                'captured_at' => StatusCode::isCaptured($data['status_code']) ? now() : null,
=======
                'type' => 'capture', // Terminal payments are always captures
                'driver' => self::PAYMENT_TYPE,
                'amount' => $data->getTransactionAmount() ?? $this->cart->total->value,
                'reference' => $data->getId() ?? now()->timestamp,
                'status' => $success ? 'approved' : 'declined',
                'notes' => $success ? '' : ($data->getVerbiage() ?? $data->getReasonCode()),
                'card_type' => $cardType,
                'last_four' => $lastFour,
                'captured_at' => $success ? now() : null,
>>>>>>> origin/main
                'meta' => $meta,
            ]);

        } catch (Exception $e) {
            Log::error('LunarFortis: Terminal payment processing failed', [
<<<<<<< HEAD
                'transaction_id' => $data['id'] ?? null,
=======
                'transaction_id' => $data?->getId(),
>>>>>>> origin/main
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
<<<<<<< HEAD
                'reference' => $data['id'] ?? now()->timestamp,
=======
                'reference' => $data?->getId() ?? now()->timestamp,
>>>>>>> origin/main
                'status' => 'failed',
                'notes' => $e->getMessage(),
                'card_type' => 'unknown',
                'last_four' => '',
                'meta' => [
<<<<<<< HEAD
                    'terminal_id' => $data['terminal_id'] ?? null,
=======
                    'terminal_id' => $data?->getTerminalId(),
>>>>>>> origin/main
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
<<<<<<< HEAD

    private function determineTransactionType(array $data): string
    {
        // Check if there's an action field that indicates the transaction type
        if (isset($data['@action'])) {
            return $data['@action'] == 'sale' ? 'capture' : 'intent';
        }

        // Check status code - if it's already captured, it was a sale transaction
        if (StatusCode::isCaptured($data['status_code'] ?? null)) {
            return 'capture';
        }

        // Default to intent for authorization-only transactions
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

        // Build transaction meta data from response
        $meta = [
            'status_code' => $data['status_code'] ?? null,
            'reason_code' => $data['reason_code'] ?? null,
            'fortis_transaction_id' => $data['id'] ?? null,
            'description' => $data['description'] ?? null,
            'verbiage' => $data['verbiage'] ?? null,
        ];

        // Add terminal ID if available
        if (isset($data['terminal_id'])) {
            $meta['terminal_id'] = $data['terminal_id'];

            // Try to get terminal details from the database
            if ($terminal = Terminal::where('fortis_id', $data['terminal_id'])->first()) {
                $meta['terminal_title'] = $terminal->title;
                $meta['terminal_serial'] = $terminal->serial_number;
            }
        }

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
            'notes' => $success ? null : ($data['verbiage'] ?? $data['reason_code'] ?? null),
            'card_type' => $data['account_type'] ?? $parentTransaction->card_type ?? 'N/A',
            'last_four' => $data['last_four'] ?? $parentTransaction->last_four ?? '',
            'captured_at' => StatusCode::isCaptured($statusCode) ? now() : null,
            'meta' => $meta,
        ]);
    }
=======
>>>>>>> origin/main
}
