<?php

namespace Hyrograsper\LunarFortis\Livewire;

use Hyrograsper\LunarFortis\Fortis;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

class PaymentForm extends Component
{
    public Cart $cart;
    public string $policy;

    public function mount(): void
    {
        $this->policy = config('lunar.fortis.policy', 'automatic');
    }

//    public function rules(): array
//    {
//        return [
//            'identifier' => 'string|required',
//        ];
//    }

    #[On('handle-payment-response')]
    public function handlePaymentResponse(array $response): void
    {
        Log::debug('Forits Payment response: '.print_r($response, true));

        if (! isset($response['data'])) {
            Log::error('Fortis Payment response missing "data" key.', $response);
            $this->dispatch('payment-error', 'Invalid payment response from gateway.');
            return;
        }

        /** @var PaymentAuthorize $paymentAuthorize */
        $paymentAuthorize = Payments::driver(FortisPaymentType::PAYMENT_TYPE)
            ->cart($this->cart)
            ->withData($response['data'])
            ->authorize();

        if (! $paymentAuthorize->success) {
            // Dispatch an event that Alpine can listen for
            $this->dispatch('payment-error', $paymentAuthorize->message);

            return;
        }

        $order = Order::find($paymentAuthorize->orderId);
        $this->redirect(route('order-summary', ['reference' => $order->reference]));
    }

    public function clientToken(): ?string
    {
        $this->cart->calculate();

        return Cache::remember($this->clientTokenCacheKey(), 5, function () {
            return (new Fortis)->getClientTokenForSaleAmount($this->cart->total->value);
        });
    }

    #[On('regenerate-client-token')]
    public function regenerateClientToken(): void
    {// Clear the cached token
        Cache::forget($this->clientTokenCacheKey());

        // Generate and return a new token
        $this->dispatch('token-regenerated', $this->clientToken());
    }

    public function clientTokenCacheKey(): string
    {
        $key = 'fortis_client_token:';

        if ($this->cart->user_id) {
            return $key.$this->cart->user_id;
        }

        return $key.$this->cart->id;
    }

    public function getFortisEnvironmentProperty(): string
    {
        return config('lunar.fortis.environment', 'sandbox');
    }

    public function getFortisJSUrlProperty(): string
    {
        return $this->getFortisEnvironmentProperty() == 'production'
            ? 'https://js.fortis.tech/commercejs-v1.0.0.min.js'
            : 'https://js.sandbox.fortis.tech/commercejs-v1.0.0.min.js';
    }

    public function getBillingProperty(): ?OrderAddress
    {
        return $this->cart->billingAddress;
    }

    public function render(): View
    {
        return view('lunar-fortis::components.payment-form');
    }
}
