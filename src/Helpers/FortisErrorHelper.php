<?php

namespace Hyrograsper\LunarFortis\Helpers;

use Exception;
use FortisAPILib\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;

class FortisErrorHelper
{
    /**
     * Parse Fortis API exception and extract detailed error information
     */
    public static function parseApiException(ApiException $exception): array
    {
        $errorDetails = [
            'message' => $exception->getMessage(),
            'status_code' => null,
            'type' => null,
            'title' => null,
            'detail' => null,
            'errors' => [],
            'raw_body' => null,
        ];

        try {
            $rawBody = $exception->getHttpResponse()->getRawBody();
            $errorDetails['raw_body'] = $rawBody;

            $decodedBody = json_decode($rawBody);

            if ($decodedBody && is_object($decodedBody)) {
                $errorDetails['status_code'] = $decodedBody->statusCode ?? null;
                $errorDetails['type'] = $decodedBody->type ?? null;
                $errorDetails['title'] = $decodedBody->title ?? null;
                $errorDetails['detail'] = $decodedBody->detail ?? null;

                // Extract validation errors from meta.errors
                if (isset($decodedBody->meta->errors) && is_object($decodedBody->meta->errors)) {
                    $errorDetails['errors'] = (array) $decodedBody->meta->errors;
                }
            }
        } catch (Exception $e) {
            // If we can't parse the response, at least we have the basic message
            Log::warning('Could not parse Fortis API exception response', [
                'original_message' => $exception->getMessage(),
                'parse_error' => $e->getMessage(),
            ]);
        }

        return $errorDetails;
    }

    /**
     * Format Fortis API exception errors for display
     */
    public static function formatApiErrors(ApiException $exception): string
    {
        $errorDetails = static::parseApiException($exception);

        $formattedError = $errorDetails['title'] ?? 'API Error';

        if ($errorDetails['detail']) {
            $formattedError .= ': '.$errorDetails['detail'];
        }

        if (! empty($errorDetails['errors'])) {
            $formattedError .= "\nValidation errors:";
            foreach ($errorDetails['errors'] as $field => $fieldErrors) {
                $formattedError .= "\n- {$field}: ".implode(', ', (array) $fieldErrors);
            }
        }

        return $formattedError;
    }

    /**
     * Get validation errors as array for easier programmatic access
     */
    public static function getValidationErrors(ApiException $exception): array
    {
        $errorDetails = static::parseApiException($exception);

        return $errorDetails['errors'] ?? [];
    }

    /**
     * Check if the exception contains validation errors
     */
    public static function hasValidationErrors(ApiException $exception): bool
    {
        $errors = static::getValidationErrors($exception);

        return ! empty($errors);
    }
}
