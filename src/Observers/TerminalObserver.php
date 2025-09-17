<?php

namespace Hyrograsper\LunarFortis\Observers;

use Exception;
use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Support\Facades\Log;

class TerminalObserver
{
    public function __construct(protected LunarFortis $fortis)
    {
        //
    }

    public function updating(Terminal $terminal): bool
    {
        // Skip sync if the terminal doesn't have a Fortis ID yet
        if (empty($terminal->fortis_id)) {
            return true;
        }

        // Skip sync if only local tracking fields were updated
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
        } catch (Exception $e) {
            // Error handling is now centralized in LunarFortis, so we just need to log the sync failure
            Log::error('LunarFortis: Failed to sync terminal to Fortis API during update', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'terminal_title' => $terminal->title,
                'changed_fields' => $significantChanges,
                'changed_values' => array_intersect_key($terminal->getAttributes(), array_flip($significantChanges)),
                'error_message' => $e->getMessage(),
            ]);

            // Re-throw the exception to prevent database save and let Filament handle the error
            throw new Exception("Failed to sync terminal to Fortis API: {$e->getMessage()}");
        }

        return true;
    }

    public function created(Terminal $terminal): void
    {
        // Skip if this terminal was created via sync (has fortis_id)
        if (! empty($terminal->fortis_id)) {
            return;
        }

        // Only sync to Fortis if minimum required fields are present
        if (empty($terminal->title) || empty($terminal->serial_number)) {
            return;
        }

        try {
            $this->createInFortis($terminal);
        } catch (Exception $e) {
            // Error handling is now centralized in LunarFortis, so we just need to log the sync failure
            Log::error('LunarFortis: Failed to create terminal in Fortis API during creation', [
                'terminal_id' => $terminal->id,
                'terminal_title' => $terminal->title,
                'terminal_serial' => $terminal->serial_number,
                'error_message' => $e->getMessage(),
            ]);

            // For creation, we might want to allow local save but log the sync failure
            // Alternatively, throw exception to prevent creation entirely:
            // throw new Exception("Failed to create terminal in Fortis API: {$e->getMessage()}");
        }
    }

    protected function syncToFortis(Terminal $terminal, array $changedFields): void
    {
        // Build update data from changed fields
        $updateData = [];

        $fieldMapping = [
            'title' => 'title',
            'serial_number' => 'serial_number',
            'location_id' => 'location_id',
            'terminal_application_id' => 'terminal_application_id',
            'terminal_manufacturer_code' => 'terminal_manufacturer_code',
            'default_product_transaction_id' => 'default_product_transaction_id',
            'active' => 'active',
        ];

        foreach ($changedFields as $field) {
            if (isset($fieldMapping[$field])) {
                $value = $terminal->getAttribute($field);
                $updateData[$fieldMapping[$field]] = $value;
            }
        }

        if (! empty($updateData)) {
            Log::info('LunarFortis: Attempting to sync terminal to Fortis API', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'terminal_title' => $terminal->title,
                'update_data' => $updateData,
                'changed_fields' => $changedFields,
            ]);

            $response = $this->fortis->updateTerminal($terminal->fortis_id, $updateData);

            // Update the synced_at timestamp
            $terminal->updateQuietly(['synced_at' => now()]);

            Log::info('LunarFortis: Terminal synced to Fortis API successfully', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'updated_fields' => array_keys($updateData),
            ]);
        } else {
            Log::info('LunarFortis: No significant data changes to sync for terminal', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'changed_fields' => $changedFields,
            ]);
        }
    }

    protected function createInFortis(Terminal $terminal): void
    {
        // Build terminal data for creation
        $terminalData = [
            'title' => $terminal->title,
            'serial_number' => $terminal->serial_number,
            'location_id' => $terminal->location_id ?: config('services.fortis.locationId'),
            'active' => $terminal->active ?? true,
        ];

        // Add optional fields if they exist
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
        $fortisTerminal = $response->getData();

        // Update the local terminal with the Fortis ID and sync timestamp
        $terminal->updateQuietly([
            'fortis_id' => $fortisTerminal->getId(),
            'synced_at' => now(),
        ]);

        Log::info('LunarFortis: Terminal created in Fortis API', [
            'terminal_id' => $terminal->id,
            'fortis_id' => $fortisTerminal->getId(),
        ]);
    }
}
