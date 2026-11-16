<?php

namespace Hyrograsper\LunarFortis\Tests\Live;

use Hyrograsper\LunarFortis\Facades\LunarFortis;
use Hyrograsper\LunarFortis\PaymentTypes\FortisPaymentType;
use Hyrograsper\LunarFortis\PaymentTypes\FortisTerminalPaymentType;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Models\Cart;
use RuntimeException;

/**
 * Test-only helper for the opt-in live Fortis sandbox suite.
 *
 * Every value returned from Fortis is reduced to a whitelist so that a failing
 * assertion never prints location, product or user ids.
 */
class LiveFortis
{
    public const string BASE_URL = 'https://api.sandbox.fortis.tech';

    public const string APPROVED_ZIP = '32606';

    public const string AVS_DECLINE_ZIP = '12345';

    private const array REQUIRED_ENV = [
        'FORTIS_USER_ID',
        'FORTIS_USER_API_KEY',
        'FORTIS_DEVELOPER_ID',
        'FORTIS_LOCATION_ID',
        'FORTIS_PRODUCT_TRANSACTION_ID',
    ];

    private const array SAFE_FIELDS = [
        'id',
        'status_code',
        'reason_code_id',
        'type_id',
        'transaction_amount',
        'auth_amount',
        'transaction_api_id',
        'avs_enhanced',
    ];

    public static function enabled(): bool
    {
        if (getenv('FORTIS_LIVE') !== '1') {
            return false;
        }

        if (getenv('FORTIS_ENVIRONMENT') === 'production') {
            return false;
        }

        foreach (self::REQUIRED_ENV as $name) {
            if (! is_string(getenv($name)) || getenv($name) === '') {
                return false;
            }
        }

        return true;
    }

    public static function skipReason(): string
    {
        return 'Live Fortis sandbox tests run only with FORTIS_LIVE=1 and sandbox credentials in the environment.';
    }

    public static function configure(): void
    {
        Config::set('lunar-fortis.environment', 'sandbox');
        Config::set('lunar-fortis.debug', false);
        Config::set('lunar-fortis.http.retry.attempts', 3);
        Config::set('lunar-fortis.elements.require_transaction_binding', false);
        Config::set('services.fortis', [
            'userId' => getenv('FORTIS_USER_ID'),
            'userApiKey' => getenv('FORTIS_USER_API_KEY'),
            'developerId' => getenv('FORTIS_DEVELOPER_ID'),
            'locationId' => getenv('FORTIS_LOCATION_ID'),
            'productTransactionId' => getenv('FORTIS_PRODUCT_TRANSACTION_ID'),
            'terminalProductTransactionId' => getenv('FORTIS_TERMINAL_PRODUCT_TRANSACTION_ID') ?: null,
        ]);
        Config::set('logging.default', 'null');
        Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        Log::setDefaultDriver('null');
    }

    public static function newApiId(): string
    {
        return 'lflive-'.bin2hex(random_bytes(8));
    }

    /** @return array<string, mixed> */
    public static function keyedSale(int $amount, ?string $transactionApiId = null, string $zip = self::APPROVED_ZIP, ?string $productTransactionId = null): array
    {
        return self::keyed('sale', $amount, $transactionApiId, $zip, $productTransactionId);
    }

    /** @return array<string, mixed> */
    public static function keyedAuthOnly(int $amount, ?string $transactionApiId = null, string $zip = self::APPROVED_ZIP): array
    {
        return self::keyed('auth-only', $amount, $transactionApiId, $zip);
    }

    /** @return array<string, mixed> */
    public static function get(string $transactionId, ?string $expand = null): array
    {
        $query = $expand ? ['expand' => $expand] : [];

        $response = self::client()->get(self::BASE_URL."/v1/transactions/{$transactionId}", $query);

        $summary = self::summarise($response);

        if ($expand !== null) {
            $summary['transaction_references'] = array_map(
                fn (array $reference): array => [
                    'transaction_id' => $reference['transaction_id'] ?? null,
                    'transaction_amount' => $reference['transaction_amount'] ?? null,
                    'reference_type' => $reference['reference_type'] ?? null,
                ],
                $response->json('data.transaction_references') ?? [],
            );
            $summary['is_refundable'] = $response->json('data.is_refundable');
            $summary['is_voidable'] = $response->json('data.is_voidable');
            $summary['is_completable'] = $response->json('data.is_completable');
        }

        return $summary;
    }

    /** @return array<string, mixed> */
    public static function refund(string $transactionId, int $amount): array
    {
        $response = self::client()->patch(self::BASE_URL."/v1/transactions/{$transactionId}/refund", [
            'transaction_amount' => $amount,
            'location_id' => getenv('FORTIS_LOCATION_ID'),
        ]);

        $summary = self::summarise($response);

        self::trace("refund {$amount} of {$transactionId}", $summary);

        return $summary;
    }

