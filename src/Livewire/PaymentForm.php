<?php

namespace Hyrograsper\LunarFortis\Livewire;

use Exception;
use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\Services\ElementsTransactionBinding;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Uri;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Livewire;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Facades\Payments;
use Lunar\Models\Cart;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;

class PaymentForm extends Component
{
    private const string CLIENT_TOKEN_ACTION = 'auth-only';

    private const int CLIENT_TOKEN_CACHE_SECONDS = 600;

    public Cart $cart;

    public string $policy;

    public function mount(): void
    {
        $this->policy = config('lunar-fortis.policy') ?? 'automatic';

        if (! isset($this->cart)) {
            return;
        }

        if (Livewire::isLivewireRequest()) {
            return;
        }

        Cache::forget($this->clientTokenCacheKey());
    }

    #[On('handle-payment-response')]
    public function handlePaymentResponse(array $response): void
    {
        if (! is_array($response['data'] ?? null)) {
            Log::error('LunarFortis: Fortis Payment response missing "data" key.', $response);
            $this->dispatch('payment-error', 'Invalid payment response from gateway.');

            return;
        }

        Cache::forget($this->clientTokenCacheKey());

        /** @var PaymentAuthorize $paymentAuthorize */
        $paymentAuthorize = Payments::driver(FortisPaymentType::PAYMENT_TYPE)
            ->cart($this->cart)
            ->withData(['id' => $response['data']['id'] ?? null])
            ->authorize();

        if (! $paymentAuthorize->success) {
            $this->dispatch('payment-error', $paymentAuthorize->message);

            return;
        }

        $successEventClass = config('lunar-fortis.success_event_class');
        $successLivewireEvent = config('lunar-fortis.success_livewire_event');

        $order = Order::find($paymentAuthorize->orderId);

        if ($order) {
            if (is_string($successEventClass) && class_exists($successEventClass)) {
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

    public function isZeroDollarCart(): bool
    {
        $this->cart->calculate();

        return $this->cart->total->value < 1;
    }

    public function clientToken(): ?string
    {
        return $this->issuedClientToken()['token'] ?? null;
    }

    #[On('regenerate-client-token')]
    public function regenerateClientToken(): void
    {
        Cache::forget($this->clientTokenCacheKey());

        $issuedClientToken = $this->issuedClientToken();

        $this->dispatch(
            'token-regenerated',
            $issuedClientToken['token'] ?? null,
            $issuedClientToken['transaction_api_id'] ?? null,
        );
    }

    public function clientTokenCacheKey(): string
    {
        $this->cart->calculate();

        $fingerprint = hash('sha256', implode('|', [
            $this->cart->total->value,
            $this->cart->currency_id,
            self::CLIENT_TOKEN_ACTION,
            $this->getFortisEnvironmentProperty(),
            config('services.fortis.locationId'),
        ]));

        return "fortis_client_token:v2:cart:{$this->cart->id}:{$fingerprint}";
    }

    /** @return array{
        token: string,
        transaction_api_id: string
    }|null */
    public function issuedClientToken(): ?array
    {
        if ($this->isZeroDollarCart()) {
            return null;
        }

        return Cache::remember($this->clientTokenCacheKey(), self::CLIENT_TOKEN_CACHE_SECONDS, fn () => $this->issueClientToken());
    }

    /** @return array{
        token: string,
        transaction_api_id: string
    }|null */
    private function issueClientToken(): ?array
    {
        $token = LunarFortis::getClientTokenForSaleAmount($this->cart->total->value, self::CLIENT_TOKEN_ACTION);

        if (! is_string($token)) {
            return null;
        }

        return [
            'token' => $token,
            'transaction_api_id' => app(ElementsTransactionBinding::class)->issue($this->cart),
        ];
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
