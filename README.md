# Fortis payment gateway integration for Lunar.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hyrograsper/lunar-fortis.svg?style=flat-square)](https://packagist.org/packages/hyrograsper/lunar-fortis)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/hyrograsper/lunar-fortis/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/hyrograsper/lunar-fortis/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/hyrograsper/lunar-fortis/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/hyrograsper/lunar-fortis/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/hyrograsper/lunar-fortis.svg?style=flat-square)](https://packagist.org/packages/hyrograsper/lunar-fortis)

A comprehensive Fortis payment gateway integration for Lunar, providing both online and in-person terminal payment processing capabilities with a streamlined admin interface for terminal management.

This package includes two payment types (online and terminal), terminal management tools, database synchronization with the Fortis API, and a complete Filament admin interface for managing payment terminals.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/lunar-fortis.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/lunar-fortis)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require hyrograsper/lunar-fortis
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="lunar-fortis-migrations"
php artisan migrate
```

**Available Migrations:**
- `create_fortis_terminals_table` - Creates the `fortis_terminals` table for storing terminal information from the Fortis API

You can publish the config file with:

```bash
php artisan vendor:publish --tag="lunar-fortis-config"
```

This is the contents of the published config file:

```php
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
    | Debug Logging
    |--------------------------------------------------------------------------
    | Turn debug logging on to view each step of the payment process.
    | Accepted values: true, false.
    |
    */
    'debug' => env('FORTIS_DEBUG', false),
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="lunar-fortis-views"
```

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file:

```env
FORTIS_USER_ID=your_fortis_user_id
FORTIS_USER_API_KEY=your_fortis_api_key
FORTIS_DEVELOPER_ID=your_fortis_developer_id
FORTIS_LOCATION_ID=your_fortis_location_id
FORTIS_ENVIRONMENT=sandbox  # or 'production'
FORTIS_DEBUG=false  # Set to true to enable debug logging
```

### Payment Configuration

Configure the payment types in your `config/services.php`:

```php
'fortis' => [
    'userId' => env('FORTIS_USER_ID'),
    'userApiKey' => env('FORTIS_USER_API_KEY'),
    'developerId' => env('FORTIS_DEVELOPER_ID'),
    'locationId' => env('FORTIS_LOCATION_ID'),
],
```

## Admin Panel Integration

### Register the Filament Plugin

To enable the terminal management interface in your Lunar admin panel, register the Filament plugin in your `AppServiceProvider` or a dedicated service provider:

```php
use Lunar\Admin\Support\Facades\LunarPanel;
use Hyrograsper\LunarFortis\Filament\LunarFortisPlugin;

public function boot(): void
{
    LunarPanel::panel(function ($panel) {
        return $panel->plugins([
                new LunarFortisPlugin,
            ]);
    })->register();
}
```

This will add "Fortis Terminals" resource to your Lunar admin panel Settings section with terminal management capabilities.

### Admin Features

Once registered, you'll have access to:

- **Terminal Management**: Full CRUD operations for payment terminals
- **Bulk Sync**: Sync all terminals from Fortis API
- **Individual Sync**: Sync specific terminals
- **Capture Payments**: Process transactions directly from admin
- **Status Management**: Activate/deactivate terminals
- **Filtering & Search**: Advanced filtering by status and manufacturer codes
- **Terminal Monitoring**: View terminal status and sync information

## Usage

### Payment Types

This package provides two payment types:

#### 1. Online Payments (`fortis`)
For e-commerce/online transactions using Fortis Elements:

```php
// Configure in Lunar payment settings
'driver' => 'fortis',
'policy' => 'automatic', // or 'manual'
```

#### 2. Terminal Payments (`fortis-terminal`) 
For in-person transactions using physical terminals:

```php
// Configure in Lunar payment settings  
'driver' => 'fortis-terminal',
// Policy is always 'automatic' for terminal payments
```

### Terminal Management

#### Database Operations

```php
use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\LunarFortis;

// Sync all terminals from Fortis API
$stats = Terminal::syncFromFortis();
// Returns: ['created' => 3, 'updated' => 2, 'errors' => 0, 'total_processed' => 5]

// Sync a single terminal
$terminal = Terminal::syncSingleFromFortis('terminal_id_123');

// Query terminals
$activeTerminals = Terminal::active()->get();
$locationTerminals = Terminal::forLocation('location_id')->get();
$manufacturerTerminals = Terminal::byManufacturer('1')->get();
$readyTerminals = Terminal::where('active', true)->get();
```

#### API Operations

```php
$fortis = app(LunarFortis::class);

// Create a terminal
$terminal = $fortis->createSimpleTerminal('POS Terminal 1', '1234567890', 'app_id');

// List terminals with filtering
$terminals = $fortis->listTerminals([
    'filterBy' => [
        ['key' => 'active', 'operator' => '=', 'value' => '1']
    ]
]);

// Get specific terminal
$terminal = $fortis->getTerminal('terminal_id_123');

// Update terminal
$updated = $fortis->updateTerminal('terminal_id_123', [
    'title' => 'Updated Terminal Name',
    'active' => false
]);
```

### Terminal Payment Processing

#### Using Terminal Model

```php
use Hyrograsper\LunarFortis\Models\Terminal;

$terminal = Terminal::where('active', true)->first();

// Complete payment (authorize + capture)
$result = $terminal->processCompletePayment(1099, [
    'order_number' => 'ORD-12345',
    'description' => 'Coffee purchase'
]);

// Authorization only
$authResult = $terminal->authorizePayment(1099, [
    'order_number' => 'ORD-12345',
    'description' => 'Payment'
]);

// Capture authorized transaction
if ($authResult['success']) {
    $transactionId = $authResult['transaction_id'];
    $captureResult = $terminal->captureTransaction($transactionId, 1099);
}

// Check terminal readiness
if ($terminal->isReadyForPayments()) {
    $result = $terminal->processCompletePayment(1099, [
        'order_number' => 'ORD-12345',
        'description' => 'Payment'
    ]);
}
```

#### Using LunarFortis Class

```php
$fortis = app(LunarFortis::class);

// Complete authorization processing with automatic monitoring
$result = $fortis->processTerminalCreditCardAuth('terminal_123', 1099, [
    'order_number' => 'ORD-12345',
    'customer_id' => 'CUST-456'
]);

// Manual authorization handling
$response = $fortis->authorizeTerminalCreditCard('terminal_123', 1099, [
    'order_number' => 'ORD-12345'
]);
$statusCode = $response['data']['async']['code'];
$finalStatus = $fortis->waitForTerminalTransaction($statusCode);

// Capture the authorized transaction
if ($finalStatus['data']['progress'] >= 100) {
    $transactionId = $finalStatus['data']['id'];
    $captureResult = $fortis->captureTerminalTransaction($transactionId, 1099, [
        'order_number' => 'ORD-12345'
    ]);
}
```

### Lunar Integration

#### In Your Order Processing

```php
// Update this section on use.
// Right now I am doing a terminal transaction outside of payment driver
// so that I can get status and show updates in real time.
// Then I pass the transaction id to the payment driver to fetch transaction and store.
```

## Testing

```bash
# No tests  yet
# composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Alec Garcia](https://github.com/alecgarcia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
