<?php

use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Hyrograsper\LunarFortis\Tests\Live\LiveFortis;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

uses(InteractsWithLunarCarts::class)->group('live');

/*
 * Runs one card-present auth-only on the first terminal already listed for the
 * sandbox location. It only reads the terminal list and never creates, updates
 * or saves a terminal (no Terminal model is touched, so TerminalObserver never fires).
 */
beforeEach(function () {
    if (! LiveFortis::enabled()) {
        $this->markTestSkipped(LiveFortis::skipReason());
    }

    if (! getenv('FORTIS_TERMINAL_PRODUCT_TRANSACTION_ID')) {
        $this->markTestSkipped('No terminal product transaction id configured.');
    }

    $this->setUpLunar();

    LiveFortis::configure();
});

it('authorizes and captures a card-present auth-only from an existing simulated terminal', function () {
    $terminal = LiveFortis::firstTerminal();

    if ($terminal === null) {
        $this->markTestSkipped('No terminal exists in the sandbox location.');
    }

    $cart = $this->makeCart(1710);
    $total = $cart->calculate()->total->value;

    $outcome = LunarFortis::processTerminalCreditCardAuth($terminal['id'], $total, [
        'timeout_seconds' => 90,
        'poll_interval_seconds' => 3,
    ]);

    if (! $outcome['completed'] || ! is_string($outcome['transaction_id'])) {
        $this->markTestSkipped(sprintf(
            'The terminal did not complete the transaction (progress %s, error %s).',
            $outcome['progress'],
            json_encode($outcome['error']),
        ));
    }

    $record = LiveFortis::get($outcome['transaction_id']);

    fwrite(STDERR, "\n[terminal] ".json_encode($record)."\n");

    $result = LiveFortis::authorizeTerminal($cart, $outcome['transaction_id']);

    expect($record['status_code'] ?? null)->toBe(102)
        ->and($result->success)->toBeTrue()
        ->and(Order::where('cart_id', $cart->id)->first()->placed_at)->not->toBeNull()
        ->and(Transaction::where('type', 'capture')->where('success', true)->count())->toBe(1)
        ->and(Terminal::count())->toBe(0)
        ->and(LiveFortis::get($outcome['transaction_id']))->toMatchArray(['status_code' => 101, 'reason_code_id' => 1001]);
});
