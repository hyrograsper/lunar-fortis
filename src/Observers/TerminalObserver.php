<?php

namespace Hyrograsper\LunarFortis\Observers;

use Exception;
use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Support\Facades\Log;

class TerminalObserver
{
    public function __construct(
        protected LunarFortis $fortis,
    ) {}

    public function updating(Terminal $terminal): bool
    {
        if (empty($terminal->fortis_id)) {
            return true;
        }

        $localOnlyFields = [
            'synced_at',
            'created_at',
            'updated_at',
            'fortis_data',
        ];

        $dirtyFields = array_keys($terminal->getDirty());
        $significantChanges = array_diff($dirtyFields, $localOnlyFields);

        if (empty($significantChanges)) {
            return true;
        }

        try {
            $this->syncToFortis($terminal, $significantChanges);
        } catch (Exception $exception) {
            Log::error('LunarFortis: Failed to sync terminal to Fortis API during update', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'terminal_title' => $terminal->title,
                'changed_fields' => $significantChanges,
                'changed_values' => array_intersect_key($terminal->getAttributes(), array_flip($significantChanges)),
                'error_message' => $exception->getMessage(),
            ]);

            throw new Exception("Failed to sync terminal to Fortis API: {$exception->getMessage()}");
        }

        return true;
    }

    public function created(Terminal $terminal): void
    {
        if (! empty($terminal->fortis_id)) {
            return;
        }

        if (empty($terminal->title) || empty($terminal->serial_number)) {
            return;
        }

        try {
            $this->createInFortis($terminal);
        } catch (Exception $exception) {
            Log::error('LunarFortis: Failed to create terminal in Fortis API during creation', [
                'terminal_id' => $terminal->id,
                'terminal_title' => $terminal->title,
                'terminal_serial' => $terminal->serial_number,
                'error_message' => $exception->getMessage(),
            ]);
        }
    }

    protected function syncToFortis(Terminal $terminal, array $changedFields): void
    {
        $syncableFields = [
            'title', 'serial_number', 'location_id', 'terminal_application_id',
            'terminal_manufacturer_code', 'default_product_transaction_id', 'active',
        ];

        $updateData = array_intersect_key(
            $terminal->getAttributes(),
            array_flip(array_intersect($changedFields, $syncableFields))
        );

        if (empty($updateData)) {
            return;
        }

        $this->fortis->updateTerminal($terminal->fortis_id, $updateData);

        $terminal->updateQuietly(['synced_at' => now()]);

        Log::info('LunarFortis: Terminal synced to Fortis API', [
            'fortis_id' => $terminal->fortis_id,
            'updated_fields' => array_keys($updateData),
        ]);
    }

    protected function createInFortis(Terminal $terminal): void
    {
        $terminalData = [
            'title' => $terminal->title,
            'serial_number' => $terminal->serial_number,
            'location_id' => $terminal->location_id ?: config('services.fortis.locationId'),
            'active' => $terminal->active ?? true,
        ];

        $optionalFields = [
            'terminal_application_id',
            'terminal_manufacturer_code',
            'default_product_transaction_id',
        ];

        foreach ($optionalFields as $field) {
            if (! is_null($terminal->getAttribute($field))) {
                $terminalData[$field] = $terminal->getAttribute($field);
            }
        }

        $response = $this->fortis->createTerminal($terminalData);
        $fortisTerminal = $response['data'] ?? [];

        $terminal->updateQuietly([
            'fortis_id' => $fortisTerminal['id'] ?? null,
            'synced_at' => now(),
        ]);

        Log::info('LunarFortis: Terminal created in Fortis API', [
            'terminal_id' => $terminal->id,
            'fortis_id' => $fortisTerminal['id'] ?? null,
        ]);
    }
}
