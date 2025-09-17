<?php

namespace Hyrograsper\LunarFortis\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FortisHttpService
{
    protected string $baseUrl;
    protected array $headers;

    public function __construct()
    {
        $this->baseUrl = config('lunar-fortis.environment') === 'production'
            ? 'https://api.fortis.tech'
            : 'https://api.sandbox.fortis.tech';

        $this->headers = [
            'Content-Type' => 'application/json',
            'user-id' => config('services.fortis.userId'),
            'user-api-key' => config('services.fortis.userApiKey'),
            'developer-id' => config('services.fortis.developerId'),
        ];
    }

    /**
     * Make HTTP request with retry logic
     */
    protected function makeRequest(string $method, string $endpoint, array $data = [], array $queryParams = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $httpClient = Http::retry(3, 1000, function ($exception, $request) {
            // Retry on network errors or 429 rate limiting
            if ($exception instanceof ConnectionException) {
                if (config('lunar-fortis.debug')) {
                    Log::debug('LunarFortis: Retrying due to connection error', [
                        'exception' => $exception->getMessage(),
                    ]);
                }
                return true;
            }

            // Check if we have a response from the exception
            if ($exception && method_exists($exception, 'getResponse')) {
                $response = $exception->getResponse();
                if ($response && $response->getStatusCode() === 429) {
                    if (config('lunar-fortis.debug')) {
                        Log::debug('LunarFortis: Retrying due to rate limiting (429)', [
                            'response' => $response->getBody()->getContents(),
                        ]);
                    }
                    return true;
                }
            }

            return false;
        })
        ->withHeaders($this->headers)
        ->timeout(30);

        $response = match ($method) {
            'GET' => $httpClient->get($url, $queryParams),
            'POST' => $httpClient->post($url, $data),
            'PUT' => $httpClient->put($url, $data),
            'PATCH' => $httpClient->patch($url, $data),
            'DELETE' => $httpClient->delete($url),
        };

        return $response->throw()->json();
    }

    /**
     * Create transaction intention for Elements
     */
    public function createTransactionIntention(int $amount, string $action = 'sale'): array
    {
        try {
            $data = [
                'action' => $action,
                'digitalWalletsOnly' => false,
                'methods' => [
                    [
                        'type' => 'cc',
                        'product_transaction_id' => config('services.fortis.productTransactionId'),
                    ]
                ],
                'amount' => $amount,
                'location_id' => config('services.fortis.locationId'),
            ];

            $response = $this->makeRequest('POST', '/v1/elements/transaction/intention', $data);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Transaction intention created successfully', [
                    'amount' => $amount,
                    'action' => $action,
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            $responseBody = $e->response?->body();
            Log::error('LunarFortis: Failed to create transaction intention', [
                'amount' => $amount,
                'action' => $action,
                'status_code' => $e->response?->status(),
                'error' => $e->getMessage(),
                'response' => $responseBody,
                'request_data' => $data,
            ]);

            $errorDetail = '';
            if ($responseBody) {
                $errorData = json_decode($responseBody, true);
                if ($errorData && isset($errorData['detail'])) {
                    $errorDetail = ' - ' . $errorData['detail'];
                }
            }

            throw new Exception("Failed to create transaction intention: {$e->getMessage()}{$errorDetail}");
        }
    }

    /**
     * Complete authorized transaction
     */
    public function completeAuthorizedTransaction(string $transactionId, int $amount, ?string $orderNumber = null, ?string $customerId = null): array
    {
        try {
            $data = [
                'location_id' => config('services.fortis.locationId'),
                'transaction_amount' => $amount,
            ];

            if ($orderNumber) {
                $data['order_number'] = $orderNumber;
            }

            if ($customerId) {
                $data['customer_id'] = $customerId;
            }

            $response = $this->makeRequest('PATCH', "/v1/transactions/{$transactionId}/auth-complete", $data);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Transaction authorization completed successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to complete authorized transaction', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to complete authorized transaction: {$e->getMessage()}");
        }
    }

