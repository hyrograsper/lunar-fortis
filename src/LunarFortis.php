<?php

namespace Hyrograsper\LunarFortis;

use Exception;
use FortisAPILib\Authentication\DeveloperIdCredentialsBuilder;
use FortisAPILib\Authentication\UserApiKeyCredentialsBuilder;
use FortisAPILib\Authentication\UserIdCredentialsBuilder;
use FortisAPILib\Environment;
use FortisAPILib\Exceptions\ApiException;
use FortisAPILib\FortisAPIClient;
use FortisAPILib\FortisAPIClientBuilder;
use FortisAPILib\Models\ActionEnum;
use FortisAPILib\Models\Builders\BillingAddress1Builder;
use FortisAPILib\Models\Builders\FilterByBuilder;
use FortisAPILib\Models\Builders\Order21Builder;
use FortisAPILib\Models\Builders\PageBuilder;
use FortisAPILib\Models\Builders\V1ElementsTransactionIntentionRequestBuilder;
use FortisAPILib\Models\Builders\V1TerminalsRequest1Builder;
use FortisAPILib\Models\Builders\V1TerminalsRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsAuthCompleteRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsCcAuthOnlyTokenRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsCcRefundKeyedRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsCcSaleTerminalRequestBuilder;
use FortisAPILib\Models\OperatorEnum;
use FortisAPILib\Models\ResponseAsyncStatus;
use FortisAPILib\Models\ResponseTerminal;
use FortisAPILib\Models\ResponseTerminalsCollection;
use FortisAPILib\Models\ResponseTransaction;
use FortisAPILib\Models\ResponseTransactionProcessing;
use FortisAPILib\Models\TerminalManufacturerCodeEnum;
use FortisAPILib\Models\V1ElementsTransactionIntentionRequest;
use Hyrograsper\LunarFortis\Helpers\FortisErrorHelper;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

class LunarFortis
{
    protected ?FortisAPIClient $client = null;

    protected function getClientInstance(): FortisAPIClient
    {
        if ($this->client) {
            return $this->client;
        }

        return $this->client = FortisAPIClientBuilder::init()
            ->environment(config('lunar-fortis.environment') == 'production'
                ? Environment::PRODUCTION
                : Environment::SANDBOX
            )
            ->userIdCredentials(
                UserIdCredentialsBuilder::init(
                    userId: config('services.fortis.userId'),
                )
            )
            ->userApiKeyCredentials(
                UserApiKeyCredentialsBuilder::init(
                    userApiKey: config('services.fortis.userApiKey'),
                )
            )
            ->developerIdCredentials(
                DeveloperIdCredentialsBuilder::init(
                    developerId: config('services.fortis.developerId'),
                )
            )
            ->build();
    }

    /**
     * @throws ApiException
     */
    protected function buildTransactionIntentionRequest(int $amount, string $action = ActionEnum::SALE): V1ElementsTransactionIntentionRequest
    {
        ActionEnum::checkValue($action);

        return V1ElementsTransactionIntentionRequestBuilder::init()
            ->action($action)
            ->digitalWalletsOnly(false)
            ->amount($amount)
            ->locationId(config('services.fortis.locationId'))
            ->build();
    }

