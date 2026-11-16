<?php

/*
 * Pending for 4.x: terminal creation and sync (live sandbox findings, CC-62).
 * Not collected by the test runners; see README.md in this directory.
 */

use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\Models\Terminal;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const PENDING_FINDINGS_TERMINAL = 'term0000000000000000000009';

beforeEach(function () {
    config()->set('lunar-fortis.http.retry.attempts', 1);
});

it('sends the flags Fortis requires when creating a terminal', function () {
    Http::fake([
        'api.sandbox.fortis.tech/v1/terminals' => Http::response(['data' => ['id' => PENDING_FINDINGS_TERMINAL]], 201),
    ]);

    LunarFortis::createTerminal([
        'title' => 'Front counter',
        'serial_number' => 'SN-1',
        'terminal_application_id' => 'app0000000000000000000001',
    ]);

    Http::assertSent(function (Request $request) {
        foreach (['debit', 'emv', 'cashback_enable', 'print_enable', 'sig_capture_enable'] as $required) {
            if (! array_key_exists($required, $request->data())) {
                return false;
            }
        }

        return is_string($request['terminal_manufacturer_code']);
    });
});

it('does not keep a local terminal that Fortis refused to create', function () {
    Http::fake([
        'api.sandbox.fortis.tech/v1/terminals' => Http::response([
            'type' => 'Error',
            'statusCode' => 412,
            'title' => 'Precondition Failed',
            'detail' => '"debit" is required',
        ], 412),
    ]);

    try {
        Terminal::create([
            'title' => 'Front counter',
            'serial_number' => 'SN-2',
            'terminal_application_id' => 'app0000000000000000000001',
            'terminal_manufacturer_code' => '100',
        ]);
    } catch (Exception) {
    }

    expect(Terminal::whereNull('fortis_id')->count())->toBe(0);
});

it('does not push Fortis values back to Fortis when syncing a terminal from Fortis', function () {
    Terminal::query()->insert([
        'fortis_id' => PENDING_FINDINGS_TERMINAL,
        'title' => 'Renamed locally',
        'serial_number' => 'SN-3',
        'active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Http::fake([
        'api.sandbox.fortis.tech/v1/terminals/*' => Http::response(['data' => [
            'id' => PENDING_FINDINGS_TERMINAL,
            'title' => 'Name in Fortis',
            'serial_number' => 'SN-3',
            'active' => 1,
            'terminal_manufacturer_code' => 100,
        ]]),
    ]);

    $terminal = Terminal::syncSingleFromFortis(PENDING_FINDINGS_TERMINAL);

    expect($terminal->title)->toBe('Name in Fortis');

    Http::assertNotSent(fn (Request $request) => $request->method() === 'PATCH');
});
