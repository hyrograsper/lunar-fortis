<?php

/*
 * Child process for the live parallel double-submit test.
 *
 * Usage: php authorize-worker.php <sqlite path> <cache path> <cart id> <fortis transaction id> <start at>
 *
 * Boots a Testbench application against the shared sqlite file and file cache
 * store, waits for the shared start time, calls FortisPaymentType::authorize()
 * and prints only {success, message, started, finished} as JSON.
 */

use Cartalyst\Converter\Laravel\ConverterServiceProvider;
use Hyrograsper\LunarFortis\LunarFortisServiceProvider;
use Hyrograsper\LunarFortis\Tests\Live\LiveFortis;
use Illuminate\Support\Facades\Config;
use Livewire\LivewireServiceProvider;
use Lunar\LunarServiceProvider;
use Lunar\Models\Cart;
use Orchestra\Testbench\Foundation\Application;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Spatie\LaravelBlink\BlinkServiceProvider;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

require __DIR__.'/../../vendor/autoload.php';

[, $databasePath, $cachePath, $cartId, $transactionId, $startAt] = $argv;

$app = Application::create(options: [
    'extra' => [
        'providers' => [
            LivewireServiceProvider::class,
            LunarFortisServiceProvider::class,
        ],
    ],
]);

Config::set('activitylog.enabled', false);
Config::set('database.default', 'live');
Config::set('database.connections.live', [
    'driver' => 'sqlite',
    'database' => $databasePath,
    'prefix' => '',
    'busy_timeout' => 15000,
    'journal_mode' => 'WAL',
    'transaction_mode' => 'IMMEDIATE',
    'foreign_key_constraints' => false,
]);
Config::set('cache.default', 'file');
Config::set('cache.stores.file', [
    'driver' => 'file',
    'path' => $cachePath,
    'lock_path' => $cachePath,
]);

$nestedSet = class_exists('Lunar\\Nestedset\\NestedSetServiceProvider')
    ? 'Lunar\\Nestedset\\NestedSetServiceProvider'
    : 'Kalnoy\\Nestedset\\NestedSetServiceProvider';

$app->register($nestedSet);

if (class_exists(ConverterServiceProvider::class)) {
    $app->register(ConverterServiceProvider::class);
}

$app->register(BlinkServiceProvider::class);
$app->register(ActivitylogServiceProvider::class);
$app->register(MediaLibraryServiceProvider::class);
$app->register(LunarServiceProvider::class);

LiveFortis::configure();

$cart = Cart::findOrFail((int) $cartId);

while (microtime(true) < (float) $startAt) {
    usleep(1000);
}

$started = microtime(true);
$result = LiveFortis::authorizeElements($cart, ['id' => $transactionId], 'automatic');
$finished = microtime(true);

echo json_encode([
    'success' => $result->success,
    'message' => $result->message,
    'started' => $started,
    'finished' => $finished,
]);
