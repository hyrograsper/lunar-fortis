<?php

use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const RESULT_TERMINAL = 'term0000000000000000000011';

const RESULT_TXN = 'txn00000000000000000000012';

beforeEach(function () {
    config()->set('lunar-fortis.http.retry.attempts', 1);
});

function fakeTerminalRun(array $record): void
{
    Http::fake([
        'api.sandbox.fortis.tech/v1/transactions/cc/auth-only/terminal' => Http::response([
            'type' => 'TransactionProcessing',
            'data' => ['async' => ['code' => 'async-code-1', 'link' => '/v1/async/status/async-code-1']],
        ], 202),
        'api.sandbox.fortis.tech/v1/async/status/*' => Http::response([
            'type' => 'AsyncStatus',
            'data' => ['code' => 'async-code-1', 'error' => null, 'id' => RESULT_TXN, 'progress' => 100, 'type' => 'Transaction'],
        ]),
        'api.sandbox.fortis.tech/v1/transactions/'.RESULT_TXN.'*' => Http::response(['data' => array_merge([
            'id' => RESULT_TXN,
            'terminal_id' => RESULT_TERMINAL,
        ], $record)]),
    ]);
}

function runTerminalAuth(int $amount): array
{
    return LunarFortis::processTerminalCreditCardAuth(RESULT_TERMINAL, $amount, ['poll_interval_seconds' => 0]);
}

it('does not report a declined terminal authorization as successful', function () {
    fakeTerminalRun(['status_code' => 301, 'reason_code_id' => 1500, 'transaction_amount' => 500]);

    $result = runTerminalAuth(500);

    expect($result['success'])->toBeFalse()
        ->and($result['completed'])->toBeTrue()
        ->and($result['transaction_id'])->toBe(RESULT_TXN)
        ->and($result['transaction_status_code'])->toBe(301)
        ->and($result['reason_code_id'])->toBe(1500)
        ->and($result['status_code'])->toBe('async-code-1');
});

it('reports an approved terminal authorization as successful after checking the transaction', function () {
    fakeTerminalRun(['status_code' => 102, 'reason_code_id' => 1000, 'transaction_amount' => 1000]);

    $result = runTerminalAuth(1000);

    expect($result['success'])->toBeTrue()
        ->and($result['transaction_status_code'])->toBe(102)
        ->and($result['reason_code_id'])->toBe(1000);

    Http::assertSent(fn (Request $request) => $request->method() === 'GET'
        && str_contains($request->url(), '/v1/transactions/'.RESULT_TXN));
});

it('reports the terminal timing out before the processor answered as a timed out result', function () {
    Http::fake([
        'api.sandbox.fortis.tech/v1/transactions/cc/auth-only/terminal' => Http::response([
            'type' => 'Error',
            'statusCode' => 422,
            'title' => 'Unprocessable Entity',
            'detail' => 'Data Validation Failed.',
            'meta' => ['errors' => ['terminal_id' => ['Time out waiting for processor.']]],
        ], 422),
    ]);

    $result = runTerminalAuth(770);

    expect($result['success'])->toBeFalse()
        ->and($result['timed_out'])->toBeTrue()
        ->and($result['completed'])->toBeFalse()
        ->and($result['transaction_id'])->toBeNull()
        ->and($result['error'])->toBe('Time out waiting for processor.');
});

it('still throws for other terminal authorization validation errors', function () {
    Http::fake([
        'api.sandbox.fortis.tech/v1/transactions/cc/auth-only/terminal' => Http::response([
            'type' => 'Error',
            'statusCode' => 422,
            'detail' => 'Data Validation Failed.',
            'meta' => ['errors' => ['transaction_amount' => ['Must be greater than 0']]],
        ], 422),
    ]);

    expect(fn () => runTerminalAuth(0))->toThrow(Exception::class);
});

it('does not report a declined authorization as successful when waiting through the terminal model', function () {
    fakeTerminalRun(['status_code' => 301, 'reason_code_id' => 1500, 'transaction_amount' => 500]);

    $terminal = new Terminal(['fortis_id' => RESULT_TERMINAL, 'title' => 'Counter', 'serial_number' => 'SN-1', 'active' => true]);

    $result = $terminal->waitForAuthorization('async-code-1', 5, 0);

    expect($result['completed'])->toBeTrue()
        ->and($result['success'])->toBeFalse()
        ->and($result['transaction_status_code'])->toBe(301)
        ->and($result['transaction_id'])->toBe(RESULT_TXN);
});
