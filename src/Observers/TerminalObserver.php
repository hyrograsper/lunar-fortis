<?php

namespace Hyrograsper\LunarFortis\Observers;

use Exception;
use FortisAPILib\Exceptions\ApiException;
use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Support\Facades\Log;

class TerminalObserver
{
    public function __construct(protected LunarFortis $fortis)
    {
        //
    }

    public function updated(Terminal $terminal): void
    {
        // Skip sync if the terminal doesn't have a Fortis ID yet
        if (empty($terminal->fortis_id)) {
            return;
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
            return;
        }

        try {
            $this->syncToFortis($terminal, $significantChanges);
        } catch (ApiException $e) {
            Log::error('Failed to sync terminal to Fortis API after update (API Exception)', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'terminal_title' => $terminal->title,
                'changed_fields' => $significantChanges,
                'changed_values' => array_intersect_key($terminal->getAttributes(), array_flip($significantChanges)),
                'api_error_code' => $e->getCode(),
                'api_error_message' => $e->getMessage(),
                'api_response_body' => method_exists($e, 'getResponseBody') ? $e->getResponseBody() : null,
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Don't throw the exception to prevent the update from failing
            // Just log the error for manual resolution
        } catch (Exception $e) {
            Log::error('Failed to sync terminal to Fortis API after update (General Exception)', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'terminal_title' => $terminal->title,
                'changed_fields' => $significantChanges,
                'changed_values' => array_intersect_key($terminal->getAttributes(), array_flip($significantChanges)),
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Don't throw the exception to prevent the update from failing
            // Just log the error for manual resolution
        }
    }

    public function created(Terminal $terminal): void
    {
        // Skip if this terminal was created via sync (has fortis_id)
        if (!empty($terminal->fortis_id)) {
            return;
        }

        // Only sync to Fortis if minimum required fields are present
        if (empty($terminal->title) || empty($terminal->serial_number)) {
            return;
        }

        try {
            $this->createInFortis($terminal);
        } catch (ApiException $e) {
            Log::error('Failed to create terminal in Fortis API (API Exception)', [
                'terminal_id' => $terminal->id,
                'terminal_title' => $terminal->title,
                'terminal_serial' => $terminal->serial_number,
                'api_error_code' => $e->getCode(),
                'api_error_message' => $e->getMessage(),
                'api_response_body' => method_exists($e, 'getResponseBody') ? $e->getResponseBody() : null,
                'stack_trace' => $e->getTraceAsString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create terminal in Fortis API (General Exception)', [
                'terminal_id' => $terminal->id,
                'terminal_title' => $terminal->title,
                'terminal_serial' => $terminal->serial_number,
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
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
            'terminal_cvm_id' => 'terminal_cvm_id',
            'terminal_manufacturer_code' => 'terminal_manufacturer_code',
            'default_product_transaction_id' => 'default_product_transaction_id',
            'mac_address' => 'mac_address',
            'local_ip_address' => 'local_ip_address',
            'port' => 'port',
            'terminal_number' => 'terminal_number',
            'communication_type' => 'communication_type',
            'debit' => 'debit',
            'emv' => 'emv',
            'cashback_enable' => 'cashback_enable',
            'print_enable' => 'print_enable',
            'sig_capture_enable' => 'sig_capture_enable',
            'tip_enable' => 'tip_enable',
            'is_provisioned' => 'is_provisioned',
            'validated_decryption' => 'validated_decryption',
            'active' => 'active',
        ];

        // Header lines
        for ($i = 1; $i <= 5; $i++) {
            $fieldMapping["header_line_{$i}"] = "header_line_{$i}";
            $fieldMapping["trailer_line_{$i}"] = "trailer_line_{$i}";
        }

        // Lodging fields
        $fieldMapping = array_merge($fieldMapping, [
            'default_checkin' => 'default_checkin',
            'default_checkout' => 'default_checkout',
            'default_room_rate' => 'default_room_rate',
            'default_room_number' => 'default_room_number',
        ]);

        foreach ($changedFields as $field) {
            if (isset($fieldMapping[$field])) {
                $value = $terminal->getAttribute($field);
                
                // Convert dates to proper format for API
                if (in_array($field, ['default_checkin', 'default_checkout']) && $value) {
                    $value = $value instanceof \DateTime ? $value->format('Y-m-d') : $value;
                }

                $updateData[$fieldMapping[$field]] = $value;
            }
        }

        if (!empty($updateData)) {
            Log::info('Attempting to sync terminal to Fortis API', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'terminal_title' => $terminal->title,
                'update_data' => $updateData,
                'changed_fields' => $changedFields,
            ]);

            $response = $this->fortis->updateTerminal($terminal->fortis_id, $updateData);
            
            // Update the synced_at timestamp
            $terminal->updateQuietly(['synced_at' => now()]);

            Log::info('Terminal synced to Fortis API successfully', [
                'terminal_id' => $terminal->id,
                'fortis_id' => $terminal->fortis_id,
                'updated_fields' => array_keys($updateData),
            ]);
        } else {
            Log::info('No significant data changes to sync for terminal', [
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
            'debit' => $terminal->debit ?? false,
            'emv' => $terminal->emv ?? true,
            'cashback_enable' => $terminal->cashback_enable ?? false,
            'print_enable' => $terminal->print_enable ?? false,
            'sig_capture_enable' => $terminal->sig_capture_enable ?? false,
            'tip_enable' => $terminal->tip_enable ?? false,
            'active' => $terminal->active ?? true,
        ];

        // Add optional fields if they exist
        $optionalFields = [
            'terminal_application_id',
            'terminal_cvm_id',
            'terminal_manufacturer_code',
            'default_product_transaction_id',
            'mac_address',
            'local_ip_address',
            'port',
            'terminal_number',
            'communication_type',
            'is_provisioned',
            'validated_decryption',
        ];

        foreach ($optionalFields as $field) {
            if (!is_null($terminal->getAttribute($field))) {
                $terminalData[$field] = $terminal->getAttribute($field);
            }
        }

        // Header and trailer lines
        for ($i = 1; $i <= 5; $i++) {
            if ($terminal->getAttribute("header_line_{$i}")) {
                $terminalData["header_line_{$i}"] = $terminal->getAttribute("header_line_{$i}");
            }
            if ($terminal->getAttribute("trailer_line_{$i}")) {
                $terminalData["trailer_line_{$i}"] = $terminal->getAttribute("trailer_line_{$i}");
            }
        }

        // Lodging fields
        $lodgingFields = ['default_checkin', 'default_checkout', 'default_room_rate', 'default_room_number'];
        foreach ($lodgingFields as $field) {
            $value = $terminal->getAttribute($field);
            if (!is_null($value)) {
                if (in_array($field, ['default_checkin', 'default_checkout']) && $value) {
                    $value = $value instanceof \DateTime ? $value->format('Y-m-d') : $value;
                }
                $terminalData[$field] = $value;
            }
        }

        $response = $this->fortis->createTerminal($terminalData);
        $fortisTerminal = $response->getData();

        // Update the local terminal with the Fortis ID and sync timestamp
        $terminal->updateQuietly([
            'fortis_id' => $fortisTerminal->getId(),
            'synced_at' => now(),
        ]);

        Log::info('Terminal created in Fortis API', [
            'terminal_id' => $terminal->id,
            'fortis_id' => $fortisTerminal->getId(),
        ]);
    }
}