<?php

namespace Hyrograsper\LunarFortis;

use FortisAPILib\Authentication\DeveloperIdCredentialsBuilder;
use FortisAPILib\Authentication\UserApiKeyCredentialsBuilder;
use FortisAPILib\Authentication\UserIdCredentialsBuilder;
use FortisAPILib\Environment;
use FortisAPILib\Exceptions\ApiException;
use FortisAPILib\FortisAPIClient;
use FortisAPILib\FortisAPIClientBuilder;
use FortisAPILib\Models\ActionEnum;
use FortisAPILib\Models\Builders\BillingAddress1Builder;
use FortisAPILib\Models\Builders\V1ElementsTransactionIntentionRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsCcAuthOnlyTokenRequestBuilder;
use FortisAPILib\Models\Builders\V1TransactionsCcRefundKeyedRequestBuilder;
use FortisAPILib\Models\ResponseTransaction;
use FortisAPILib\Models\V1ElementsTransactionIntentionRequest;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\Transaction;

class LunarFortis
{
    protected ?FortisAPIClient $client = null;

    protected function getClientInstance(): FortisAPIClient
    {
        if ($this->client) {
            return $this->client;
        }

        return $this->client = FortisAPIClientBuilder::init()
            ->environment(config('lunar.fortis.environment') == 'production'
                ? Environment::PRODUCTION
                : Environment::SANDBOX
            )
            ->userIdCredentials(
                UserIdCredentialsBuilder::init(
                    userId: config('services.fortis.userId'),
                )
            )
            ->userApiKeyCredentials(
                UserApiKeyCredentialsBuilder::init(
                    userApiKey: config('services.fortis.userApiKey'),
                )
            )
            ->developerIdCredentials(
                DeveloperIdCredentialsBuilder::init(
                    developerId: config('services.fortis.developerId'),
                )
            )
            ->build();
    }

    protected function buildTransactionIntentionRequest(int $amount): V1ElementsTransactionIntentionRequest
    {
        return V1ElementsTransactionIntentionRequestBuilder::init()
            ->action(ActionEnum::SALE)
            ->digitalWalletsOnly(false)
            ->amount($amount)
            ->locationId(config('services.fortis.locationId'))
            ->build();
    }

    public function getClientTokenForSaleAmount(int $amount): ?string
    {
        try {
            $data = $this->getClientInstance()
                ->getElementsController()
                ->transactionIntention(
                    body: $this->buildTransactionIntentionRequest($amount)
                )->getData();

            $token = $data->getClientToken();

            return $token;
        } catch (Exception $e) {
            Log::error("Unable to get client token for sale: {$e->getMessage()}");

            return null;
        }
    }

    public function capturePreviousTransaction() {}

    /**
     * @throws ApiException
     */
    public function authorizeCcFromToken(string $tokenId, Order $order): ResponseTransaction
    {
        /** @var OrderAddress $billingAddress */
        $billingAddress = $order->billingAddress;

        return $this->getClientInstance()
            ->getTransactionsCreditCardController()
            ->ccAuthOnlyTokenized(
                V1TransactionsCcAuthOnlyTokenRequestBuilder::init($order->total->value)
                    ->tokenId($tokenId)
                    ->orderNumber($order->reference)
                    ->billingAddress(
                        BillingAddress1Builder::init()
                            ->street($billingAddress->line_one)
                            ->city($billingAddress->city)
                            ->state($billingAddress->state)
                            ->postalCode($billingAddress->postcode)
                            ->country($billingAddress->country->iso3)
                            ->build()
                    )
                    ->customerId($order->customer_id)
                    ->build()
            );
    }

    /**
     * @throws ApiException
     */
    public function refund(Transaction $transaction, int $amount): ResponseTransaction
    {
        return $this->getClientInstance()
            ->getTransactionsCreditCardController()
            ->cCRefund(
                V1TransactionsCcRefundKeyedRequestBuilder::init($amount)
                    ->previousTransactionId($transaction->reference)
                    ->locationId(config('services.fortis.locationId'))
                    ->orderNumber($transaction->reference)
                    ->cvv(999)
                    ->build()
            );
    }
}
