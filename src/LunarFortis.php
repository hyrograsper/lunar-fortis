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
        if ($this->httpService) {
            return $this->httpService;
        }

        return $this->httpService = new FortisHttpService;
    }

    /**
     * @throws Exception
     */
    public function getClientTokenForSaleAmount(int $amount, string $action = 'sale'): ?string
    {
        try {
            $response = $this->getHttpService()->createTransactionIntention($amount, $action);

            return $response['data']['client_token'] ?? null;
        } catch (Exception $exception) {
            throw new Exception("Unable to get client token for {$action}: {$exception->getMessage()}");
        }
    }

    /**
     * @throws Exception
     */
    public function completeAuthorizedTransaction(TransactionContract $transaction, int $amount = 0, array $options = []): array
    {
        try {
            // Build options from transaction data and merge with provided options
            $transactionOptions = [];

            if ($transaction->order?->reference) {
                $transactionOptions['order_number'] = $transaction->order->reference;
            }

            if ($transaction->order?->customer_id) {
                $transactionOptions['customer_id'] = $transaction->order->customer_id;
            }

            $mergedOptions = array_merge($transactionOptions, $options);

            return $this->getHttpService()->completeAuthorizedTransaction(
                $transaction->reference,
                $amount,
                $mergedOptions
            );
        } catch (Exception $exception) {
            throw new Exception("Failed to complete authorized transaction: {$exception->getMessage()}");
        }
    }

    /**
     * @throws Exception
     */
    public function authorizeCcFromToken(string $tokenId, Order $order): array
    {
        try {
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
        } catch (Exception $exception) {
            throw new Exception("Failed to authorize credit card from token: {$exception->getMessage()}");
        }
    }

    /**
     * @throws Exception
     */
    public function refund(TransactionContract $transaction, int $amount): array
    {
        try {
            return $this->getHttpService()->refund($transaction->reference, $amount);
        } catch (Exception $exception) {
            throw new Exception("Failed to process refund: {$exception->getMessage()}");
        }
    }

    /**
     * @throws Exception
     */
    public function getTransaction(string $transactionId): array
    {
        try {
            return $this->getHttpService()->getTransaction($transactionId);
        } catch (Exception $exception) {
            throw new Exception("Failed to retrieve transaction: {$exception->getMessage()}");
        }
    }

    /**
     * Create a new terminal device
     *
     * @throws Exception
     */
    public function createTerminal(array $terminalData): array
    {
        try {
            return $this->getHttpService()->createTerminal($terminalData);
        } catch (Exception $exception) {
            throw new Exception("Failed to create terminal: {$exception->getMessage()}");
        }
    }

    /**
     * Get all terminals for the location
     *
     * @throws Exception
     */
    public function listTerminals(array $options = []): array
    {
        try {
            return $this->getHttpService()->listTerminals($options);
        } catch (Exception $exception) {
            throw new Exception("Failed to list terminals: {$exception->getMessage()}");
        }
    }

    /**
     * Get a single terminal by ID
     *
     * @throws Exception
     */
    public function getTerminal(string $terminalId, ?array $expand = null, ?array $fields = null): array
    {
        try {
            return $this->getHttpService()->getTerminal($terminalId, $expand, $fields);
        } catch (Exception $exception) {
            throw new Exception("Failed to retrieve terminal {$terminalId}: {$exception->getMessage()}");
        }
    }

    /**
     * Update an existing terminal
     *
     * @throws Exception
     */
    public function updateTerminal(string $terminalId, array $terminalData, ?array $expand = null): array
    {
        try {
            return $this->getHttpService()->updateTerminal($terminalId, $terminalData, $expand);
        } catch (Exception $exception) {
            throw new Exception("Failed to update terminal {$terminalId}: {$exception->getMessage()}");
        }
    }

    /**
     * Create a simple terminal with minimal required data
     *
     * @throws Exception
     */
    public function createSimpleTerminal(
        string $title,
        string $serialNumber,
        string $terminalApplicationId,
        array $additionalOptions = []
    ): array {
        $terminalData = array_merge([
            'title' => $title,
            'serial_number' => $serialNumber,
            'terminal_application_id' => $terminalApplicationId,
            'active' => true,
        ], $additionalOptions);

        return $this->createTerminal($terminalData);
    }

    /**
     * Get all active terminals for current location
     *
     * @throws Exception
     */
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

    /**
     * Activate/deactivate a terminal
     *
     * @throws Exception
     */
    public function setTerminalStatus(string $terminalId, bool $active): array
    {
        return $this->updateTerminal($terminalId, ['active' => $active]);
    }

    /**
     * Authorize a credit card transaction through a terminal (auth-only)
     *
     * @throws Exception
     */
    public function authorizeTerminalCreditCard(string $terminalId, int $amount, array $options = []): array
    {
        try {
            return $this->getHttpService()->authorizeTerminalCreditCard($terminalId, $amount, $options);
        } catch (Exception $exception) {
            throw new Exception("Failed to authorize terminal credit card: {$exception->getMessage()}");
        }
    }

    /**
     * Check the status of an async terminal transaction
     *
     * @throws Exception
     */
    public function checkTerminalTransactionStatus(string $statusCode): array
    {
        try {
            return $this->getHttpService()->checkAsyncStatus($statusCode);
        } catch (Exception $exception) {
            throw new Exception("Failed to check terminal transaction status: {$exception->getMessage()}");
        }
    }

    /**
     * Wait for a terminal transaction to complete by polling the status
     *
     * @throws Exception
     */
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

            // Check if transaction is complete
            if (($statusData['progress'] ?? 0) >= 100) {
                return $status;
            }

            // Check for errors
            if ($statusData['error'] ?? null) {
                Log::error('LunarFortis: Terminal transaction failed', [
                    'status_code' => $statusCode,
                    'error' => $statusData['error'],
                    'progress' => $statusData['progress'] ?? 0,
                ]);

                return $status;
            }

            // Wait before next poll
            sleep($pollIntervalSeconds);
        }

        // Timeout reached - get final status
        $finalStatus = $this->checkTerminalTransactionStatus($statusCode);
        Log::warning('LunarFortis: Terminal transaction polling timed out', [
            'status_code' => $statusCode,
            'timeout_seconds' => $timeoutSeconds,
            'final_progress' => $finalStatus['data']['progress'] ?? 0,
        ]);

        return $finalStatus;
    }

    /**
     * Process a complete terminal credit card authorization with automatic status monitoring
     *
     * @throws Exception
     */
    public function processTerminalCreditCardAuth(string $terminalId, int $amount, array $options = []): array
    {
        // Extract polling options
        $timeoutSeconds = $options['timeout_seconds'] ?? 300;
        $pollIntervalSeconds = $options['poll_interval_seconds'] ?? 2;
        unset($options['timeout_seconds'], $options['poll_interval_seconds']);

        try {
            // Step 1: Initiate the terminal authorization
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

            // Step 2: Wait for completion
            $finalStatus = $this->waitForTerminalTransaction($statusCode, $timeoutSeconds, $pollIntervalSeconds);
            $statusData = $finalStatus['data'] ?? [];

            // Step 3: Build result array
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

    /**
     * Capture an authorized terminal transaction (complete the payment)
     *
     * @throws Exception
     */
    public function captureTerminalTransaction(string $transactionId, int $amount, array $options = []): array
    {
        try {
            return $this->getHttpService()->completeAuthorizedTransaction(
                $transactionId,
                $amount,
                $options
            );
        } catch (Exception $exception) {
            throw new Exception("Failed to capture terminal transaction: {$exception->getMessage()}");
        }
    }
}