    /**
     * Authorize credit card from token
     */
    public function authorizeCcFromToken(string $tokenId, int $amount, array $options = []): array
    {
        try {
            $data = [
                'transaction_amount' => $amount,
                'token_id' => $tokenId,
            ];

            if (isset($options['order_number'])) {
                $data['order_number'] = $options['order_number'];
            }

            if (isset($options['customer_id'])) {
                $data['customer_id'] = $options['customer_id'];
            }

            if (isset($options['billing_address'])) {
                $data['billing_address'] = $options['billing_address'];
            }

            $response = $this->makeRequest('POST', '/v1/transactions/cc/auth-only/token', $data);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Credit card authorization from token completed successfully', [
                    'token_id' => $tokenId,
                    'amount' => $amount,
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to authorize credit card from token', [
                'token_id' => $tokenId,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to authorize credit card from token: {$e->getMessage()}");
        }
    }

    /**
     * Process refund
     */
    public function refund(string $previousTransactionId, int $amount): array
    {
        try {
            $data = [
                'transaction_amount' => $amount,
                'location_id' => config('services.fortis.locationId'),
            ];

            $response = $this->makeRequest('PATCH', "/v1/transactions/{$previousTransactionId}/refund", $data);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Refund completed successfully', [
                    'previous_transaction_id' => $previousTransactionId,
                    'amount' => $amount,
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to process refund', [
                'previous_transaction_id' => $previousTransactionId,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to process refund: {$e->getMessage()}");
        }
    }

    /**
     * Get transaction by ID
     */
    public function getTransaction(string $transactionId): array
    {
        try {
            $response = $this->makeRequest('GET', "/v1/transactions/{$transactionId}");

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Transaction retrieved successfully', [
                    'transaction_id' => $transactionId,
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to retrieve transaction', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to retrieve transaction: {$e->getMessage()}");
        }
    }

    // Terminal Management Methods

    /**
     * Create terminal
     */
    public function createTerminal(array $terminalData): array
    {
        try {
            $data = [
                'location_id' => $terminalData['location_id'] ?? config('services.fortis.locationId'),
                'terminal_application_id' => $terminalData['terminal_application_id'],
                'terminal_manufacturer_code' => $terminalData['terminal_manufacturer_code'] ?? 1,
                'title' => $terminalData['title'],
                'serial_number' => $terminalData['serial_number'],
            ];

            // Add optional fields
            if (isset($terminalData['default_product_transaction_id'])) {
                $data['default_product_transaction_id'] = $terminalData['default_product_transaction_id'];
            }
            if (isset($terminalData['active'])) {
                $data['active'] = $terminalData['active'];
            }

            $response = $this->makeRequest('POST', '/v1/terminals', $data);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal created successfully', [
                    'title' => $terminalData['title'],
                    'serial_number' => $terminalData['serial_number'],
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to create terminal', [
                'terminal_data' => $terminalData,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to create terminal: {$e->getMessage()}");
        }
    }

    /**
     * List terminals
     */
    public function listTerminals(array $options = []): array
    {
        try {
            $queryParams = [];

            if (isset($options['page'])) {
                $queryParams['page'] = $options['page'];
            }

            if (isset($options['order'])) {
                foreach ($options['order'] as $index => $orderItem) {
                    $queryParams["order[{$index}][key]"] = $orderItem['key'];
                    $queryParams["order[{$index}][operator]"] = $orderItem['operator'];
                }
            }

            if (isset($options['filterBy'])) {
                foreach ($options['filterBy'] as $index => $filter) {
                    $queryParams["filter[{$index}][key]"] = $filter['key'];
                    $queryParams["filter[{$index}][operator]"] = $filter['operator'];
                    $queryParams["filter[{$index}][value]"] = $filter['value'];
                }
            }

            $response = $this->makeRequest('GET', '/v1/terminals', [], $queryParams);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminals listed successfully', [
                    'total_count' => count($response['list'] ?? []),
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to list terminals', [
                'options' => $options,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to list terminals: {$e->getMessage()}");
        }
    }

