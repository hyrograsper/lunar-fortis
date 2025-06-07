# Apple Pay Validate Merchant

```php
$applePayValidateMerchantController = $client->getApplePayValidateMerchantController();
```

## Class Name

`ApplePayValidateMerchantController`


# Apple Pay Validate Merchant

Apple Pay Validate Merchant

```php
function applePayValidateMerchant(
    V1WalletProviderApplePayValidateMerchantRequest $body
): ResponseApplePayValidateMerchant
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `body` | [`V1WalletProviderApplePayValidateMerchantRequest`](../../doc/models/v1-wallet-provider-apple-pay-validate-merchant-request.md) | Body, Required | - |

## Response Type

[`ResponseApplePayValidateMerchant`](../../doc/models/response-apple-pay-validate-merchant.md)

## Example Usage

```php
$body = V1WalletProviderApplePayValidateMerchantRequestBuilder::init(
    'https://apple-pay-gateway-cert.apple.com/paymentservices/startSession',
    'abc1234',
    'website.domain.com',
    'Sandwich Market'
)->build();

$result = $applePayValidateMerchantController->applePayValidateMerchant($body);
```

## Example Response *(as JSON)*

```json
{
  "type": "ApplePayValidateMerchant",
  "data": {
    "merchantSession": "{\"epochTimestamp\":1689772866529,\"expiresAt\":1689776466529,\"merchantSessionIdentifier\":\"SSH3D9224\",\"nonce\":\"d70dbe8a\",\"merchantIdentifier\":\"46A940\",\"domainName\":\"paygistixcert.paymentlogistics.net\",\"displayName\":\"F\",\"signature\":\"30800609f6e2\",\"operationalAnalyticsIdentifier\":\"F:46A4E40\",\"retries\":0,\"pspId\":\"ADD36D\"}"
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |
| 412 | Precondition Failed | [`Response412Exception`](../../doc/models/response-412-exception.md) |

