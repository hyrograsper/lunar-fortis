<?php

// config for Hyrograsper/LunarFortis
return [
    /*
    |--------------------------------------------------------------------------
    | Fortis Environment
    |--------------------------------------------------------------------------
    | Accepted values: 'sandbox', 'production'.
    */
    'environment' => env('FORTIS_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Payment Policy
    |--------------------------------------------------------------------------
    | 'automatic' will capture the payment immediately.
    */
    'policy' => env('LUNAR_FORTIS_POLICY', 'automatic'),

    /*
    |--------------------------------------------------------------------------
    | Fortis JavaScript SDK URLs
    |--------------------------------------------------------------------------
    */
    'js_url_sandbox' => env('FORTIS_JS_URL_SANDBOX', 'https://js.sandbox.fortis.tech/commercejs-v1.0.0.min.js'),
    'js_url_production' => env('FORTIS_JS_URL_PRODUCTION', 'https://js.fortis.tech/commercejs-v1.0.0.min.js'),

    /*
    |--------------------------------------------------------------------------
    | Status mapping
    |--------------------------------------------------------------------------
    */
    'status_mapping' => [
        'payment-authorized' => 'payment-authorized',
        'payment-received' => 'payment-received',
    ],

    /*
    |--------------------------------------------------------------------------
    | Elements Appearance Settings
    |
    | Settings will be applied separately for dark and light mode.
    | See Fortis Docs for all settings: https://docs.fortis.tech/v/1_0_0#/rest/elements/configuration-options/appearance-option
    |--------------------------------------------------------------------------
    */
    'elements' => [
        'appearance' => [
            'light' => [],
            'dark' => [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Success Redirect
    |--------------------------------------------------------------------------
    | Where to redirect when successful. Set false to disable.
    | If 'route_name' is set, it will redirect using routes.
    | If you do not want your route to be a signed route set 'use_signed_routes' to false (default: true)
    | If 'route_name' is not set, will try and use 'uri' to redirect. The 'uri' can be a full url or just uri.
    |
    | NOTE: A 'reference' query parameter will be passed along set to the \Lunar\Models\Order $order->reference.
    |
    */
    'success_redirect' => [
        'route_name' => null,
        'use_signed_route' => true,
        'uri' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Success Laravel Event
    |--------------------------------------------------------------------------
    | The Laravel Event Class to be dispatched. \Lunar\Models\Order $order will be passed in.
    | To disable set to null.
    |
    */
    'success_event_class' => null,

    /*
    |--------------------------------------------------------------------------
    | Success Livewire Event
    |--------------------------------------------------------------------------
    | The livewire event to be dispatched. \Lunar\Models\Order $order will be passed in.
    | To disable set to null.
    |
    */
    'success_livewire_event' => null,

    /*
    |--------------------------------------------------------------------------
    | HTTP Configuration
    |--------------------------------------------------------------------------
    | Configure HTTP client behavior for Fortis API requests.
    |
    */
    'http' => [
        /*
         * Request timeout in seconds
         */
        'timeout' => env('FORTIS_HTTP_TIMEOUT', 30),

        /*
         * Retry configuration
         */
        'retry' => [
            /*
             * Number of retry attempts for failed requests
             */
            'attempts' => env('FORTIS_HTTP_RETRY_ATTEMPTS', 3),

            /*
             * Delay between retries in milliseconds
             */
            'delay' => env('FORTIS_HTTP_RETRY_DELAY', 1000),

            /*
             * Whether to retry on connection errors (network issues)
             */
            'on_connection_error' => env('FORTIS_HTTP_RETRY_CONNECTION', true),

            /*
             * HTTP status codes that should trigger a retry
             * Common defaults: 429 (rate limit), 502 (bad gateway), 503 (service unavailable), 504 (gateway timeout)
             */
            'on_status_codes' => array_map('intval', explode(',', env('FORTIS_HTTP_RETRY_STATUS_CODES', '429,502,503,504'))),

            /*
             * Whether to use exponential backoff (multiplies delay by attempt number)
             * If false, uses fixed delay between retries
             */
            'exponential_backoff' => env('FORTIS_HTTP_RETRY_EXPONENTIAL', false),

            /*
             * Maximum delay in milliseconds when using exponential backoff
             */
            'max_delay' => env('FORTIS_HTTP_RETRY_MAX_DELAY', 10000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Logging
    |--------------------------------------------------------------------------
    | Turn debug logging on to view each step of the payment process.
    | Accepted values: true, false.
    |
    */
    'debug' => env('FORTIS_DEBUG', false),
];
