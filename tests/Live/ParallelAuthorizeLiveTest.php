<?php

use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Hyrograsper\LunarFortis\Tests\Live\LiveFortis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

uses(InteractsWithLunarCarts::class)->group('live');

/*
 * Two separate PHP processes (tests/Live/authorize-worker.php) authorize the same
 * Fortis transaction id for the same cart at the same moment. They share a sqlite
 * file and the file cache store, so Cache::lock() is a real cross-process lock.
 */
beforeEach(function () {
    if (! LiveFortis::enabled()) {
        $this->markTestSkipped(LiveFortis::skipReason());
    }

    $this->sharedDirectory = sys_get_temp_dir().'/lflive-'.bin2hex(random_bytes(6));
    $this->databasePath = "{$this->sharedDirectory}/database.sqlite";
    $this->cachePath = "{$this->sharedDirectory}/cache";

    File::ensureDirectoryExists($this->cachePath);

    $sharedDirectory = $this->sharedDirectory;
    register_shutdown_function(fn () => File::deleteDirectory($sharedDirectory));
    touch($this->databasePath);

    Config::set('database.connections.testing.database', $this->databasePath);
    Config::set('database.connections.testing.busy_timeout', 15000);
    Config::set('database.connections.testing.journal_mode', 'WAL');
    Config::set('database.connections.testing.transaction_mode', 'IMMEDIATE');
    DB::purge('testing');

    Config::set('cache.stores.file.path', $this->cachePath);
    Config::set('cache.stores.file.lock_path', $this->cachePath);
    Cache::setDefaultDriver('file');

    $this->setUpLunar();

    LiveFortis::configure();
});

/** @return array<int, array{success: bool, message: string, started: float, finished: float}> */
function liveRunWorkers(string $databasePath, string $cachePath, int $cartId, string $transactionId, int $count = 2): array
{
    $startAt = microtime(true) + 4;
    $processes = [];

    for ($index = 0; $index < $count; $index++) {
        $command = [
            PHP_BINARY,
            __DIR__.'/authorize-worker.php',
            $databasePath,
            $cachePath,
            (string) $cartId,
            $transactionId,
            (string) $startAt,
        ];

        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

        $processes[] = [$process, $pipes];
    }

    return array_map(function (array $entry): array {
        [$process, $pipes] = $entry;

        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        $decoded = json_decode($output, true);

        if (! is_array($decoded)) {
            $plain = preg_replace('/\e\[[0-9;]*m/', '', trim("{$errors}\n{$output}"));
            $summary = implode(' | ', array_slice(array_values(array_filter(array_map('trim', explode("\n", $plain)))), 0, 3));

            throw new RuntimeException("Worker failed: {$summary}");
        }

        return $decoded;
    }, $processes);
}

it('11: lets exactly one of two concurrent processes authorize the same sale', function (int $price) {
    $cart = $this->makeCart($price);
    $total = $cart->calculate()->total->value;
    $cart->createOrder();

    $fortis = LiveFortis::keyedSale($total);

    expect($fortis)->toMatchArray(['http' => 201, 'status_code' => 101]);

    $results = liveRunWorkers($this->databasePath, $this->cachePath, $cart->id, $fortis['id']);

    $winners = collect($results)->where('success', true);
    $loser = collect($results)->firstWhere('success', false);
    $overlapped = max(array_column($results, 'started')) < min(array_column($results, 'finished'));

    fwrite(STDERR, sprintf(
        "\n[case 11 price %d] overlapped=%s loser=\"%s\"\n",
        $price,
        $overlapped ? 'yes' : 'no',
        $loser['message'] ?? 'none',
    ));

    expect($winners)->toHaveCount(1)
        ->and(Transaction::where('reference', $fortis['id'])->count())->toBe(1)
        ->and(Order::where('cart_id', $cart->id)->whereNotNull('placed_at')->count())->toBe(1);
})->with([1610, 1620, 1630]);

it('11b: lets exactly one of two concurrent processes authorize and capture the same auth-only', function () {
    $cart = $this->makeCart(1640);
    $total = $cart->calculate()->total->value;
    $cart->createOrder();

    $fortis = LiveFortis::keyedAuthOnly($total);

    $results = liveRunWorkers($this->databasePath, $this->cachePath, $cart->id, $fortis['id']);

    $loser = collect($results)->firstWhere('success', false);
    $overlapped = max(array_column($results, 'started')) < min(array_column($results, 'finished'));

    fwrite(STDERR, sprintf(
        "\n[case 11b] overlapped=%s loser=\"%s\"\n",
        $overlapped ? 'yes' : 'no',
        $loser['message'] ?? 'none',
    ));

    expect(collect($results)->where('success', true))->toHaveCount(1)
        ->and(Transaction::where('reference', $fortis['id'])->whereNull('parent_transaction_id')->count())->toBe(1)
        ->and(Transaction::where('type', 'capture')->where('success', true)->count())->toBe(1)
        ->and(LiveFortis::get($fortis['id']))->toMatchArray(['status_code' => 101, 'reason_code_id' => 1001]);
});
