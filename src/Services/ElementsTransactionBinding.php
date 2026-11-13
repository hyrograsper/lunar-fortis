<?php

namespace Hyrograsper\LunarFortis\Services;

use Illuminate\Support\Facades\Cache;
use Lunar\Models\Cart;

class ElementsTransactionBinding
{
    private const string PREFIX = 'lf-';

    private const int MAX_ISSUED_PER_CART = 10;

    private const int TTL_SECONDS = 3600;

    public function issue(Cart $cart): string
    {
        $transactionApiId = self::PREFIX.bin2hex(random_bytes(16));

        $issued = array_slice($this->issued($cart), -(self::MAX_ISSUED_PER_CART - 1));
        $issued[] = $transactionApiId;

        Cache::put($this->cacheKey($cart), $issued, self::TTL_SECONDS);

        return $transactionApiId;
    }

    public function isIssued(Cart $cart, mixed $transactionApiId): bool
    {
        if (! is_string($transactionApiId)) {
            return false;
        }

        if ($transactionApiId === '') {
            return false;
        }

        return in_array($transactionApiId, $this->issued($cart), true);
    }

    public function release(Cart $cart, string $transactionApiId): void
    {
        $remaining = array_values(array_diff($this->issued($cart), [$transactionApiId]));

        if ($remaining === []) {
            Cache::forget($this->cacheKey($cart));

            return;
        }

        Cache::put($this->cacheKey($cart), $remaining, self::TTL_SECONDS);
    }

    public function cacheKey(Cart $cart): string
    {
        return "lunar-fortis:elements-binding:cart:{$cart->id}";
    }

    /** @return array<int, string> */
    private function issued(Cart $cart): array
    {
        $issued = Cache::get($this->cacheKey($cart), []);

        if (! is_array($issued)) {
            return [];
        }

        return array_values(array_filter($issued, 'is_string'));
    }
}
