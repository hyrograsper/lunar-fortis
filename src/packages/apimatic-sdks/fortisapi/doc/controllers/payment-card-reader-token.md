# Payment Card Reader Token

```php
$paymentCardReaderTokenController = $client->getPaymentCardReaderTokenController();
```

## Class Name

`PaymentCardReaderTokenController`


# Payment Card Reader Token Request

For initializing iPhone card readers for Apple Tap to Pay transactions

```php
function paymentCardReaderTokenRequest(string $productTransactionId): ResponsePaymentCardReaderToken
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `productTransactionId` | `string` | Query, Required | Product Transaction ID to be used to initialize the card reader<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` |

## Response Type

[`ResponsePaymentCardReaderToken`](../../doc/models/response-payment-card-reader-token.md)

## Example Usage

```php
$productTransactionId = '11e95f8ec39de8fbdb0a4f1a';

$result = $paymentCardReaderTokenController->paymentCardReaderTokenRequest($productTransactionId);
```

## Example Response *(as JSON)*

```json
{
  "type": "PaymentCardReaderToken",
  "data": {}
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |

