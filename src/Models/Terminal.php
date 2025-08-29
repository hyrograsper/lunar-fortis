<?php

namespace Hyrograsper\LunarFortis\Models;

use Carbon\Carbon;
use Exception;
use FortisAPILib\Models\TerminalManufacturerCodeEnum;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
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

        // Fetch all terminals from Fortis API (error handling is in LunarFortis)
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

            } catch (Exception $e) {
                $stats['errors']++;
                Log::error('Failed to sync individual terminal', [
                    'fortis_id' => $terminalAttributes['fortis_id'] ?? 'unknown',
                    'terminal_data' => $terminalData ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
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
        // Fetch single terminal from Fortis API (error handling is in LunarFortis)
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

        // Error handling is now centralized in LunarFortis
        return LunarFortis::processTerminalCreditCard(
            terminalId: $this->fortis_id,
            amount: $amount,
            options: $options
        );
    }

    /**
     * Initiate a payment and return async status code for manual monitoring
     *
     * @throws Exception
     */
    public function initiatePayment(int $amount, array $options = []): string
    {
        if (! $this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        // Error handling is now centralized in LunarFortis
        $response = LunarFortis::chargeTerminalCreditCard(
            terminalId: $this->fortis_id,
            amount: $amount,
            options: $options
        );

        return $response->getData()->getAsync()->getCode();
    }

    /**
     * Check the status of a payment by async status code
     *
     * @throws Exception
     */
    public function checkPaymentStatus(string $statusCode): array
    {
        // Error handling is now centralized in LunarFortis
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
        // Error handling is now centralized in LunarFortis
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
    }

    // ========================================
    // Validation & Configuration Methods
    // ========================================

    /**
     * Get validation rules for Terminal fields
     */
    public static function getValidationRules(?string $scenario = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:255'],
            'location_id' => ['nullable', 'string', 'max:255'],
            'terminal_application_id' => ['nullable', 'string', 'max:255'],
            'terminal_manufacturer_code' => [
                'nullable',
                'string',
                Rule::in(static::getAllowedManufacturerCodes()),
            ],
            'default_product_transaction_id' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
            'fortis_id' => ['required', 'string', 'max:255'],
        ];

        // Scenario-specific rule modifications
        return match ($scenario) {
            'create' => array_merge($rules, [
                'fortis_id' => ['required', 'string', 'max:255', 'unique:fortis_terminals,fortis_id'],
            ]),
            'update' => array_merge($rules, [
                'fortis_id' => ['sometimes', 'required', 'string', 'max:255'],
            ]),
            default => $rules,
        };
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
     * Check if a manufacturer code is valid
     *
     * @param  mixed  $code
     */
    public static function isValidManufacturerCode($code): bool
    {
        return in_array($code, static::getAllowedManufacturerCodes(), true);
    }

    /**
     * Get display names for manufacturer codes
     *
     * @param  string|null  $code  Optional specific code to get label for
     * @return array|string|null
     */
    public static function getManufacturerCodeLabels(?string $code = null)
    {
        $labels = [
            TerminalManufacturerCodeEnum::ENUM_1 => 'Manufacturer 1',
            TerminalManufacturerCodeEnum::ENUM_2 => 'Manufacturer 2',
            TerminalManufacturerCodeEnum::ENUM_4 => 'Manufacturer 4',
            TerminalManufacturerCodeEnum::ENUM_100 => 'Manufacturer 100',
        ];

        return $code ? ($labels[$code] ?? null) : $labels;
    }

    /**
     * Get manufacturer code options formatted for select dropdowns
     */
    public static function getManufacturerCodeOptions(): array
    {
        $options = [];
        foreach (static::getManufacturerCodeLabels() as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => $label,
            ];
        }

        return $options;
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
