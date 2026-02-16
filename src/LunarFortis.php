<?php

namespace Hyrograsper\LunarFortis;

use Exception;
use Hyrograsper\LunarFortis\Services\FortisHttpService;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

class LunarFortis
{
    protected ?FortisHttpService $httpService = null;

    protected function getHttpService(): FortisHttpService
    {
        return $this->httpService ??= new FortisHttpService;
    }

    /** @throws Exception */
    public function getClientTokenForSaleAmount(int $amount, string $action = 'sale'): ?string
    {
        $response = $this->getHttpService()->createTransactionIntention($amount, $action);

        return $response['data']['client_token'] ?? null;
    }

    /** @throws Exception */
    public function completeAuthorizedTransaction(TransactionContract $transaction, int $amount = 0, array $options = []): array
    {
        $transactionOptions = [];

        if ($transaction->order?->reference) {
            $transactionOptions['order_number'] = $transaction->order->reference;
        }

        if ($transaction->order?->customer_id) {
            $transactionOptions['customer_id'] = $transaction->order->customer_id;
        }

        return $this->getHttpService()->completeAuthorizedTransaction(
            $transaction->reference,
            $amount,
            array_merge($transactionOptions, $options)
        );
    }

    /** @throws Exception */
    public function authorizeCcFromToken(string $tokenId, Order $order): array
    {
        /** @var OrderAddress $billingAddress */
        $billingAddress = $order->billingAddress;

        $options = [
            'order_number' => $order->reference,
            'customer_id' => $order->customer_id,
            'billing_address' => [
                'street' => $billingAddress->line_one,
                'city' => $billingAddress->city,
                'state' => $billingAddress->state,
                'postal_code' => $billingAddress->postcode,
                'country' => $billingAddress->country->iso3,
            ],
        ];

        return $this->getHttpService()->authorizeCcFromToken($tokenId, $order->total->value, $options);
    }

    /** @throws Exception */
    public function refund(TransactionContract $transaction, int $amount): array
    {
        return $this->getHttpService()->refund($transaction->reference, $amount);
    }

    /** @throws Exception */
    public function getTransaction(string $transactionId): array
    {
        return $this->getHttpService()->getTransaction($transactionId);
    }

    /** @throws Exception */
    public function createTerminal(array $terminalData): array
    {
        return $this->getHttpService()->createTerminal($terminalData);
    }

    /** @throws Exception */
    public function listTerminals(array $options = []): array
    {
        return $this->getHttpService()->listTerminals($options);
    }

    /** @throws Exception */
    public function getTerminal(string $terminalId, ?array $expand = null, ?array $fields = null): array
    {
        return $this->getHttpService()->getTerminal($terminalId, $expand, $fields);
    }

    /** @throws Exception */
    public function updateTerminal(string $terminalId, array $terminalData, ?array $expand = null): array
    {
        return $this->getHttpService()->updateTerminal($terminalId, $terminalData, $expand);
    }

    /** @throws Exception */
    public function createSimpleTerminal(
        string $title,
        string $serialNumber,
        string $terminalApplicationId,
        array $additionalOptions = []
    ): array {
        return $this->createTerminal(array_merge([
            'title' => $title,
            'serial_number' => $serialNumber,
            'terminal_application_id' => $terminalApplicationId,
            'active' => true,
        ], $additionalOptions));
    }

    /** @throws Exception */
    public function getActiveTerminals(): array
    {
        return $this->listTerminals([
            'filterBy' => [
                [
                    'key' => 'active',
                    'operator' => '=',
                    'value' => '1',
                ],
                [
                    'key' => 'location_id',
                    'operator' => '=',
                    'value' => config('services.fortis.locationId'),
                ],
            ],
        ]);
    }

    /** @throws Exception */
    public function setTerminalStatus(string $terminalId, bool $active): array
    {
        return $this->updateTerminal($terminalId, ['active' => $active]);
    }

