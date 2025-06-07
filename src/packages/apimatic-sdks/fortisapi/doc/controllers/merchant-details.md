# Merchant Details

```php
$merchantDetailsController = $client->getMerchantDetailsController();
```

## Class Name

`MerchantDetailsController`


# Merchant Details

Merchant Details

```php
function merchantDetails(V1WalletProviderMerchantDetailsRequest $body): ResponseMerchantDetails
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `body` | [`V1WalletProviderMerchantDetailsRequest`](../../doc/models/v1-wallet-provider-merchant-details-request.md) | Body, Required | - |

## Response Type

[`ResponseMerchantDetails`](../../doc/models/response-merchant-details.md)

## Example Usage

```php
$body = V1WalletProviderMerchantDetailsRequestBuilder::init(
    'dev.pay.site'
)->build();

$result = $merchantDetailsController->merchantDetails($body);
```

## Example Response *(as JSON)*

```json
{
  "type": "MerchantDetails",
  "data": {
    "resultCode": false,
    "merchantID": "abc1234",
    "applePay": true,
    "googlePay": true,
    "applePayDomains": [
      null
    ],
    "message": "valid user",
    "googleJWT": "45r8v29bvj4gc904jfd932nm"
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |
| 412 | Precondition Failed | [`Response412Exception`](../../doc/models/response-412-exception.md) |

