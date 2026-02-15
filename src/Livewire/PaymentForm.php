<?php

namespace Hyrograsper\LunarFortis\Livewire;

use Exception;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Uri;
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
        $this->policy = config('lunar-fortis.policy') ?? 'automatic';
    }

    #[On('handle-payment-response')]
    public function handlePaymentResponse(array $response): void
    {
        if (! isset($response['data'])) {
            Log::error('LunarFortis: Fortis Payment response missing "data" key.', $response);
            $this->dispatch('payment-error', 'Invalid payment response from gateway.');

            return;
        }

        /** @var PaymentAuthorize $paymentAuthorize */
        $paymentAuthorize = Payments::driver(FortisPaymentType::PAYMENT_TYPE)
            ->cart($this->cart)
            ->withData($response['data'])
            ->authorize();

        if (! $paymentAuthorize->success) {
            $this->dispatch('payment-error', $paymentAuthorize->message);

            return;
        }

        $successEventClass = config('lunar-fortis.success_event_class');
        $successLivewireEvent = config('lunar-fortis.success_livewire_event');

        $order = Order::find($paymentAuthorize->orderId);

        if ($order) {
            if (class_exists($successEventClass)) {
                try {
                    $successEventClass::dispatch($order);
                } catch (Exception $exception) {
                    Log::error('LunarFortis: Unable to dispatch Success Event Class: '.$exception->getMessage());
                }
            }

            if ($successLivewireEvent) {
                $this->dispatch($successLivewireEvent, $order);
            }

            if (config('lunar-fortis.success_redirect', false)) {
                $routeName = config('lunar-fortis.success_redirect.route_name');

                if ($routeName && config('lunar-fortis.success_redirect.use_signed_route', true)) {
                    $this->redirect(Uri::signedRoute($routeName, ['reference' => $order->reference])->value());

                    return;
                }

                if ($routeName) {
                    $this->redirect(route($routeName, ['reference' => $order->reference]));

                    return;
                }

                if ($redirectUri = config('lunar-fortis.success_redirect.uri')) {
                    $this->redirect(Uri::of($redirectUri)
                        ->withQueryIfMissing(['reference' => $order->reference])->value());

                    return;
                }
            }
        }
    }

    /** Fortis API requires a minimum amount of 1 cent. */
    public function isZeroDollarCart(): bool
    {
        $this->cart->calculate();

        return $this->cart->total->value < 1;
    }

    public function clientToken(): ?string
    {
        if ($this->isZeroDollarCart()) {
            return null;
        }

        return Cache::remember($this->clientTokenCacheKey(), 5, function () {
            return LunarFortis::getClientTokenForSaleAmount($this->cart->total->value, 'auth-only');
        });
    }

    #[On('regenerate-client-token')]
    public function regenerateClientToken(): void
    {
        Cache::forget($this->clientTokenCacheKey());

        $this->dispatch('token-regenerated', $this->clientToken());
    }

    public function clientTokenCacheKey(): string
    {
        $identifier = $this->cart->user_id ?: $this->cart->id;

        return "fortis_client_token:{$identifier}";
    }

    public function getBillingProperty(): ?OrderAddress
    {
        return $this->cart->billingAddress;
    }

    public function getElementsAppearanceSettingsProperty(): string
    {
        return json_encode(config('lunar-fortis.elements.appearance'));
    }

    public function getFortisEnvironmentProperty(): string
    {
        return config('lunar-fortis.environment', 'sandbox');
    }

    public function getFortisJSUrlProperty(): string
    {
        return $this->getFortisEnvironmentProperty() === 'production'
            ? 'https://js.fortis.tech/commercejs-v1.0.0.min.js'
            : 'https://js.sandbox.fortis.tech/commercejs-v1.0.0.min.js';
    }

    public function render(): View
    {
        /** @var view-string $viewName */
        $viewName = 'lunar-fortis::components.payment-form';

        return view($viewName);
    }
}
