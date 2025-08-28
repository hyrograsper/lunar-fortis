<?php

namespace Hyrograsper\LunarFortis\Models;

use Carbon\Carbon;
use Exception;
use FortisAPILib\Exceptions\ApiException;
use FortisAPILib\Models\TerminalManufacturerCodeEnum;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Helpers\FortisErrorHelper;
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

    // ========================================
    // Query Scopes
    // ========================================

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

    // ========================================
    // Accessors & Mutators
    // ========================================

    public function getDisplayNameAttribute(): string
    {
        return $this->title.' ('.$this->serial_number.')';
    }

    // ========================================
    // Terminal Status & Sync Methods
    // ========================================

    public function needsSync(?int $hoursThreshold = 24): bool
    {
        if (! $this->synced_at) {
            return true;
        }

        return $this->synced_at->diffInHours(now()) > $hoursThreshold;
    }

    public function markSynced(): void
    {
        $this->update(['synced_at' => now()]);
    }

    public function isReadyForPayments(): bool
    {
        return $this->active;
    }

    // ========================================
    // Fortis API Sync Methods
    // ========================================

    /**
     * Sync all terminals from Fortis API
     *
     * @throws Exception
     */
    public static function syncFromFortis(): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'total_processed' => 0,
        ];

        try {
            // Fetch all terminals from Fortis API
            $response = LunarFortis::listTerminals();
            $terminals = $response->getList() ?? [];

            foreach ($terminals as $terminalData) {
                $stats['total_processed']++;

                try {
                    // Extract terminal data
                    $terminalAttributes = static::mapFortisDataToAttributes($terminalData);

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

                } catch (ApiException $e) {
                    $stats['errors']++;
                    $errorDetails = FortisErrorHelper::parseApiException($e);
                    
                    Log::error('Failed to sync individual terminal', [
                        'fortis_id' => $terminalAttributes['fortis_id'] ?? 'unknown',
                        'terminal_data' => $terminalData ?? null,
                        'error_details' => $errorDetails,
                    ]);
                } catch (Exception $e) {
                    $stats['errors']++;
                    Log::error('Failed to sync individual terminal', [
                        'fortis_id' => $terminalAttributes['fortis_id'] ?? 'unknown',
                        'terminal_data' => $terminalData ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);
            
            Log::error('Failed to fetch terminals from Fortis API', [
                'error_details' => $errorDetails,
            ]);
            
            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to sync terminals from Fortis API: {$formattedError}");
        }

        return $stats;
    }

    /**
     * Sync a single terminal from Fortis API by ID
     *
     * @throws Exception
     */
    public static function syncSingleFromFortis(string $fortisId): ?static
    {
        try {
            // Fetch single terminal from Fortis API
            $response = LunarFortis::getTerminal($fortisId);
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

        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);
            
            Log::error("Failed to sync terminal {$fortisId}", [
                'fortis_id' => $fortisId,
                'error_details' => $errorDetails,
            ]);
            
            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Failed to sync terminal {$fortisId}: {$formattedError}");
        }
    }

    // ========================================
    // Payment Processing Methods
    // ========================================

    /**
     * Process a credit card payment using this terminal
     *
     * @throws Exception
     */
    public function processPayment(int $amount, array $options = []): array
    {
        if (! $this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        try {
            return LunarFortis::processTerminalCreditCard(
                terminalId: $this->fortis_id,
                amount: $amount,
                options: $options
            );
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('Terminal payment processing failed', [
                'terminal_id' => $this->fortis_id,
                'terminal_title' => $this->title,
                'amount' => $amount,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Payment processing failed: {$formattedError}");
        } catch (Exception $e) {
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
     *
     * @throws Exception
     */
    public function initiatePayment(int $amount, array $options = []): string
    {
        if (! $this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        try {
            $response = LunarFortis::chargeTerminalCreditCard(
                terminalId: $this->fortis_id,
                amount: $amount,
                options: $options
            );

            return $response->getData()->getAsync()->getCode();
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('Terminal payment initiation failed', [
                'terminal_id' => $this->fortis_id,
                'terminal_title' => $this->title,
                'amount' => $amount,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Payment initiation failed: {$formattedError}");
        }
    }

    /**
     * Check the status of a payment by async status code
     *
     * @throws Exception
     */
    public function checkPaymentStatus(string $statusCode): array
    {
        try {
            $statusData = LunarFortis::checkTerminalTransactionStatus($statusCode)
                ->getData();

            return [
                'progress' => $statusData->getProgress(),
                'completed' => $statusData->getProgress() >= 100,
                'success' => $statusData->getProgress() >= 100 && ! $statusData->getError(),
                'error' => $statusData->getError(),
                'transaction_id' => $statusData->getId(),
                'type' => $statusData->getType(),
                'ttl' => $statusData->getTtl(),
            ];
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('Terminal payment status check failed', [
                'terminal_id' => $this->fortis_id,
                'status_code' => $statusCode,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Payment status check failed: {$formattedError}");
        }
    }

    /**
     * Wait for a payment to complete
     *
     * @throws Exception
     */
    public function waitForPayment(
        string $statusCode,
        int $timeoutSeconds = 300,
        int $pollIntervalSeconds = 2
    ): array {
        try {
            $status = LunarFortis::waitForTerminalTransaction($statusCode, $timeoutSeconds, $pollIntervalSeconds);
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
        } catch (ApiException $e) {
            $errorDetails = FortisErrorHelper::parseApiException($e);

            Log::error('Terminal payment wait failed', [
                'terminal_id' => $this->fortis_id,
                'status_code' => $statusCode,
                'error_details' => $errorDetails,
            ]);

            $formattedError = FortisErrorHelper::formatApiErrors($e);
            throw new Exception("Payment wait failed: {$formattedError}");
        }
    }

    // ========================================
    // Validation & Configuration Methods
    // ========================================

    /**
     * Get validation rules for Terminal fields
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

    // ========================================
    // Private Helper Methods
    // ========================================

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
