<?php

namespace Hyrograsper\LunarFortis\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Hyrograsper\LunarFortis\LunarFortis;
use FortisAPILib\Exceptions\ApiException;
use FortisAPILib\Models\CommunicationTypeEnum;
use FortisAPILib\Models\TerminalManufacturerCodeEnum;
use Illuminate\Validation\Rule;
use Exception;

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
        'terminal_cvm_id',
        'terminal_manufacturer_code',
        'default_product_transaction_id',
        'mac_address',
        'local_ip_address',
        'port',
        'terminal_number',
        'communication_type',
        'debit',
        'emv',
        'cashback_enable',
        'print_enable',
        'sig_capture_enable',
        'tip_enable',
        'is_provisioned',
        'validated_decryption',
        'active',
        'header_line_1',
        'header_line_2',
        'header_line_3',
        'header_line_4',
        'header_line_5',
        'trailer_line_1',
        'trailer_line_2',
        'trailer_line_3',
        'trailer_line_4',
        'trailer_line_5',
        'default_checkin',
        'default_checkout',
        'default_room_rate',
        'default_room_number',
        'fortis_created_at',
        'fortis_modified_at',
        'last_registration_ts',
        'created_user_id',
        'modified_user_id',
        'synced_at',
        'fortis_data',
    ];

    protected $casts = [
        'debit' => 'boolean',
        'emv' => 'boolean',
        'cashback_enable' => 'boolean',
        'print_enable' => 'boolean',
        'sig_capture_enable' => 'boolean',
        'tip_enable' => 'boolean',
        'is_provisioned' => 'boolean',
        'validated_decryption' => 'boolean',
        'active' => 'boolean',
        'port' => 'integer',
        'default_room_rate' => 'integer',
        'default_checkin' => 'date',
        'default_checkout' => 'date',
        'fortis_created_at' => 'timestamp',
        'fortis_modified_at' => 'timestamp',
        'last_registration_ts' => 'timestamp',
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

    public function scopeWithCapability($query, string $capability)
    {
        return $query->where($capability, true);
    }

    // Accessor for display name
    public function getDisplayNameAttribute(): string
    {
        return $this->title . ' (' . $this->serial_number . ')';
    }

    // Check if terminal has specific capability
    public function hasCapability(string $capability): bool
    {
        return match ($capability) {
            'debit' => $this->debit,
            'emv' => $this->emv,
            'cashback' => $this->cashback_enable,
            'print' => $this->print_enable,
            'signature' => $this->sig_capture_enable,
            'tip' => $this->tip_enable,
            default => false,
        };
    }

    // Get all terminal capabilities as array
    public function getCapabilitiesAttribute(): array
    {
        $capabilities = [];

        if ($this->debit) $capabilities[] = 'debit';
        if ($this->emv) $capabilities[] = 'emv';
        if ($this->cashback_enable) $capabilities[] = 'cashback';
        if ($this->print_enable) $capabilities[] = 'print';
        if ($this->sig_capture_enable) $capabilities[] = 'signature';
        if ($this->tip_enable) $capabilities[] = 'tip';

        return $capabilities;
    }

    // Get receipt header lines as array (non-empty only)
    public function getHeaderLinesAttribute(): array
    {
        return array_filter([
            $this->header_line_1,
            $this->header_line_2,
            $this->header_line_3,
            $this->header_line_4,
            $this->header_line_5,
        ]);
    }

    // Get receipt trailer lines as array (non-empty only)
    public function getTrailerLinesAttribute(): array
    {
        return array_filter([
            $this->trailer_line_1,
            $this->trailer_line_2,
            $this->trailer_line_3,
            $this->trailer_line_4,
            $this->trailer_line_5,
        ]);
    }

    // Check if terminal needs sync (hasn't been synced recently)
    public function needsSync(?int $hoursThreshold = 24): bool
    {
        if (!$this->synced_at) {
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
     * @param \Hyrograsper\LunarFortis\LunarFortis|null $fortisClient Optional Fortis client instance
     * @return array Statistics about the sync operation
     * @throws \Exception
     */
    public static function syncFromFortis(?\Hyrograsper\LunarFortis\LunarFortis $fortisClient = null): array
    {
        $fortisClient = $fortisClient ?? app(\Hyrograsper\LunarFortis\LunarFortis::class);

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

                } catch (\Exception $e) {
                    $stats['errors']++;
                    \Log::error("Failed to sync terminal: " . $e->getMessage(), [
                        'terminal_data' => $terminalData ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error("Failed to fetch terminals from Fortis API: " . $e->getMessage());
            throw $e;
        }

        return $stats;
    }

    // Payment Processing Helper Methods

    /**
     * Process a credit card payment using this terminal
     * 
     * @param int $amount Amount in cents (e.g., 1099 for $10.99)
     * @param array $options Additional transaction options
     * @return array Transaction result with status and details
     * @throws Exception
     */
    public function processPayment(int $amount, array $options = []): array
    {
        if (!$this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        if (!$this->is_provisioned) {
            throw new Exception("Terminal {$this->fortis_id} is not provisioned");
        }

        try {
            $fortis = app(LunarFortis::class);
            
            return $fortis->processTerminalCreditCard(
                terminalId: $this->fortis_id,
                amount: $amount,
                options: $options
            );
        } catch (ApiException|Exception $e) {
            \Log::error("Terminal payment processing failed", [
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
     * @param int $amount Amount in cents
     * @param array $options Additional transaction options
     * @return string Async status code for monitoring
     * @throws Exception
     */
    public function initiatePayment(int $amount, array $options = []): string
    {
        if (!$this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        if (!$this->is_provisioned) {
            throw new Exception("Terminal {$this->fortis_id} is not provisioned");
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
            \Log::error("Terminal payment initiation failed", [
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
     * @param string $statusCode Async status code from initiation
     * @return array Status information
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
                'success' => $statusData->getProgress() >= 100 && !$statusData->getError(),
                'error' => $statusData->getError(),
                'transaction_id' => $statusData->getId(),
                'type' => $statusData->getType(),
                'ttl' => $statusData->getTtl(),
            ];
        } catch (ApiException|Exception $e) {
            \Log::error("Terminal payment status check failed", [
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
     * @param string $statusCode Async status code from initiation
     * @param int $timeoutSeconds Maximum wait time (default: 300 seconds)
     * @param int $pollIntervalSeconds Polling interval (default: 2 seconds)
     * @return array Final status information
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
                'success' => $statusData->getProgress() >= 100 && !$statusData->getError(),
                'error' => $statusData->getError(),
                'transaction_id' => $statusData->getId(),
                'type' => $statusData->getType(),
                'ttl' => $statusData->getTtl(),
                'timed_out' => $statusData->getProgress() < 100 && !$statusData->getError(),
            ];
        } catch (ApiException|Exception $e) {
            \Log::error("Terminal payment wait failed", [
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
     * @param int $amount Base amount in cents
     * @param int $tipAmount Tip amount in cents (default: 0)
     * @param array $options Additional options
     * @return array Transaction result
     * @throws Exception
     */
    public function processPaymentWithTip(int $amount, int $tipAmount = 0, array $options = []): array
    {
        if (!$this->tip_enable && $tipAmount > 0) {
            throw new Exception("Terminal {$this->fortis_id} does not support tips");
        }

        $options['tip_amount'] = $tipAmount;
        
        return $this->processPayment($amount, $options);
    }

    /**
     * Process a lodging payment with room details
     * 
     * @param int $amount Amount in cents
     * @param string $roomNumber Room number
     * @param int $roomRate Room rate in cents
     * @param string $checkinDate Check-in date (YYYY-MM-DD)
     * @param string $checkoutDate Check-out date (YYYY-MM-DD)
     * @param array $options Additional options
     * @return array Transaction result
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
        return $this->active && $this->is_provisioned;
    }

    /**
     * Get terminal capabilities as a formatted string
     * 
     * @return string Comma-separated list of capabilities
     */
    public function getCapabilitiesString(): string
    {
        return implode(', ', $this->getCapabilitiesAttribute());
    }

    /**
     * Check if terminal supports a specific payment method
     * 
     * @param string $method Payment method ('debit', 'emv', 'cashback', etc.)
     * @return bool True if supported
     */
    public function supportsPaymentMethod(string $method): bool
    {
        return match (strtolower($method)) {
            'debit', 'debit_card' => $this->debit,
            'emv', 'chip', 'chip_card' => $this->emv,
            'cashback', 'cash_back' => $this->cashback_enable,
            'signature', 'sig_capture' => $this->sig_capture_enable,
            'tip', 'tips' => $this->tip_enable,
            'print', 'receipt' => $this->print_enable,
            default => false,
        };
    }

    /**
     * Sync a single terminal from Fortis API by ID
     *
     * @param string $fortisId Fortis terminal ID
     * @param \Hyrograsper\LunarFortis\LunarFortis|null $fortisClient Optional Fortis client instance
     * @return static|null The synced terminal or null if not found
     * @throws \Exception
     */
    public static function syncSingleFromFortis(string $fortisId, ?\Hyrograsper\LunarFortis\LunarFortis $fortisClient = null): ?static
    {
        $fortisClient = $fortisClient ?? app(\Hyrograsper\LunarFortis\LunarFortis::class);

        try {
            // Fetch single terminal from Fortis API
            $response = $fortisClient->getTerminal($fortisId);
            $data = $response->getData();

            if (!$data) {
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

        } catch (\Exception $e) {
            \Log::error("Failed to sync terminal {$fortisId}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Map Fortis API response data to model attributes
     *
     * @param object $data Fortis terminal data object
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
            'terminal_cvm_id' => ['nullable', 'string', 'max:255'],
            'terminal_manufacturer_code' => [
                'nullable',
                'string',
                Rule::in([
                    TerminalManufacturerCodeEnum::ENUM_1,
                    TerminalManufacturerCodeEnum::ENUM_2,
                    TerminalManufacturerCodeEnum::ENUM_4,
                    TerminalManufacturerCodeEnum::ENUM_100,
                ])
            ],
            'default_product_transaction_id' => ['nullable', 'string', 'max:255'],
            'mac_address' => ['nullable', 'string', 'max:255'],
            'local_ip_address' => ['nullable', 'ip'],
            'port' => ['nullable', 'integer', 'between:1,65535'],
            'terminal_number' => ['nullable', 'string', 'max:255'],
            'communication_type' => [
                'nullable',
                'string',
                Rule::in([
                    CommunicationTypeEnum::HTTP,
                    CommunicationTypeEnum::ENUM_TCPIP,
                    CommunicationTypeEnum::ENUM_USBSERIAL,
                ])
            ],
            'debit' => ['boolean'],
            'emv' => ['boolean'],
            'cashback_enable' => ['boolean'],
            'print_enable' => ['boolean'],
            'sig_capture_enable' => ['boolean'],
            'tip_enable' => ['boolean'],
            'is_provisioned' => ['boolean'],
            'validated_decryption' => ['boolean'],
            'active' => ['boolean'],
            'header_line_1' => ['nullable', 'string', 'max:255'],
            'header_line_2' => ['nullable', 'string', 'max:255'],
            'header_line_3' => ['nullable', 'string', 'max:255'],
            'header_line_4' => ['nullable', 'string', 'max:255'],
            'header_line_5' => ['nullable', 'string', 'max:255'],
            'trailer_line_1' => ['nullable', 'string', 'max:255'],
            'trailer_line_2' => ['nullable', 'string', 'max:255'],
            'trailer_line_3' => ['nullable', 'string', 'max:255'],
            'trailer_line_4' => ['nullable', 'string', 'max:255'],
            'trailer_line_5' => ['nullable', 'string', 'max:255'],
            'default_checkin' => ['nullable', 'date'],
            'default_checkout' => ['nullable', 'date', 'after:default_checkin'],
            'default_room_rate' => ['nullable', 'integer', 'min:0'],
            'default_room_number' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get the allowed values for terminal manufacturer codes
     * 
     * @return array
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
     * Get the allowed values for communication types
     * 
     * @return array
     */
    public static function getAllowedCommunicationTypes(): array
    {
        return [
            CommunicationTypeEnum::HTTP,
            CommunicationTypeEnum::ENUM_TCPIP,
            CommunicationTypeEnum::ENUM_USBSERIAL,
        ];
    }

    /**
     * Get display names for manufacturer codes
     * 
     * @return array
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

    /**
     * Get display names for communication types
     * 
     * @return array
     */
    public static function getCommunicationTypeLabels(): array
    {
        return [
            CommunicationTypeEnum::HTTP => 'HTTP',
            CommunicationTypeEnum::ENUM_TCPIP => 'TCP/IP',
            CommunicationTypeEnum::ENUM_USBSERIAL => 'USB/Serial',
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
            'terminal_cvm_id' => $data->getTerminalCvmId(),
            'terminal_manufacturer_code' => (string) $data->getTerminalManufacturerCode(),
            'default_product_transaction_id' => $data->getDefaultProductTransactionId(),
            'mac_address' => $data->getMacAddress(),
            'local_ip_address' => $data->getLocalIpAddress(),
            'port' => $data->getPort(),
            'terminal_number' => $data->getTerminalNumber(),
            'communication_type' => $data->getCommunicationType() ?? 'http',
            'debit' => (bool) $data->getDebit(),
            'emv' => (bool) $data->getEmv(),
            'cashback_enable' => (bool) $data->getCashbackEnable(),
            'print_enable' => (bool) $data->getPrintEnable(),
            'sig_capture_enable' => (bool) $data->getSigCaptureEnable(),
            'tip_enable' => (bool) $data->getTipEnable(),
            'is_provisioned' => (bool) $data->getIsProvisioned(),
            'validated_decryption' => (bool) $data->getValidatedDecryption(),
            'active' => (bool) $data->getActive(),
            'header_line_1' => $data->getHeaderLine1(),
            'header_line_2' => $data->getHeaderLine2(),
            'header_line_3' => $data->getHeaderLine3(),
            'header_line_4' => $data->getHeaderLine4(),
            'header_line_5' => $data->getHeaderLine5(),
            'trailer_line_1' => $data->getTrailerLine1(),
            'trailer_line_2' => $data->getTrailerLine2(),
            'trailer_line_3' => $data->getTrailerLine3(),
            'trailer_line_4' => $data->getTrailerLine4(),
            'trailer_line_5' => $data->getTrailerLine5(),
            'default_checkin' => $data->getDefaultCheckin() ? \Carbon\Carbon::parse($data->getDefaultCheckin()) : null,
            'default_checkout' => $data->getDefaultCheckout() ? \Carbon\Carbon::parse($data->getDefaultCheckout()) : null,
            'default_room_rate' => $data->getDefaultRoomRate(),
            'default_room_number' => $data->getDefaultRoomNumber(),
            'fortis_created_at' => $data->getCreatedTs() ? \Carbon\Carbon::createFromTimestamp($data->getCreatedTs()) : null,
            'fortis_modified_at' => $data->getModifiedTs() ? \Carbon\Carbon::createFromTimestamp($data->getModifiedTs()) : null,
            'last_registration_ts' => $data->getLastRegistrationTs() ? \Carbon\Carbon::createFromTimestamp($data->getLastRegistrationTs()) : null,
            'created_user_id' => $data->getCreatedUserId(),
            'modified_user_id' => $data->getModifiedUserId(),
            'fortis_data' => json_decode(json_encode($data), true), // Store full response for reference
        ];
    }
}
