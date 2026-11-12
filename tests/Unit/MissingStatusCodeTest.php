<?php

use Hyrograsper\LunarFortis\Facades\LunarFortis as LunarFortisFacade;
use Hyrograsper\LunarFortis\LunarFortis;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\ModelManifest;
use Lunar\Base\ModelManifestInterface;
use Lunar\Models\Order;
use Lunar\Models\Transaction;

beforeEach(function () {
    app()->singleton(ModelManifestInterface::class, ModelManifest::class);

    Schema::create('transactions', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('parent_transaction_id')->nullable();
        $table->unsignedBigInteger('order_id');
        $table->boolean('success');
        $table->string('type')->nullable();
        $table->boolean('refund')->default(false);
        $table->string('driver');
        $table->integer('amount');
        $table->string('reference');
        $table->string('status');
        $table->string('notes')->nullable();
        $table->string('card_type')->nullable();
        $table->string('last_four')->nullable();
        $table->timestamp('captured_at')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
    });

    $order = new Order;
    $order->forceFill(['id' => 1]);

    $this->parentTransaction = new Transaction;
    $this->parentTransaction->forceFill(['card_type' => 'visa', 'last_four' => '4242']);
    $this->parentTransaction->setRelation('order', $order);
});

function responseWithoutStatusCode(): array
{
    return [
        'data' => [
            'id' => 'txn-no-status',
            'transaction_amount' => '10.00',
            'reason_code_id' => 1500,
            'verbiage' => 'Gateway said no',
        ],
    ];
}

it('records a declined transaction when a FortisPaymentType capture response has no status_code', function () {
    $fortis = Mockery::mock(LunarFortis::class);
    $fortis->shouldReceive('completeAuthorizedTransaction')->once()->andReturn(responseWithoutStatusCode());

    $result = (new FortisPaymentType($fortis))->capture($this->parentTransaction, 1000);

    $recorded = Transaction::first();

    expect($result->success)->toBeFalse()
        ->and($recorded)->not->toBeNull()
        ->and((bool) $recorded->success)->toBeFalse()
        ->and($recorded->status)->toBe('declined')
        ->and($recorded->captured_at)->toBeNull();
});

it('records a declined transaction when a FortisTerminalPaymentType capture response has no status_code', function () {
    LunarFortisFacade::shouldReceive('completeAuthorizedTransaction')->once()->andReturn(responseWithoutStatusCode());

    $result = (new FortisTerminalPaymentType)->capture($this->parentTransaction, 1000);

    $recorded = Transaction::first();

    expect($result->success)->toBeFalse()
        ->and($recorded)->not->toBeNull()
        ->and((bool) $recorded->success)->toBeFalse()
        ->and($recorded->status)->toBe('declined')
        ->and($recorded->captured_at)->toBeNull();
});
