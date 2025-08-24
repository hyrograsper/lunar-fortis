<?php

namespace Hyrograsper\LunarFortis\Models;

use Carbon\Carbon;
use Exception;
use FortisAPILib\Exceptions\ApiException;
use FortisAPILib\Models\TerminalManufacturerCodeEnum;
use Hyrograsper\LunarFortis\LunarFortis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class Terminal extends Model
{
    use HasFactory;

    protected $table = 'fortis_terminals';

    protected $fillable = [
        'fortis_id',
        'location_id',
        'title',
        'serial_number',
        'terminal_application_id',
        'terminal_manufacturer_code',
        'default_product_transaction_id',
        'active',
        'fortis_created_at',
        'fortis_modified_at',
        'created_user_id',
        'modified_user_id',
        'synced_at',
        'fortis_data',
    ];

    protected $casts = [
        'active' => 'boolean',
        'fortis_created_at' => 'timestamp',
        'fortis_modified_at' => 'timestamp',
        'synced_at' => 'timestamp',
        'fortis_data' => 'array',
    ];

    // Scopes for common queries
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForLocation($query, string $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeByManufacturer($query, string $manufacturerCode)
    {
        return $query->where('terminal_manufacturer_code', $manufacturerCode);
    }

    // Accessor for display name
    public function getDisplayNameAttribute(): string
    {
        return $this->title.' ('.$this->serial_number.')';
    }


    // Check if terminal needs sync (hasn't been synced recently)
    public function needsSync(?int $hoursThreshold = 24): bool
    {
        if (! $this->synced_at) {
            return true;
        }

        return $this->synced_at->diffInHours(now()) > $hoursThreshold;
    }

    // Mark as synced
    public function markSynced(): void
    {
        $this->update(['synced_at' => now()]);
    }

    /**
     * Sync all terminals from Fortis API
     *
     * @param  LunarFortis|null  $fortisClient  Optional Fortis client instance
     * @return array Statistics about the sync operation
     *
     * @throws Exception
     */
    public static function syncFromFortis(?LunarFortis $fortisClient = null): array
    {
        $fortisClient = $fortisClient ?? app(LunarFortis::class);

        $stats = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'total_processed' => 0,
        ];

        try {
            // Fetch all terminals from Fortis API
            $response = $fortisClient->listTerminals();
            $terminals = $response->getList() ?? [];

            foreach ($terminals as $terminalData) {
                $stats['total_processed']++;

                try {
                    // For list responses, the terminal data is directly in the list item
                    $data = $terminalData;

                    // Extract terminal data
                    $terminalAttributes = static::mapFortisDataToAttributes($data);

                    // Update or create terminal
                    $terminal = static::updateOrCreate(
                        ['fortis_id' => $terminalAttributes['fortis_id']],
                        $terminalAttributes
                    );

                    // Mark as synced
                    $terminal->markSynced();

                    if ($terminal->wasRecentlyCreated) {
                        $stats['created']++;
                    } else {
                        $stats['updated']++;
                    }

                } catch (Exception $e) {
                    $stats['errors']++;
                    Log::error('Failed to sync terminal: '.$e->getMessage(), [
                        'terminal_data' => $terminalData ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (Exception $e) {
            Log::error('Failed to fetch terminals from Fortis API: '.$e->getMessage());
            throw $e;
        }

        return $stats;
    }

    // Payment Processing Helper Methods

    /**
     * Process a credit card payment using this terminal
     *
     * @param  int  $amount  Amount in cents (e.g., 1099 for $10.99)
     * @param  array  $options  Additional transaction options
     * @return array Transaction result with status and details
     *
     * @throws Exception
     */
    public function processPayment(int $amount, array $options = []): array
    {
        if (! $this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        try {
            $fortis = app(LunarFortis::class);

            return $fortis->processTerminalCreditCard(
                terminalId: $this->fortis_id,
                amount: $amount,
                options: $options
            );
        } catch (ApiException|Exception $e) {
            Log::error('Terminal payment processing failed', [
                'terminal_id' => $this->fortis_id,
                'terminal_title' => $this->title,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Payment processing failed: {$e->getMessage()}");
        }
    }

    /**
     * Initiate a payment and return async status code for manual monitoring
     *
     * @param  int  $amount  Amount in cents
     * @param  array  $options  Additional transaction options
     * @return string Async status code for monitoring
     *
     * @throws Exception
     */
    public function initiatePayment(int $amount, array $options = []): string
    {
        if (! $this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        try {
            $fortis = app(LunarFortis::class);

            $response = $fortis->chargeTerminalCreditCard(
                terminalId: $this->fortis_id,
                amount: $amount,
                options: $options
            );

            return $response->getData()->getAsync()->getCode();
        } catch (ApiException|Exception $e) {
            Log::error('Terminal payment initiation failed', [
                'terminal_id' => $this->fortis_id,
                'terminal_title' => $this->title,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Payment initiation failed: {$e->getMessage()}");
        }
    }

    /**
     * Check the status of a payment by async status code
     *
     * @param  string  $statusCode  Async status code from initiation
     * @return array Status information
     *
     * @throws Exception
     */
    public function checkPaymentStatus(string $statusCode): array
    {
        try {
            $fortis = app(LunarFortis::class);
            $status = $fortis->checkTerminalTransactionStatus($statusCode);
            $statusData = $status->getData();

            return [
                'progress' => $statusData->getProgress(),
                'completed' => $statusData->getProgress() >= 100,
                'success' => $statusData->getProgress() >= 100 && ! $statusData->getError(),
                'error' => $statusData->getError(),
                'transaction_id' => $statusData->getId(),
                'type' => $statusData->getType(),
                'ttl' => $statusData->getTtl(),
            ];
        } catch (ApiException|Exception $e) {
            Log::error('Terminal payment status check failed', [
                'terminal_id' => $this->fortis_id,
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Payment status check failed: {$e->getMessage()}");
        }
    }

    /**
     * Wait for a payment to complete
     *
     * @param  string  $statusCode  Async status code from initiation
     * @param  int  $timeoutSeconds  Maximum wait time (default: 300 seconds)
     * @param  int  $pollIntervalSeconds  Polling interval (default: 2 seconds)
     * @return array Final status information
     *
     * @throws Exception
     */
    public function waitForPayment(
        string $statusCode,
        int $timeoutSeconds = 300,
        int $pollIntervalSeconds = 2
    ): array {
        try {
            $fortis = app(LunarFortis::class);
            $status = $fortis->waitForTerminalTransaction($statusCode, $timeoutSeconds, $pollIntervalSeconds);
            $statusData = $status->getData();

            return [
                'progress' => $statusData->getProgress(),
                'completed' => $statusData->getProgress() >= 100,
                'success' => $statusData->getProgress() >= 100 && ! $statusData->getError(),
                'error' => $statusData->getError(),
                'transaction_id' => $statusData->getId(),
                'type' => $statusData->getType(),
                'ttl' => $statusData->getTtl(),
                'timed_out' => $statusData->getProgress() < 100 && ! $statusData->getError(),
            ];
        } catch (ApiException|Exception $e) {
            Log::error('Terminal payment wait failed', [
                'terminal_id' => $this->fortis_id,
                'status_code' => $statusCode,
                'error' => $e->getMessage(),
            ]);
            throw new Exception("Payment wait failed: {$e->getMessage()}");
        }
    }

    /**
     * Process a payment with tip support
     *
     * @param  int  $amount  Base amount in cents
     * @param  int  $tipAmount  Tip amount in cents (default: 0)
     * @param  array  $options  Additional options
     * @return array Transaction result
     *
     * @throws Exception
     */
    public function processPaymentWithTip(int $amount, int $tipAmount = 0, array $options = []): array
    {
        if (! $this->tip_enable && $tipAmount > 0) {
            throw new Exception("Terminal {$this->fortis_id} does not support tips");
        }

        $options['tip_amount'] = $tipAmount;

        return $this->processPayment($amount, $options);
    }

    /**
     * Process a lodging payment with room details
     *
     * @param  int  $amount  Amount in cents
     * @param  string  $roomNumber  Room number
     * @param  int  $roomRate  Room rate in cents
     * @param  string  $checkinDate  Check-in date (YYYY-MM-DD)
     * @param  string  $checkoutDate  Check-out date (YYYY-MM-DD)
     * @param  array  $options  Additional options
     * @return array Transaction result
     *
     * @throws Exception
     */
    public function processLodgingPayment(
        int $amount,
        string $roomNumber,
        int $roomRate,
        string $checkinDate,
        string $checkoutDate,
        array $options = []
    ): array {
        $lodgingOptions = array_merge($options, [
            'room_num' => $roomNumber,
            'room_rate' => $roomRate,
            'checkin_date' => $checkinDate,
            'checkout_date' => $checkoutDate,
        ]);

        return $this->processPayment($amount, $lodgingOptions);
    }

    /**
     * Check if terminal is ready for payments
     *
     * @return bool True if terminal can process payments
     */
    public function isReadyForPayments(): bool
    {
        return $this->active;
    }

    /**
     * Sync a single terminal from Fortis API by ID
     *
     * @param  string  $fortisId  Fortis terminal ID
     * @param  LunarFortis|null  $fortisClient  Optional Fortis client instance
     * @return static|null The synced terminal or null if not found
     *
     * @throws Exception
     */
    public static function syncSingleFromFortis(string $fortisId, ?LunarFortis $fortisClient = null): ?static
    {
        $fortisClient = $fortisClient ?? app(LunarFortis::class);

        try {
            // Fetch single terminal from Fortis API
            $response = $fortisClient->getTerminal($fortisId);
            $data = $response->getData();

            if (! $data) {
                return null;
            }

            // Extract terminal data
            $terminalAttributes = static::mapFortisDataToAttributes($data);

            // Update or create terminal
            $terminal = static::updateOrCreate(
                ['fortis_id' => $terminalAttributes['fortis_id']],
                $terminalAttributes
            );

            // Mark as synced
            $terminal->markSynced();

            return $terminal;

        } catch (Exception $e) {
            Log::error("Failed to sync terminal {$fortisId}: ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Map Fortis API response data to model attributes
     *
     * @param  object  $data  Fortis terminal data object
     * @return array Mapped attributes for the model
     */
    /**
     * Get validation rules for Terminal fields
     *
     * @return array Validation rules using Fortis-allowed values
     */
    public static function getValidationRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:255'],
            'location_id' => ['nullable', 'string', 'max:255'],
            'terminal_application_id' => ['nullable', 'string', 'max:255'],
            'terminal_manufacturer_code' => [
                'nullable',
                'string',
                Rule::in([
                    TerminalManufacturerCodeEnum::ENUM_1,
                    TerminalManufacturerCodeEnum::ENUM_2,
                    TerminalManufacturerCodeEnum::ENUM_4,
                    TerminalManufacturerCodeEnum::ENUM_100,
                ]),
            ],
            'default_product_transaction_id' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ];
    }

    /**
     * Get the allowed values for terminal manufacturer codes
     */
    public static function getAllowedManufacturerCodes(): array
    {
        return [
            TerminalManufacturerCodeEnum::ENUM_1,
            TerminalManufacturerCodeEnum::ENUM_2,
            TerminalManufacturerCodeEnum::ENUM_4,
            TerminalManufacturerCodeEnum::ENUM_100,
        ];
    }

    /**
     * Get display names for manufacturer codes
     */
    public static function getManufacturerCodeLabels(): array
    {
        return [
            TerminalManufacturerCodeEnum::ENUM_1 => 'Manufacturer 1',
            TerminalManufacturerCodeEnum::ENUM_2 => 'Manufacturer 2',
            TerminalManufacturerCodeEnum::ENUM_4 => 'Manufacturer 4',
            TerminalManufacturerCodeEnum::ENUM_100 => 'Manufacturer 100',
        ];
    }

    protected static function mapFortisDataToAttributes($data): array
    {
        return [
            'fortis_id' => $data->getId(),
            'location_id' => $data->getLocationId(),
            'title' => $data->getTitle(),
            'serial_number' => $data->getSerialNumber(),
            'terminal_application_id' => $data->getTerminalApplicationId(),
            'terminal_manufacturer_code' => (string) $data->getTerminalManufacturerCode(),
            'default_product_transaction_id' => $data->getDefaultProductTransactionId(),
            'active' => (bool) $data->getActive(),
            'fortis_created_at' => $data->getCreatedTs() ? Carbon::createFromTimestamp($data->getCreatedTs()) : null,
            'fortis_modified_at' => $data->getModifiedTs() ? Carbon::createFromTimestamp($data->getModifiedTs()) : null,
            'created_user_id' => $data->getCreatedUserId(),
            'modified_user_id' => $data->getModifiedUserId(),
            'fortis_data' => json_decode(json_encode($data), true), // Store full response for reference
        ];
    }
}
