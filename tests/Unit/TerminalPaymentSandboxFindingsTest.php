<?php

use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Hyrograsper\LunarFortis\Tests\Concerns\InteractsWithLunarCarts;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Cart;
use Lunar\Models\Transaction;

uses(InteractsWithLunarCarts::class);

const FINDINGS_API = 'api.sandbox.fortis.tech/v1/transactions';

const FINDINGS_TXN = 'txn00000000000000000000010';

const FINDINGS_OTHER_TXN = 'txn00000000000000000000011';

const FINDINGS_TERMINAL = 'term0000000000000000000010';

beforeEach(function () {
    $this->setUpLunar();

    Http::preventStrayRequests();

    $this->cart = $this->makeCart(1000);
});

function payAtTerminal(Cart $cart, string $transactionId = FINDINGS_TXN, string $policy = 'automatic'): PaymentAuthorize
{
    Config::set('lunar-fortis.terminal_policy', $policy);

    return (new FortisTerminalPaymentType)
        ->cart($cart)
        ->withData(['fortis_transaction_id' => $transactionId])
        ->authorize();
}

function findingsRecord(array $overrides = []): array
{
    return array_merge([
        'id' => FINDINGS_TXN,
        'status_code' => 102,
        'reason_code_id' => 1000,
        'transaction_amount' => 1000,
        'tip_amount' => 0,
        'terminal_id' => FINDINGS_TERMINAL,
        'account_type' => 'visa',
        'last_four' => '1111',
        'verbiage' => 'Test 1540',
    ], $overrides);
}

/**
 * @param  array<int, array<string, mixed>>  $references
 */
function fakeFindingsLookup(array $record, array $references = []): void
{
    $id = $record['id'];

    Http::fake([
        FINDINGS_API."/{$id}/auth-complete" => Http::response([
            'data' => array_merge($record, ['status_code' => 101, 'reason_code_id' => 1001]),
        ]),
        FINDINGS_API."/{$id}/refund" => Http::response([
            'data' => ['id' => 'refund0000000000000000001', 'status_code' => 111, 'reason_code_id' => 1000, 'transaction_amount' => 300],
        ]),
        FINDINGS_API."/{$id}*" => function (Request $request) use ($record, $references) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            $expanded = in_array('transaction_references', explode(',', (string) ($query['expand'] ?? '')), true);

            return Http::response(['data' => $expanded
                ? array_merge($record, ['transaction_references' => $references])
                : $record,
            ]);
        },
    ]);
}

function refundReference(string $parentId, int $amount): array
{
    return [
        'id' => 'ref00000000000000000000001',
        'transaction_id' => 'refund0000000000000000002',
        'previous_transaction_id' => $parentId,
        'transaction_amount' => $amount,
        'previous_transaction_amount' => 1000,
        'reference_type' => 'refund',
    ];
}