    /**
     * Get single terminal
     */
    public function getTerminal(string $terminalId, ?array $expand = null, ?array $fields = null): array
    {
        try {
            $queryParams = [];
            if ($expand) {
                $queryParams['expand'] = implode(',', $expand);
            }
            if ($fields) {
                $queryParams['fields'] = implode(',', $fields);
            }

            $response = $this->makeRequest('GET', "/v1/terminals/{$terminalId}", [], $queryParams);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal retrieved successfully', [
                    'terminal_id' => $terminalId,
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to retrieve terminal', [
                'terminal_id' => $terminalId,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to retrieve terminal {$terminalId}: {$e->getMessage()}");
        }
    }

    /**
     * Update terminal
     */
    public function updateTerminal(string $terminalId, array $terminalData, ?array $expand = null): array
    {
        try {
            $queryParams = [];
            if ($expand) {
                $queryParams['expand'] = implode(',', $expand);
            }

            $response = $this->makeRequest('PATCH', "/v1/terminals/{$terminalId}", $terminalData, $queryParams);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal updated successfully', [
                    'terminal_id' => $terminalId,
                    'updated_fields' => array_keys($terminalData),
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to update terminal', [
                'terminal_id' => $terminalId,
                'terminal_data' => $terminalData,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to update terminal {$terminalId}: {$e->getMessage()}");
        }
    }

    // Terminal Transaction Methods

    /**
     * Authorize terminal credit card (auth-only)
     */
    public function authorizeTerminalCreditCard(string $terminalId, int $amount, array $options = []): array
    {
        try {
            $data = [
                'location_id' => $options['location_id'] ?? config('services.fortis.locationId'),
                'transaction_amount' => $amount,
                'terminal_id' => $terminalId,
                'card_present' => true,
                'cardholder_present' => true,
            ];

            // Handle product_transaction_id for terminal transactions
            // Check if a specific terminal product ID is configured, otherwise use the default ecommerce one
            $terminalProductId = config('services.fortis.terminalProductTransactionId');
            if ($terminalProductId) {
                $data['product_transaction_id'] = $terminalProductId;
            } elseif (!isset($options['product_transaction_id'])) {
                // Fallback to ecommerce product ID if no terminal-specific ID and none provided in options
                $data['product_transaction_id'] = config('services.fortis.productTransactionId');
            }

            // Add optional fields
            $optionalFields = [
                'order_number', 'customer_id', 'contact_id', 'description', 'clerk_number',
                'tip_amount', 'tax', 'subtotal_amount', 'surcharge_amount', 'transaction_api_id',
                'po_number', 'notification_email_address', 'save_account', 'save_account_title',
                'product_transaction_id', 'checkin_date', 'checkout_date', 'room_num', 'room_rate',
                'billing_address', 'cardholder_present', 'currency_code', 'terminal_api_id',
                'e_format', 'e_track_data', 'e_serial_number', 'additional_amounts'
            ];

            foreach ($optionalFields as $field) {
                if (isset($options[$field])) {
                    $data[$field] = $options[$field];
                }
            }

            $response = $this->makeRequest('POST', '/v1/transactions/cc/auth-only/terminal', $data);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Terminal credit card authorization initiated successfully', [
                    'terminal_id' => $terminalId,
                    'amount' => $amount,
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to authorize terminal credit card', [
                'terminal_id' => $terminalId,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to authorize terminal credit card: {$e->getMessage()}");
        }
    }

    /**
     * Check async status
     */
    public function checkAsyncStatus(string $statusCode): array
    {
        try {
            $response = $this->makeRequest('GET', "/v1/async/status/{$statusCode}");

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Async status checked', [
                    'status_code' => $statusCode,
                    'progress' => $response['data']['progress'] ?? null,
                ]);
            }

            return $response;
        } catch (RequestException $e) {
            Log::error('LunarFortis: Failed to check async status', [
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception("Failed to check async status: {$e->getMessage()}");
        }
    }
}