    /** @return array<string, mixed> */
    public static function void(string $transactionId): array
    {
        $response = self::client()->put(self::BASE_URL."/v1/transactions/{$transactionId}/void", (object) []);

        $summary = self::summarise($response);

        self::trace("void {$transactionId}", $summary);

        return $summary;
    }

    /** @return array<string, mixed> */
    public static function firstTerminal(): ?array
    {
        $response = self::client()->get(self::BASE_URL.'/v1/terminals');

        $terminal = $response->json('list.0');

        if (! is_array($terminal)) {
            return null;
        }

        return [
            'id' => $terminal['id'] ?? null,
            'title' => $terminal['title'] ?? null,
            'active' => $terminal['active'] ?? null,
        ];
    }

    /**
     * Takes a real card-present auth-only on the first simulated terminal at the
     * sandbox location. It never creates, updates or saves a terminal.
     *
     * @return array{id: string, status_code: ?int}
     */
    public static function terminalAuthOnly(int $amount): array
    {
        $terminal = self::firstTerminal();

        if ($terminal === null || ! is_string($terminal['id'])) {
            throw new RuntimeException('No terminal exists in the sandbox location.');
        }

        $outcome = LunarFortis::processTerminalCreditCardAuth($terminal['id'], $amount, [
            'timeout_seconds' => 90,
            'poll_interval_seconds' => 3,
        ]);

        if (! is_string($outcome['transaction_id'] ?? null)) {
            throw new RuntimeException('The terminal did not complete the transaction.');
        }

        $record = self::get($outcome['transaction_id']);

        self::trace('terminal auth-only', $record);

        return ['id' => $outcome['transaction_id'], 'status_code' => $record['status_code'] ?? null];
    }

    public static function authorizeElements(Cart $cart, array $data, string $policy = 'automatic'): PaymentAuthorize
    {
        Config::set('lunar-fortis.policy', $policy);

        return app(FortisPaymentType::class)
            ->cart($cart)
            ->withData($data)
            ->authorize();
    }

    public static function authorizeTerminal(Cart $cart, string $transactionId, string $policy = 'automatic'): PaymentAuthorize
    {
        Config::set('lunar-fortis.policy', $policy);
        Config::set('lunar-fortis.terminal_policy', $policy);

        return app(FortisTerminalPaymentType::class)
            ->cart($cart)
            ->withData(['fortis_transaction_id' => $transactionId])
            ->authorize();
    }

    /** @return array<string, mixed> */
    private static function keyed(string $action, int $amount, ?string $transactionApiId, string $zip, ?string $productTransactionId = null): array
    {
        $body = [
            'transaction_amount' => $amount,
            'account_number' => '4111111111111111',
            'exp_date' => '1230',
            'cvv' => '999',
            'product_transaction_id' => $productTransactionId ?? getenv('FORTIS_PRODUCT_TRANSACTION_ID'),
            'location_id' => getenv('FORTIS_LOCATION_ID'),
            'description' => 'lunar-fortis live break test',
            'billing_address' => ['postal_code' => $zip],
        ];

        if ($transactionApiId !== null) {
            $body['transaction_api_id'] = $transactionApiId;
        }

        $response = self::client()->post(self::BASE_URL."/v1/transactions/cc/{$action}/keyed", $body);

        $summary = self::summarise($response);

        self::trace("{$action} {$amount}", $summary);

        return $summary;
    }

    private static function trace(string $label, array $summary): void
    {
        fwrite(STDERR, sprintf(
            "[live] %s -> http %s id %s status %s reason %s\n",
            $label,
            $summary['http'] ?? '-',
            $summary['id'] ?? '-',
            $summary['status_code'] ?? '-',
            $summary['reason_code_id'] ?? '-',
        ));
    }

    /** @return array<string, mixed> */
    private static function summarise(Response $response): array
    {
        $data = $response->json('data');

        $summary = is_array($data)
            ? array_intersect_key($data, array_flip(self::SAFE_FIELDS))
            : [];

        $summary['http'] = $response->status();

        $errors = $response->json('meta.errors');

        if (is_array($errors)) {
            $summary['errors'] = $errors;
        }

        $detail = $response->json('detail');

        if (! $response->successful() && is_string($detail)) {
            $summary['detail'] = $detail;
        }

        return $summary;
    }

    private static function client(): PendingRequest
    {
        return Http::withHeaders([
            'user-id' => getenv('FORTIS_USER_ID'),
            'user-api-key' => getenv('FORTIS_USER_API_KEY'),
            'developer-id' => getenv('FORTIS_DEVELOPER_ID'),
        ])
            ->acceptJson()
            ->asJson()
            ->timeout(60);
    }
}
