<?php

namespace Hyrograsper\LunarFortis\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

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

    protected function makeRequest(string $method, string $endpoint, array $data = [], array $queryParams = []): array
    {
        $url = "{$this->baseUrl}{$endpoint}";

        $retryAttempts = config('lunar-fortis.http.retry.attempts', 3);
        $retryDelay = config('lunar-fortis.http.retry.delay', 1000);
        $retryOnConnection = config('lunar-fortis.http.retry.on_connection_error', true);
        $retryStatusCodes = config('lunar-fortis.http.retry.on_status_codes', [429, 502, 503, 504]);
        $exponentialBackoff = config('lunar-fortis.http.retry.exponential_backoff', false);
        $maxDelay = config('lunar-fortis.http.retry.max_delay', 10000);
        $timeout = config('lunar-fortis.http.timeout', 30);

        $delayCallback = $exponentialBackoff
            ? function ($attempt) use ($retryDelay, $maxDelay) {
                $delay = $retryDelay * $attempt;

                return min($delay, $maxDelay);
            }
        : $retryDelay;

        $httpClient = Http::retry($retryAttempts, $delayCallback, function ($exception, $request) use ($retryOnConnection, $retryStatusCodes) {
            if ($retryOnConnection && $exception instanceof ConnectionException) {
                if (config('lunar-fortis.debug')) {
                    Log::debug('LunarFortis: Retrying due to connection error', [
                        'exception' => $exception->getMessage(),
                    ]);
                }

                return true;
            }

            if (method_exists($exception, 'getResponse')) {
                $response = $exception->getResponse();
                if ($response && in_array($response->getStatusCode(), $retryStatusCodes)) {
                    if (config('lunar-fortis.debug')) {
                        Log::debug('LunarFortis: Retrying due to HTTP status code', [
                            'status_code' => $response->getStatusCode(),
                            'configured_codes' => $retryStatusCodes,
                            'response_preview' => substr($response->getBody()->getContents(), 0, 200),
                        ]);
                    }

                    return true;
                }
            }

            return false;
        })
            ->withHeaders($this->headers)
            ->timeout($timeout);

        $response = match ($method) {
            'GET' => $httpClient->get($url, $queryParams),
            'POST' => $httpClient->post($url, $data),
            'PUT' => $httpClient->put($url, $data),
            'PATCH' => $httpClient->patch($url, $data),
            'DELETE' => $httpClient->delete($url),
            default => throw new InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };

        return $response->throw()->json();
    }

    public function createTransactionIntention(int $amount, string $action = 'sale'): array
    {
        $data = [
            'action' => $action,
            'digitalWalletsOnly' => false,
            'methods' => [
                [
                    'type' => 'cc',
                    'product_transaction_id' => config('services.fortis.productTransactionId'),
                ],
            ],
            'amount' => $amount,
            'location_id' => config('services.fortis.locationId'),
        ];

        try {
            $response = $this->makeRequest('POST', '/v1/elements/transaction/intention', $data);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Transaction intention created successfully', [
                    'amount' => $amount,
                    'action' => $action,
                ]);
            }

            return $response;
        } catch (RequestException $exception) {
            $responseBody = $exception->response->body();
            Log::error('LunarFortis: Failed to create transaction intention', [
                'amount' => $amount,
                'action' => $action,
                'status_code' => $exception->response->status(),
                'error' => $exception->getMessage(),
                'response' => $responseBody,
                'request_data' => $data,
            ]);

            $errorDetail = '';
            if ($responseBody) {
                $errorData = json_decode($responseBody, true);
                if (is_array($errorData) && isset($errorData['detail'])) {
                    $errorDetail = " - {$errorData['detail']}";
                }
            }

            throw new Exception("Failed to create transaction intention: {$exception->getMessage()}{$errorDetail}");
        }
    }

    public function completeAuthorizedTransaction(string $transactionId, int $amount, array $options = []): array
    {
        try {
            $data = [
                'location_id' => $options['location_id'] ?? config('services.fortis.locationId'),
                'transaction_amount' => $amount,
            ];

            $optionalFields = [
                'order_number', 'customer_id', 'transaction_api_id', 'po_number', 'clerk_number',
                'contact_api_id', 'contact_id', 'location_api_id', 'product_transaction_id', 'quick_invoice_id',
                'secondary_amount', 'subtotal_amount', 'surcharge_amount', 'tax', 'tip_amount',
                'checkin_date', 'checkout_date',
                'save_account', 'save_account_title',
                'billing_address', 'additional_amounts', 'identity_verification',
                'custom_data', 'transaction_c1', 'transaction_c2', 'transaction_c3',
                'installment', 'installment_number', 'installment_count', 'installment_counter',
                'installment_total', 'recurring', 'recurring_flag', 'recurring_number',
                'subscription', 'standing_order',
                'room_num', 'room_rate', 'advance_deposit', 'no_show', 'mini_bar',
                'image_front', 'image_back',
                'bank_funded_only_override', 'allow_partial_authorization_override',
                'auto_decline_cvv_override', 'auto_decline_street_override', 'auto_decline_zip_override',
                'description', 'notification_email_address', 'tags', 'iias_ind',
                'ebt_type', 'currency_code', 'deferred_auth',
            ];

            foreach ($optionalFields as $field) {
                if (isset($options[$field])) {
                    if ($field === 'customer_id') {
                        $data[$field] = (string) $options[$field];
                    } else {
                        $data[$field] = $options[$field];
                    }
                }
            }

            $response = $this->makeRequest('PATCH', "/v1/transactions/{$transactionId}/auth-complete", $data);

            if (config('lunar-fortis.debug')) {
                Log::debug('LunarFortis: Transaction authorization completed successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                ]);
            }

            return $response;
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to complete authorized transaction', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to complete authorized transaction: {$exception->getMessage()}");
        }
    }

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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to authorize credit card from token', [
                'token_id' => $tokenId,
                'amount' => $amount,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to authorize credit card from token: {$exception->getMessage()}");
        }
    }

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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to process refund', [
                'previous_transaction_id' => $previousTransactionId,
                'amount' => $amount,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to process refund: {$exception->getMessage()}");
        }
    }

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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to retrieve transaction', [
                'transaction_id' => $transactionId,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to retrieve transaction: {$exception->getMessage()}");
        }
    }

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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to create terminal', [
                'terminal_data' => $terminalData,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to create terminal: {$exception->getMessage()}");
        }
    }

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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to list terminals', [
                'options' => $options,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to list terminals: {$exception->getMessage()}");
        }
    }

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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to retrieve terminal', [
                'terminal_id' => $terminalId,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to retrieve terminal {$terminalId}: {$exception->getMessage()}");
        }
    }

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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to update terminal', [
                'terminal_id' => $terminalId,
                'terminal_data' => $terminalData,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to update terminal {$terminalId}: {$exception->getMessage()}");
        }
    }

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

            $terminalProductId = config('services.fortis.terminalProductTransactionId');
            if ($terminalProductId) {
                $data['product_transaction_id'] = $terminalProductId;
            } elseif (! isset($options['product_transaction_id'])) {
                $data['product_transaction_id'] = config('services.fortis.productTransactionId');
            }

            $optionalFields = [
                'order_number', 'customer_id', 'contact_id', 'description', 'clerk_number',
                'tip_amount', 'tax', 'subtotal_amount', 'surcharge_amount', 'transaction_api_id',
                'po_number', 'notification_email_address', 'save_account', 'save_account_title',
                'product_transaction_id', 'checkin_date', 'checkout_date', 'room_num', 'room_rate',
                'billing_address', 'cardholder_present', 'currency_code', 'terminal_api_id',
                'e_format', 'e_track_data', 'e_serial_number', 'additional_amounts',
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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to authorize terminal credit card', [
                'terminal_id' => $terminalId,
                'amount' => $amount,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to authorize terminal credit card: {$exception->getMessage()}");
        }
    }

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
        } catch (RequestException $exception) {
            Log::error('LunarFortis: Failed to check async status', [
                'status_code' => $statusCode,
                'error' => $exception->getMessage(),
                'response' => $exception->response->body(),
            ]);

            throw new Exception("Failed to check async status: {$exception->getMessage()}");
        }
    }
}
