<?php

namespace Hyrograsper\LunarFortis\Services;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Lunar\Models\Cart;

class ElementsTransactionBinding
{
    private const string PREFIX = 'lf-';

    private const int MAX_ISSUED_PER_CART = 50;

    private const int TTL_SECONDS = 3600;

    private const int LOCK_SECONDS = 10;

    public function __construct(
        private readonly int $lockWaitSeconds = 2,
    ) {}

    public function issue(Cart $cart): string
    {
        $transactionApiId = self::PREFIX.bin2hex(random_bytes(16));

        $this->whileLocked($cart, function () use ($cart, $transactionApiId): void {
            $issued = array_slice($this->issued($cart), -(self::MAX_ISSUED_PER_CART - 1));
            $issued[] = $transactionApiId;

            Cache::put($this->cacheKey($cart), $issued, self::TTL_SECONDS);
        });

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
        $this->whileLocked($cart, function () use ($cart, $transactionApiId): void {
            $remaining = array_values(array_diff($this->issued($cart), [$transactionApiId]));

            if ($remaining === []) {
                Cache::forget($this->cacheKey($cart));

                return;
            }

            Cache::put($this->cacheKey($cart), $remaining, self::TTL_SECONDS);
        });
    }

    public function cacheKey(Cart $cart): string
    {
        return "lunar-fortis:elements-binding:cart:{$cart->id}";
    }

    private function whileLocked(Cart $cart, Closure $callback): void
    {
        try {
            Cache::lock("{$this->cacheKey($cart)}:lock", self::LOCK_SECONDS)->block($this->lockWaitSeconds, $callback);
        } catch (LockTimeoutException) {
            $callback();
        }
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