describe('a terminal retry on an order that is already placed', function () {
    it('returns a failed payment instead of throwing when the same transaction is submitted again', function () {
        fakeFindingsLookup(findingsRecord());

        expect(payAtTerminal($this->cart)->success)->toBeTrue();

        $order = $this->cart->refresh()->completedOrder()->first();
        $recorded = Transaction::where('order_id', $order->id)->count();

        $retry = payAtTerminal($this->cart->refresh());

        expect($retry->success)->toBeFalse()
            ->and($retry->message)->toBe('This order has already been placed')
            ->and($retry->orderId)->toBe($order->id)
            ->and(Transaction::where('order_id', $order->id)->count())->toBe($recorded);
    });

    it('refuses a second, different terminal transaction for an order that already has an authorized payment', function (string $driver) {
        fakeFindingsLookup(findingsRecord(['id' => FINDINGS_OTHER_TXN]));

        $order = $this->cart->createOrder();

        Transaction::create([
            'order_id' => $order->id,
            'success' => true,
            'type' => 'intent',
            'driver' => $driver,
            'amount' => 1000,
            'reference' => FINDINGS_TXN,
            'status' => 'authorized',
            'card_type' => 'visa',
            'last_four' => '1111',
        ]);

        $result = payAtTerminal($this->cart->refresh(), FINDINGS_OTHER_TXN, 'manual');

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('This order already has an authorized payment.')
            ->and(Transaction::where('order_id', $order->id)->pluck('reference')->all())->toBe([FINDINGS_TXN]);

        Http::assertNothingSent();
    })->with([
        'terminal' => [FortisTerminalPaymentType::PAYMENT_TYPE],
        'elements' => [FortisPaymentType::PAYMENT_TYPE],
    ]);

    it('still reuses the recorded authorization when the same transaction is retried before the order is placed', function () {
        fakeFindingsLookup(findingsRecord());

        $first = payAtTerminal($this->cart->refresh(), policy: 'manual');
        $second = payAtTerminal($this->cart->refresh(), policy: 'manual');

        expect($first->success)->toBeTrue()
            ->and($second->success)->toBeTrue()
            ->and(Transaction::where('reference', FINDINGS_TXN)->count())->toBe(1);
    });
});

describe('a refunded terminal transaction', function () {
    it('is rejected when Fortis reports a refund against it', function (int $refunded) {
        fakeFindingsLookup(
            findingsRecord(['status_code' => 101, 'reason_code_id' => 1001]),
            [refundReference(FINDINGS_TXN, $refunded)],
        );

        $result = payAtTerminal($this->cart);

        expect($result->success)->toBeFalse()
            ->and($result->message)->toBe('This payment has been refunded.')
            ->and(Transaction::where('success', true)->count())->toBe(0);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), FINDINGS_TXN.'?expand=transaction_references'));
    })->with([
        'fully' => [1000],
        'partially' => [700],
    ]);

    it('is accepted when Fortis reports no refund against it', function () {
        fakeFindingsLookup(findingsRecord(['status_code' => 101, 'reason_code_id' => 1001]));

        expect(payAtTerminal($this->cart)->success)->toBeTrue();
    });
});

it('rejects a transaction that was not taken on a terminal', function () {
    fakeFindingsLookup(findingsRecord(['terminal_id' => null]));

    $result = payAtTerminal($this->cart);

    expect($result->success)->toBeFalse()
        ->and($result->message)->toBe('The payment was not taken on a terminal.')
        ->and(Transaction::count())->toBe(0);
});

describe('a declined terminal transaction', function () {
    it('reports the Fortis reason instead of an unknown error', function (array $record, string $message) {
        fakeFindingsLookup(findingsRecord(array_merge(['status_code' => 301], $record)));

        expect(payAtTerminal($this->cart)->message)->toBe($message);
    })->with([
        'mapped reason and verbiage' => [['reason_code_id' => 1500, 'verbiage' => 'Test 1540'], 'Generic Decline. Test 1540'],
        'unmapped reason with verbiage' => [['reason_code_id' => 1999, 'verbiage' => 'Do not honor'], 'Do not honor'],
        'no reason and no verbiage' => [['reason_code_id' => null, 'verbiage' => null], 'The payment was declined.'],
    ]);

    it('keeps the reason when the declined transaction is submitted again', function () {
        fakeFindingsLookup(findingsRecord(['status_code' => 301, 'reason_code_id' => 1500]));

        payAtTerminal($this->cart);

        expect(payAtTerminal($this->cart->refresh())->message)->toBe('Generic Decline. Test 1540');
    });
});

it('keeps the terminal id on refund transactions', function () {
    fakeFindingsLookup(findingsRecord());

    payAtTerminal($this->cart);

    $capture = Transaction::where('type', 'capture')->firstOrFail();

    (new FortisTerminalPaymentType)->refund($capture, 300);

    $refund = Transaction::where('type', 'refund')->firstOrFail();

    expect($refund->meta['terminal_id'])->toBe(FINDINGS_TERMINAL);
});