    /** @throws Exception */
    public function authorizeTerminalCreditCard(string $terminalId, int $amount, array $options = []): array
    {
        return $this->getHttpService()->authorizeTerminalCreditCard($terminalId, $amount, $options);
    }

    /** @throws Exception */
    public function checkTerminalTransactionStatus(string $statusCode): array
    {
        return $this->getHttpService()->checkAsyncStatus($statusCode);
    }

    /** @throws Exception */
    public function waitForTerminalTransaction(
        string $statusCode,
        int $timeoutSeconds = 300,
        int $pollIntervalSeconds = 2
    ): array {
        $startTime = time();
        $endTime = $startTime + $timeoutSeconds;

        while (time() < $endTime) {
            $status = $this->checkTerminalTransactionStatus($statusCode);
            $statusData = $status['data'] ?? [];

            if (($statusData['progress'] ?? 0) >= 100) {
                return $status;
            }

            if ($statusData['error'] ?? null) {
                Log::error('LunarFortis: Terminal transaction failed', [
                    'status_code' => $statusCode,
                    'error' => $statusData['error'],
                    'progress' => $statusData['progress'] ?? 0,
                ]);

                return $status;
            }

            sleep($pollIntervalSeconds);
        }

        $finalStatus = $this->checkTerminalTransactionStatus($statusCode);
        Log::warning('LunarFortis: Terminal transaction polling timed out', [
            'status_code' => $statusCode,
            'timeout_seconds' => $timeoutSeconds,
            'final_progress' => $finalStatus['data']['progress'] ?? 0,
        ]);

        return $finalStatus;
    }

    /** @throws Exception */
    public function processTerminalCreditCardAuth(string $terminalId, int $amount, array $options = []): array
    {
        $timeoutSeconds = $options['timeout_seconds'] ?? 300;
        $pollIntervalSeconds = $options['poll_interval_seconds'] ?? 2;
        unset($options['timeout_seconds'], $options['poll_interval_seconds']);

        try {
            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Initiating terminal credit card authorization', [
                    'terminal_id' => $terminalId,
                    'amount' => $amount,
                    'options' => $options,
                ]);
            }

            $processingResponse = $this->authorizeTerminalCreditCard($terminalId, $amount, $options);
            $asyncData = $processingResponse['data']['async'] ?? [];
            $statusCode = $asyncData['code'] ?? null;

            if (! $statusCode) {
                throw new Exception('No async status code received from terminal authorization');
            }

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal authorization initiated', [
                    'status_code' => $statusCode,
                    'async_link' => $asyncData['link'] ?? null,
                ]);
            }

            $finalStatus = $this->waitForTerminalTransaction($statusCode, $timeoutSeconds, $pollIntervalSeconds);
            $statusData = $finalStatus['data'] ?? [];

            $result = [
                'success' => ($statusData['progress'] ?? 0) >= 100 && ! ($statusData['error'] ?? null),
                'status_code' => $statusCode,
                'progress' => $statusData['progress'] ?? 0,
                'transaction_id' => $statusData['id'] ?? null,
                'error' => $statusData['error'] ?? null,
                'ttl' => $statusData['ttl'] ?? null,
                'type' => $statusData['type'] ?? null,
                'completed' => ($statusData['progress'] ?? 0) >= 100,
                'timed_out' => ($statusData['progress'] ?? 0) < 100 && ! ($statusData['error'] ?? null),
            ];

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal authorization processing completed', $result);
            }

            return $result;

        } catch (Exception $exception) {
            Log::error('LunarFortis: Terminal credit card authorization failed', [
                'terminal_id' => $terminalId,
                'amount' => $amount,
                'options' => $options,
                'error' => $exception->getMessage(),
            ]);
            throw new Exception("Terminal credit card authorization failed: {$exception->getMessage()}");
        }
    }

    /** @throws Exception */
    public function captureTerminalTransaction(string $transactionId, int $amount, array $options = []): array
    {
        return $this->getHttpService()->completeAuthorizedTransaction(
            $transactionId,
            $amount,
            $options
        );
    }
}
