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
use FortisAPILib\Models\Builders\V1TransactionsCcAuthOnlyTokenRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsCcRefundKeyedRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsCcSalePrevTrxnRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsCcSaleTerminalRequestBuilder;
use FortisAPILib\Models\CommunicationTypeEnum;
use FortisAPILib\Models\OperatorEnum;
use FortisAPILib\Models\ResponseAsyncStatus;
use FortisAPILib\Models\ResponseTerminal;
use FortisAPILib\Models\ResponseTerminalsCollection;
use FortisAPILib\Models\ResponseTransaction;
use FortisAPILib\Models\ResponseTransactionProcessing;
use FortisAPILib\Models\TerminalManufacturerCodeEnum;
use FortisAPILib\Models\V1ElementsTransactionIntentionRequest;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\Transaction;

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
     * @throws ApiException|Exception
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
     * @throws ApiException|Exception
     */
    public function getClientTokenForSaleAmount(int $amount, string $action = ActionEnum::SALE): ?string
    {
        try {
            $data = $this->getClientInstance()
                ->getElementsController()
                ->transactionIntention(
                    body: $this->buildTransactionIntentionRequest($amount, $action)
                )->getData();

            return $data->getClientToken();
        } catch (ApiException|Exception $e) {
            $message = "Unable to get client token for {$action}: {$e->getMessage()}";
            Log::error($message);
            throw new Exception($message);
        }
    }

    /**
     * @throws ApiException|Exception
     */
    public function capturePreviousTransaction(Transaction $transaction, int $amount = 0): ResponseTransaction
    {
        return $this->getClientInstance()
            ->getTransactionsCreditCardController()
            ->cCSalePreviousTransaction(
                V1TransactionsCcSalePrevTrxnRequestBuilder::init()
                    ->locationId(config('services.fortis.locationId'))
                    ->previousTransactionId($transaction->reference)
                    ->transactionAmount($amount)
                    ->orderNumber($transaction->order?->reference)
                    ->customerId($transaction->order?->customer_id)
                    ->build()
            );
    }

    /**
     * @throws ApiException|Exception
     */
    public function authorizeCcFromToken(string $tokenId, Order $order): ResponseTransaction
    {
        /** @var OrderAddress $billingAddress */
        $billingAddress = $order->billingAddress;

        return $this->getClientInstance()
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
    }

    /**
     * @throws ApiException|Exception
     */
    public function refund(Transaction $transaction, int $amount): ResponseTransaction
    {
        return $this->getClientInstance()
            ->getTransactionsCreditCardController()
            ->cCRefund(
                V1TransactionsCcRefundKeyedRequestBuilder::init($amount)
                    ->previousTransactionId($transaction->reference)
                    ->locationId(config('services.fortis.locationId'))
                    ->orderNumber($transaction->reference)
                    ->cvv(999)
                    ->build()
            );
    }

    // Terminal Management Methods

    /**
     * Create a new terminal device
     *
     * @param  array  $terminalData  Array containing terminal configuration data
     *
     * @throws ApiException|Exception
     */
    public function createTerminal(array $terminalData): ResponseTerminal
    {
        $builder = V1TerminalsRequestBuilder::init(
            locationId: $terminalData['location_id'] ?? config('services.fortis.locationId'),
            terminalApplicationId: $terminalData['terminal_application_id'],
            terminalManufacturerCode: $terminalData['terminal_manufacturer_code'] ?? TerminalManufacturerCodeEnum::ENUM_1,
            title: $terminalData['title'],
            serialNumber: $terminalData['serial_number'],
            debit: $terminalData['debit'] ?? false,
            emv: $terminalData['emv'] ?? false,
            cashbackEnable: $terminalData['cashback_enable'] ?? false,
            printEnable: $terminalData['print_enable'] ?? false,
            sigCaptureEnable: $terminalData['sig_capture_enable'] ?? false
        );

        // Add optional fields if provided
        if (isset($terminalData['default_product_transaction_id'])) {
            $builder->defaultProductTransactionId($terminalData['default_product_transaction_id']);
        }
        if (isset($terminalData['terminal_cvm_id'])) {
            $builder->terminalCvmId($terminalData['terminal_cvm_id']);
        }
        if (isset($terminalData['mac_address'])) {
            $builder->macAddress($terminalData['mac_address']);
        }
        if (isset($terminalData['local_ip_address'])) {
            $builder->localIpAddress($terminalData['local_ip_address']);
        }
        if (isset($terminalData['port'])) {
            $builder->port($terminalData['port']);
        }
        if (isset($terminalData['terminal_number'])) {
            $builder->terminalNumber($terminalData['terminal_number']);
        }
        if (isset($terminalData['communication_type'])) {
            $builder->communicationType($terminalData['communication_type']);
        }
        if (isset($terminalData['active'])) {
            $builder->active($terminalData['active']);
        }

        // Header lines
        for ($i = 1; $i <= 5; $i++) {
            if (isset($terminalData["header_line_{$i}"])) {
                $method = "headerLine{$i}";
                $builder->$method($terminalData["header_line_{$i}"]);
            }
        }

        // Trailer lines
        for ($i = 1; $i <= 5; $i++) {
            if (isset($terminalData["trailer_line_{$i}"])) {
                $method = "trailerLine{$i}";
                $builder->$method($terminalData["trailer_line_{$i}"]);
            }
        }

        // Lodging specific fields
        if (isset($terminalData['default_checkin'])) {
            $builder->defaultCheckin($terminalData['default_checkin']);
        }
        if (isset($terminalData['default_checkout'])) {
            $builder->defaultCheckout($terminalData['default_checkout']);
        }
        if (isset($terminalData['default_room_rate'])) {
            $builder->defaultRoomRate($terminalData['default_room_rate']);
        }
        if (isset($terminalData['default_room_number'])) {
            $builder->defaultRoomNumber($terminalData['default_room_number']);
        }

        // Additional optional fields
        if (isset($terminalData['is_provisioned'])) {
            $builder->isProvisioned($terminalData['is_provisioned']);
        }
        if (isset($terminalData['tip_enable'])) {
            $builder->tipEnable($terminalData['tip_enable']);
        }
        if (isset($terminalData['validated_decryption'])) {
            $builder->validatedDecryption($terminalData['validated_decryption']);
        }

        return $this->getClientInstance()
            ->getTerminalsController()
            ->createANewTerminalDevice($builder->build());
    }

    /**
     * Get all terminals for the location
     *
     * @param  array  $options  Optional parameters (page, order, filterBy, expand, etc.)
     *
     * @throws ApiException|Exception
     */
    public function listTerminals(array $options = []): ResponseTerminalsCollection
    {
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

        return $this->getClientInstance()
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
    }

    /**
     * Get a single terminal by ID
     *
     * @param  string  $terminalId  Terminal ID
     * @param  array  $expand  Optional expand parameters
     * @param  array  $fields  Optional fields to return
     *
     * @throws ApiException|Exception
     */
    public function getTerminal(string $terminalId, ?array $expand = null, ?array $fields = null): ResponseTerminal
    {
        return $this->getClientInstance()
            ->getTerminalsController()
            ->viewSingleTerminalsRecord($terminalId, $expand, $fields);
    }

    /**
     * Update an existing terminal
     *
     * @param  string  $terminalId  Terminal ID to update
     * @param  array  $terminalData  Updated terminal data
     * @param  array  $expand  Optional expand parameters
     *
     * @throws ApiException|Exception
     */
    public function updateTerminal(string $terminalId, array $terminalData, ?array $expand = null): ResponseTerminal
    {
        $builder = V1TerminalsRequest1Builder::init();

        // Add provided fields to the builder
        $fieldMapping = [
            'location_id' => 'locationId',
            'default_product_transaction_id' => 'defaultProductTransactionId',
            'terminal_application_id' => 'terminalApplicationId',
            'terminal_cvm_id' => 'terminalCvmId',
            'terminal_manufacturer_code' => 'terminalManufacturerCode',
            'title' => 'title',
            'mac_address' => 'macAddress',
            'local_ip_address' => 'localIpAddress',
            'port' => 'port',
            'serial_number' => 'serialNumber',
            'terminal_number' => 'terminalNumber',
            'default_checkin' => 'defaultCheckin',
            'default_checkout' => 'defaultCheckout',
            'default_room_rate' => 'defaultRoomRate',
            'default_room_number' => 'defaultRoomNumber',
            'debit' => 'debit',
            'emv' => 'emv',
            'cashback_enable' => 'cashbackEnable',
            'print_enable' => 'printEnable',
            'sig_capture_enable' => 'sigCaptureEnable',
            'is_provisioned' => 'isProvisioned',
            'tip_enable' => 'tipEnable',
            'validated_decryption' => 'validatedDecryption',
            'communication_type' => 'communicationType',
            'active' => 'active',
        ];

        foreach ($fieldMapping as $dataKey => $builderMethod) {
            if (isset($terminalData[$dataKey])) {
                $builder->$builderMethod($terminalData[$dataKey]);
            }
        }

        // Handle header lines
        for ($i = 1; $i <= 5; $i++) {
            if (isset($terminalData["header_line_{$i}"])) {
                $method = "headerLine{$i}";
                $builder->$method($terminalData["header_line_{$i}"]);
            }
        }

        // Handle trailer lines
        for ($i = 1; $i <= 5; $i++) {
            if (isset($terminalData["trailer_line_{$i}"])) {
                $method = "trailerLine{$i}";
                $builder->$method($terminalData["trailer_line_{$i}"]);
            }
        }

        return $this->getClientInstance()
            ->getTerminalsController()
            ->updateTerminalRecord($terminalId, $builder->build(), $expand);
    }

    // Helper methods for common terminal operations

    /**
     * Create a simple terminal with minimal required data
     *
     * @param  string  $title  Terminal name/title
     * @param  string  $serialNumber  Terminal serial number
     * @param  string  $terminalApplicationId  Terminal application ID
     * @param  array  $additionalOptions  Optional additional configuration
     *
     * @throws ApiException|Exception
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
            'debit' => false,
            'emv' => true,
            'cashback_enable' => false,
            'print_enable' => false,
            'sig_capture_enable' => false,
            'active' => true,
            'communication_type' => CommunicationTypeEnum::HTTP,
        ], $additionalOptions);

        return $this->createTerminal($terminalData);
    }

    /**
     * Get all active terminals for current location
     *
     * @throws ApiException|Exception
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
     * @param  string  $terminalId  Terminal ID
     * @param  bool  $active  True to activate, false to deactivate
     *
     * @throws ApiException|Exception
     */
    public function setTerminalStatus(string $terminalId, bool $active): ResponseTerminal
    {
        return $this->updateTerminal($terminalId, ['active' => $active]);
    }

    // Terminal Credit Card Processing Methods

    /**
     * Initiate a credit card sale transaction through a terminal
     *
     * @param  string  $terminalId  Terminal ID to process the transaction
     * @param  int  $amount  Transaction amount in cents (e.g., 1099 for $10.99)
     * @param  array  $options  Optional parameters (order_number, customer_id, description, etc.)
     * @return ResponseTransactionProcessing Transaction processing response with async code
     *
     * @throws ApiException|Exception
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

        return $this->getClientInstance()
            ->getTransactionsCreditCardController()
            ->cCSaleTerminal($builder->build());
    }

    /**
     * Check the status of an async terminal transaction
     *
     * @param  string  $statusCode  Async status code from the initial transaction response
     * @return ResponseAsyncStatus Current status of the transaction
     *
     * @throws ApiException|Exception
     */
    public function checkTerminalTransactionStatus(string $statusCode): ResponseAsyncStatus
    {
        return $this->getClientInstance()
            ->getAsyncProcessingController()
            ->statusCheck($statusCode);
    }

    /**
     * Wait for a terminal transaction to complete by polling the status
     *
     * @param  string  $statusCode  Async status code from the initial transaction response
     * @param  int  $timeoutSeconds  Maximum time to wait in seconds (default: 300 = 5 minutes)
     * @param  int  $pollIntervalSeconds  How often to check the status in seconds (default: 2)
     * @return ResponseAsyncStatus Final status when completed or timed out
     *
     * @throws ApiException|Exception
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
                Log::error('Terminal transaction failed', [
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
        Log::warning('Terminal transaction polling timed out', [
            'status_code' => $statusCode,
            'timeout_seconds' => $timeoutSeconds,
            'final_progress' => $finalStatus->getData()->getProgress(),
        ]);

        return $finalStatus;
    }

    /**
     * Process a complete terminal credit card transaction with automatic status monitoring
     *
     * @param  string  $terminalId  Terminal ID to process the transaction
     * @param  int  $amount  Transaction amount in cents (e.g., 1099 for $10.99)
     * @param  array  $options  Optional parameters and polling configuration
     * @return array Result containing final status, transaction ID, and processing info
     *
     * @throws ApiException|Exception
     */
    public function processTerminalCreditCard(string $terminalId, int $amount, array $options = []): array
    {
        // Extract polling options
        $timeoutSeconds = $options['timeout_seconds'] ?? 300;
        $pollIntervalSeconds = $options['poll_interval_seconds'] ?? 2;
        unset($options['timeout_seconds'], $options['poll_interval_seconds']);

        try {
            // Step 1: Initiate the terminal transaction
            Log::info('Initiating terminal credit card transaction', [
                'terminal_id' => $terminalId,
                'amount' => $amount,
                'options' => $options,
            ]);

            $processingResponse = $this->chargeTerminalCreditCard($terminalId, $amount, $options);
            $asyncData = $processingResponse->getData()->getAsync();
            $statusCode = $asyncData->getCode();

            Log::info('Terminal transaction initiated', [
                'status_code' => $statusCode,
                'async_link' => $asyncData->getLink(),
            ]);

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

            Log::info('Terminal transaction processing completed', $result);

            return $result;

        } catch (Exception $e) {
            Log::error('Terminal credit card processing failed', [
                'terminal_id' => $terminalId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
