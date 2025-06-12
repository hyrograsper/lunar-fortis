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
];
