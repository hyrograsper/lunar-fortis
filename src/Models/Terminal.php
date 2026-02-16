<?php

namespace Hyrograsper\LunarFortis\Models;

use Carbon\Carbon;
use Exception;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * @property string $fortis_id
 * @property string|null $location_id
 * @property string $title
 * @property string $serial_number
 * @property string|null $terminal_application_id
 * @property string $terminal_manufacturer_code
 * @property string|null $default_product_transaction_id
 * @property bool $active
 * @property Carbon|null $fortis_created_at
 * @property Carbon|null $fortis_modified_at
 * @property string|null $created_user_id
 * @property string|null $modified_user_id
 * @property Carbon|null $synced_at
 * @property array|null $fortis_data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $display_name
 */
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

    /** @param Builder<Terminal> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /** @param Builder<Terminal> $query */
    public function scopeForLocation(Builder $query, string $locationId): Builder
    {
        return $query->where('location_id', $locationId);
    }

    /** @param Builder<Terminal> $query */
    public function scopeByManufacturer(Builder $query, string $manufacturerCode): Builder
    {
        return $query->where('terminal_manufacturer_code', $manufacturerCode);
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->title} ({$this->serial_number})";
    }

    public function needsSync(?int $hoursThreshold = 24): bool
    {
        if (! $this->synced_at) {
            return true;
        }

        return Carbon::parse($this->synced_at)->diffInHours(now()) > $hoursThreshold;
    }

    public function markSynced(): void
    {
        $this->update(['synced_at' => now()]);
    }

    public function isReadyForPayments(): bool
    {
        return $this->active;
    }

    /** @throws Exception */
    public static function syncFromFortis(): array
    {
        $stats = [
            'created' => 0,
            'updated' => 0,
            'errors' => 0,
            'total_processed' => 0,
        ];

        $response = LunarFortis::listTerminals();
        $terminals = $response['list'] ?? [];

        foreach ($terminals as $terminalData) {
            $stats['total_processed']++;

            try {
                $terminalAttributes = static::mapFortisDataToAttributes($terminalData);

                $terminal = static::updateOrCreate(
                    ['fortis_id' => $terminalAttributes['fortis_id']],
                    $terminalAttributes
                );

                $terminal->markSynced();

                if ($terminal->wasRecentlyCreated) {
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }

            } catch (Exception $exception) {
                $stats['errors']++;
                Log::error('LunarFortis: Failed to sync individual terminal', [
                    'fortis_id' => $terminalAttributes['fortis_id'] ?? 'unknown',
                    'terminal_data' => $terminalData ?? null,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    /** @throws Exception */
    public static function syncSingleFromFortis(string $fortisId): ?static
    {
        try {
            $response = LunarFortis::getTerminal($fortisId);
            $data = $response['data'] ?? [];

            if (empty($data)) {
                return null;
            }
        } catch (Exception) {
            return null;
        }

        $terminalAttributes = static::mapFortisDataToAttributes($data);

        $terminal = static::updateOrCreate(
            ['fortis_id' => $terminalAttributes['fortis_id']],
            $terminalAttributes
        );

        $terminal->markSynced();

        return $terminal;
    }

    /** @throws Exception */
    public function authorizePayment(int $amount, array $options = []): array
    {
        if (! $this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        return LunarFortis::processTerminalCreditCardAuth(
            terminalId: $this->fortis_id,
            amount: $amount,
            options: $options
        );
    }

    /** @throws Exception */
    public function initiateAuthorization(int $amount, array $options = []): string
    {
        if (! $this->active) {
            throw new Exception("Terminal {$this->fortis_id} is not active");
        }

        $response = LunarFortis::authorizeTerminalCreditCard(
            terminalId: $this->fortis_id,
            amount: $amount,
            options: $options
        );

        $asyncData = $response['data']['async'] ?? [];
        $statusCode = $asyncData['code'] ?? null;

        if (! $statusCode) {
            throw new Exception('No async status code received from terminal authorization initiation');
        }

        return $statusCode;
    }

    /** @throws Exception */
    public function checkAuthorizationStatus(string $statusCode): array
    {
        $response = LunarFortis::checkTerminalTransactionStatus($statusCode);
        $statusData = $response['data'] ?? [];

        $progress = $statusData['progress'] ?? 0;

        return [
            'progress' => $progress,
            'completed' => $progress >= 100,
            'success' => $progress >= 100 && ! ($statusData['error'] ?? null),
            'error' => $statusData['error'] ?? null,
            'transaction_id' => $statusData['id'] ?? null,
            'type' => $statusData['type'] ?? null,
            'ttl' => $statusData['ttl'] ?? null,
        ];
    }

    /** @throws Exception */
    public function waitForAuthorization(
        string $statusCode,
        int $timeoutSeconds = 300,
        int $pollIntervalSeconds = 2
    ): array {
        $response = LunarFortis::waitForTerminalTransaction($statusCode, $timeoutSeconds, $pollIntervalSeconds);
        $statusData = $response['data'] ?? [];

        $progress = $statusData['progress'] ?? 0;
        $error = $statusData['error'] ?? null;

        return [
            'progress' => $progress,
            'completed' => $progress >= 100,
            'success' => $progress >= 100 && ! $error,
            'error' => $error,
            'transaction_id' => $statusData['id'] ?? null,
            'type' => $statusData['type'] ?? null,
            'ttl' => $statusData['ttl'] ?? null,
            'timed_out' => $progress < 100 && ! $error,
        ];
    }

    /** @throws Exception */
    public function captureTransaction(string $transactionId, int $amount, array $options = []): array
    {
        return LunarFortis::captureTerminalTransaction($transactionId, $amount, $options);
    }

    /** @throws Exception */
    public function processCompletePayment(int $amount, array $options = []): array
    {
        $authResult = $this->authorizePayment($amount, $options);

        if (! $authResult['success']) {
            return $authResult;
        }

        $transactionId = $authResult['transaction_id'];
        if (! $transactionId) {
            throw new Exception('No transaction ID returned from authorization');
        }

        $captureResult = $this->captureTransaction($transactionId, $amount, $options);

        return array_merge($authResult, [
            'captured' => true,
            'capture_result' => $captureResult,
        ]);
    }

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

    public static function getAllowedManufacturerCodes(): array
    {
        return [
            1,
            2,
            4,
            100,
        ];
    }

    public static function isValidManufacturerCode(mixed $code): bool
    {
        return in_array($code, static::getAllowedManufacturerCodes(), true);
    }

    /** @return ($code is null ? array<int, string> : string|null) */
    public static function getManufacturerCodeLabels(?int $code = null): array|string|null
    {
        $labels = [
            1 => 'Manufacturer 1',
            2 => 'Manufacturer 2',
            4 => 'Manufacturer 4',
            100 => 'Manufacturer 100',
        ];

        return $code !== null ? ($labels[$code] ?? null) : $labels;
    }

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

    protected static function mapFortisDataToAttributes(array $data): array
    {
        return [
            'fortis_id' => $data['id'] ?? null,
            'location_id' => $data['location_id'] ?? null,
            'title' => $data['title'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'terminal_application_id' => $data['terminal_application_id'] ?? null,
            'terminal_manufacturer_code' => (string) ($data['terminal_manufacturer_code'] ?? 1),
            'default_product_transaction_id' => $data['default_product_transaction_id'] ?? null,
            'active' => (bool) ($data['active'] ?? true),
            'fortis_created_at' => isset($data['created_ts']) ? Carbon::createFromTimestamp($data['created_ts']) : null,
            'fortis_modified_at' => isset($data['modified_ts']) ? Carbon::createFromTimestamp($data['modified_ts']) : null,
            'created_user_id' => $data['created_user_id'] ?? null,
            'modified_user_id' => $data['modified_user_id'] ?? null,
            'fortis_data' => $data,
        ];
    }
}