    /**
     * @throws Exception
     */
    public function getClientTokenForSaleAmount(int $amount, string $action = ActionEnum::SALE): ?string
    {
        try {
            $data = $this->getClientInstance()
                ->getElementsController()
                ->transactionIntention(
                    body: $this->buildTransactionIntentionRequest($amount, $action)
                )->getData();

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Client token generated successfully', [
                    'amount' => $amount,
                    'action' => $action,
                ]);
            }

            return $data->getClientToken();
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to get client token for transaction intention', [
                'amount' => $amount,
                'action' => $action,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Unable to get client token for {$action}: {$formattedError}");
        } catch (Exception $e) {
            Log::error('LunarFortis: Failed to get client token for transaction intention', [
                'amount' => $amount,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Unable to get client token for {$action}: {$e->getMessage()}");
        }
    }

    /**
     * @throws Exception
     */
    public function completeAuthorizedTransaction(TransactionContract $transaction, int $amount = 0): ResponseTransaction
    {
        try {
            $result = $this->getClientInstance()
                ->getTransactionsUpdatesController()
                ->authComplete(
                    $transaction->reference,
                    V1TransactionsAuthCompleteRequestBuilder::init()
                        ->locationId(config('services.fortis.locationId'))
                        ->transactionAmount($amount)
                        ->orderNumber($transaction->order?->reference)
                        ->customerId($transaction->order?->customer_id)
                        ->build()
                );

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Transaction authorization completed successfully', [
                    'transaction_reference' => $transaction->reference,
                    'amount' => $amount,
                    'order_reference' => $transaction->order?->reference,
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to complete authorized transaction', [
                'transaction_reference' => $transaction->reference,
                'amount' => $amount,
                'order_reference' => $transaction->order?->reference,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to complete authorized transaction: {$formattedError}");
        }
    }

    /**
     * @throws Exception
     */
    public function authorizeCcFromToken(string $tokenId, Order $order): ResponseTransaction
    {
        try {
            /** @var OrderAddress $billingAddress */
            $billingAddress = $order->billingAddress;

            $result = $this->getClientInstance()
                ->getTransactionsCreditCardController()
                ->ccAuthOnlyTokenized(
                    V1TransactionsCcAuthOnlyTokenRequestBuilder::init($order->total->value)
                        ->tokenId($tokenId)
                        ->orderNumber($order->reference)
                        ->billingAddress(
                            BillingAddress1Builder::init()
                                ->street($billingAddress->line_one)
                                ->city($billingAddress->city)
                                ->state($billingAddress->state)
                                ->postalCode($billingAddress->postcode)
                                ->country($billingAddress->country->iso3)
                                ->build()
                        )
                        ->customerId($order->customer_id)
                        ->build()
                );

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Credit card authorization from token completed successfully', [
                    'token_id' => $tokenId,
                    'order_reference' => $order->reference,
                    'amount' => $order->total->value,
                    'customer_id' => $order->customer_id,
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to authorize credit card from token', [
                'token_id' => $tokenId,
                'order_reference' => $order->reference,
                'amount' => $order->total->value,
                'customer_id' => $order->customer_id,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to authorize credit card from token: {$formattedError}");
        }
    }

    /**
     * @throws Exception
     */
    public function refund(TransactionContract $transaction, int $amount): ResponseTransaction
    {
        try {
            $result = $this->getClientInstance()
                ->getTransactionsCreditCardController()
                ->cCRefund(
                    V1TransactionsCcRefundKeyedRequestBuilder::init($amount)
                        ->previousTransactionId($transaction->reference)
                        ->locationId(config('services.fortis.locationId'))
                        ->orderNumber($transaction->reference)
                        ->cvv(999)
                        ->build()
                );

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Transaction refund completed successfully', [
                    'transaction_reference' => $transaction->reference,
                    'refund_amount' => $amount,
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to process refund', [
                'transaction_reference' => $transaction->reference,
                'refund_amount' => $amount,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to process refund: {$formattedError}");
        }
    }

    /**
     * @throws Exception
     */
    public function getTransaction(string $transactionId): ResponseTransaction
    {
        try {
            $result = $this->getClientInstance()
                ->getTransactionsReadController()
                ->getTransaction($transactionId);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Transaction retrieved successfully', [
                    'transaction_id' => $transactionId,
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to retrieve transaction', [
                'transaction_id' => $transactionId,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to retrieve transaction: {$formattedError}");
        }
    }

    // Terminal Management Methods

    /**
     * Create a new terminal device
     *
     * @throws Exception
     */
    public function createTerminal(array $terminalData): ResponseTerminal
    {
        try {
            $builder = V1TerminalsRequestBuilder::init(
                locationId: $terminalData['location_id'] ?? config('services.fortis.locationId'),
                terminalApplicationId: $terminalData['terminal_application_id'],
                terminalManufacturerCode: $terminalData['terminal_manufacturer_code'] ?? TerminalManufacturerCodeEnum::ENUM_1,
                title: $terminalData['title'],
                serialNumber: $terminalData['serial_number']
            );

            // Add optional fields if provided
            if (isset($terminalData['default_product_transaction_id'])) {
                $builder->defaultProductTransactionId($terminalData['default_product_transaction_id']);
            }
            if (isset($terminalData['active'])) {
                $builder->active($terminalData['active']);
            }

            $result = $this->getClientInstance()
                ->getTerminalsController()
                ->createANewTerminalDevice($builder->build());

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal created successfully', [
                    'title' => $terminalData['title'],
                    'serial_number' => $terminalData['serial_number'],
                    'terminal_id' => $result->getData()->getId(),
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to create terminal', [
                'terminal_data' => $terminalData,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to create terminal: {$formattedError}");
        }
    }

    /**
     * Get all terminals for the location
     *
     * @throws Exception
     */
    public function listTerminals(array $options = []): ResponseTerminalsCollection
    {
        try {
            $page = null;
            $order = null;
            $filterBy = null;
            $expand = $options['expand'] ?? null;
            $format = $options['format'] ?? null;
            $typeahead = $options['typeahead'] ?? null;
            $fields = $options['fields'] ?? null;

            // Build pagination if provided
            if (isset($options['page'])) {
                $page = PageBuilder::init()
                    ->number($options['page']['number'] ?? 1)
                    ->size($options['page']['size'] ?? 50)
                    ->build();
            }

            // Build order if provided
            if (isset($options['order'])) {
                $order = [];
                foreach ($options['order'] as $orderItem) {
                    // Convert string operators to proper enum values
                    $operator = match (strtolower($orderItem['operator'])) {
                        'asc' => OperatorEnum::ASC,
                        'desc' => OperatorEnum::DESC,
                        default => OperatorEnum::ASC,
                    };

                    $order[] = Order21Builder::init(
                        $orderItem['key'],
                        $operator
                    )->build();
                }
            }

            // Build filters if provided
            if (isset($options['filterBy'])) {
                $filterBy = [];
                foreach ($options['filterBy'] as $filter) {
                    // Use the operator string directly - the SDK expects string operators, not enum values
                    $filterBy[] = FilterByBuilder::init(
                        $filter['key'],
                        $filter['operator'],
                        $filter['value']
                    )->build();
                }
            }

            $result = $this->getClientInstance()
                ->getTerminalsController()
                ->listAllTerminalsRelated(
                    $page,
                    $order,
                    $filterBy,
                    $expand,
                    $format,
                    $typeahead,
                    $fields
                );

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminals listed successfully', [
                    'total_count' => count($result->getList() ?? []),
                    'options' => $options,
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to list terminals', [
                'options' => $options,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to list terminals: {$formattedError}");
        }
    }

    /**
     * Get a single terminal by ID
     *
     * @throws Exception
     */
    public function getTerminal(string $terminalId, ?array $expand = null, ?array $fields = null): ResponseTerminal
    {
        try {
            $result = $this->getClientInstance()
                ->getTerminalsController()
                ->viewSingleTerminalsRecord($terminalId, $expand, $fields);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal retrieved successfully', [
                    'terminal_id' => $terminalId,
                    'expand' => $expand,
                    'fields' => $fields,
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to retrieve terminal', [
                'terminal_id' => $terminalId,
                'expand' => $expand,
                'fields' => $fields,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to retrieve terminal {$terminalId}: {$formattedError}");
        }
    }

    /**
     * Update an existing terminal
     *
     * @throws Exception
     */
    public function updateTerminal(string $terminalId, array $terminalData, ?array $expand = null): ResponseTerminal
    {
        try {
            $builder = V1TerminalsRequest1Builder::init();

            // Add provided fields to the builder
            $fieldMapping = [
                'location_id' => 'locationId',
                'default_product_transaction_id' => 'defaultProductTransactionId',
                'terminal_application_id' => 'terminalApplicationId',
                'terminal_manufacturer_code' => 'terminalManufacturerCode',
                'title' => 'title',
                'serial_number' => 'serialNumber',
                'active' => 'active',
            ];

            foreach ($fieldMapping as $dataKey => $builderMethod) {
                if (isset($terminalData[$dataKey])) {
                    $builder->$builderMethod($terminalData[$dataKey]);
                }
            }

            $result = $this->getClientInstance()
                ->getTerminalsController()
                ->updateTerminalRecord($terminalId, $builder->build(), $expand);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal updated successfully', [
                    'terminal_id' => $terminalId,
                    'updated_fields' => array_keys($terminalData),
                    'expand' => $expand,
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to update terminal', [
                'terminal_id' => $terminalId,
                'terminal_data' => $terminalData,
                'expand' => $expand,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to update terminal {$terminalId}: {$formattedError}");
        }
    }

    // Helper methods for common terminal operations

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
    ): ResponseTerminal {
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
    public function getActiveTerminals(): ResponseTerminalsCollection
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
    public function setTerminalStatus(string $terminalId, bool $active): ResponseTerminal
    {
        return $this->updateTerminal($terminalId, ['active' => $active]);
    }

    // Terminal Credit Card Processing Methods

    /**
     * Initiate a credit card sale transaction through a terminal
     *
     * @throws Exception
     */
    public function chargeTerminalCreditCard(string $terminalId, int $amount, array $options = []): ResponseTransactionProcessing
    {
        $builder = V1TransactionsCcSaleTerminalRequestBuilder::init(
            locationId: $options['location_id'] ?? config('services.fortis.locationId'),
            transactionAmount: $amount,
            terminalId: $terminalId
        );

        // Add optional fields if provided
        if (isset($options['order_number'])) {
            $builder->orderNumber($options['order_number']);
        }
        if (isset($options['customer_id'])) {
            $builder->customerId($options['customer_id']);
        }
        if (isset($options['contact_id'])) {
            $builder->contactId($options['contact_id']);
        }
        if (isset($options['description'])) {
            $builder->description($options['description']);
        }
        if (isset($options['clerk_number'])) {
            $builder->clerkNumber($options['clerk_number']);
        }
        if (isset($options['tip_amount'])) {
            $builder->tipAmount($options['tip_amount']);
        }
        if (isset($options['tax'])) {
            $builder->tax($options['tax']);
        }
        if (isset($options['subtotal_amount'])) {
            $builder->subtotalAmount($options['subtotal_amount']);
        }
        if (isset($options['surcharge_amount'])) {
            $builder->surchargeAmount($options['surcharge_amount']);
        }
        if (isset($options['transaction_api_id'])) {
            $builder->transactionApiId($options['transaction_api_id']);
        }
        if (isset($options['po_number'])) {
            $builder->poNumber($options['po_number']);
        }
        if (isset($options['notification_email_address'])) {
            $builder->notificationEmailAddress($options['notification_email_address']);
        }
        if (isset($options['save_account'])) {
            $builder->saveAccount($options['save_account']);
        }
        if (isset($options['save_account_title'])) {
            $builder->saveAccountTitle($options['save_account_title']);
        }
        if (isset($options['product_transaction_id'])) {
            $builder->productTransactionId($options['product_transaction_id']);
        }

        // Lodging industry specific fields
        if (isset($options['checkin_date'])) {
            $builder->checkinDate($options['checkin_date']);
        }
        if (isset($options['checkout_date'])) {
            $builder->checkoutDate($options['checkout_date']);
        }
        if (isset($options['room_num'])) {
            $builder->roomNum($options['room_num']);
        }
        if (isset($options['room_rate'])) {
            $builder->roomRate($options['room_rate']);
        }

        try {
            $result = $this->getClientInstance()
                ->getTransactionsCreditCardController()
                ->cCSaleTerminal($builder->build());

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal credit card charge initiated successfully', [
                    'terminal_id' => $terminalId,
                    'amount' => $amount,
                    'status_code' => $result->getData()->getAsync()->getCode(),
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to charge terminal credit card', [
                'terminal_id' => $terminalId,
                'amount' => $amount,
                'options' => $options,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to charge terminal credit card: {$formattedError}");
        }
    }

    /**
     * Check the status of an async terminal transaction
     *
     * @throws Exception
     */
    public function checkTerminalTransactionStatus(string $statusCode): ResponseAsyncStatus
    {
        try {
            $result = $this->getClientInstance()
                ->getAsyncProcessingController()
                ->statusCheck($statusCode);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal transaction status checked', [
                    'status_code' => $statusCode,
                    'progress' => $result->getData()->getProgress(),
                    'error' => $result->getData()->getError(),
                ]);
            }

            return $result;
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Failed to check terminal transaction status', [
                'status_code' => $statusCode,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to check terminal transaction status: {$formattedError}");
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
    ): ResponseAsyncStatus {
        $startTime = time();
        $endTime = $startTime + $timeoutSeconds;

        while (time() < $endTime) {
            $status = $this->checkTerminalTransactionStatus($statusCode);
            $statusData = $status->getData();

            // Check if transaction is complete
            if ($statusData->getProgress() >= 100) {
                return $status;
            }

            // Check for errors
            if ($statusData->getError()) {
                Log::error('LunarFortis: Terminal transaction failed', [
                    'status_code' => $statusCode,
                    'error' => $statusData->getError(),
                    'progress' => $statusData->getProgress(),
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
            'final_progress' => $finalStatus->getData()->getProgress(),
        ]);

        return $finalStatus;
    }

    /**
     * Process a complete terminal credit card transaction with automatic status monitoring
     *
     * @throws Exception
     */
    public function processTerminalCreditCard(string $terminalId, int $amount, array $options = []): array
    {
        // Extract polling options
        $timeoutSeconds = $options['timeout_seconds'] ?? 300;
        $pollIntervalSeconds = $options['poll_interval_seconds'] ?? 2;
        unset($options['timeout_seconds'], $options['poll_interval_seconds']);

        try {
            // Step 1: Initiate the terminal transaction
            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Initiating terminal credit card transaction', [
                    'terminal_id' => $terminalId,
                    'amount' => $amount,
                    'options' => $options,
                ]);
            }

            $processingResponse = $this->chargeTerminalCreditCard($terminalId, $amount, $options);
            $asyncData = $processingResponse->getData()->getAsync();
            $statusCode = $asyncData->getCode();

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal transaction initiated', [
                    'status_code' => $statusCode,
                    'async_link' => $asyncData->getLink(),
                ]);
            }

            // Step 2: Wait for completion
            $finalStatus = $this->waitForTerminalTransaction($statusCode, $timeoutSeconds, $pollIntervalSeconds);
            $statusData = $finalStatus->getData();

            // Step 3: Build result array
            $result = [
                'success' => $statusData->getProgress() >= 100 && ! $statusData->getError(),
                'status_code' => $statusCode,
                'progress' => $statusData->getProgress(),
                'transaction_id' => $statusData->getId(),
                'error' => $statusData->getError(),
                'ttl' => $statusData->getTtl(),
                'type' => $statusData->getType(),
                'completed' => $statusData->getProgress() >= 100,
                'timed_out' => $statusData->getProgress() < 100 && ! $statusData->getError(),
            ];

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal transaction processing completed', $result);
            }

            return $result;

        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('LunarFortis: Terminal credit card processing failed', [
                'terminal_id' => $terminalId,
                'amount' => $amount,
                'options' => $options,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Terminal credit card processing failed: {$formattedError}");
        } catch (Exception $e) {
            Log::error('LunarFortis: Terminal credit card processing failed', [
                'terminal_id' => $terminalId,
                'amount' => $amount,
                'options' => $options,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Terminal credit card processing failed: {$e->getMessage()}");
        }
    }
}
