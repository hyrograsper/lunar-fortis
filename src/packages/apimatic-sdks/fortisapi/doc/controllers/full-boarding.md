# Full Boarding

```php
$fullBoardingController = $client->getFullBoardingController();
```

## Class Name

`FullBoardingController`


# Merchant Boarding Full

This method is used to fully board a merchant to the platform. When using this method, you must provide data for each "Required" property. See the description for each of these properties for more information about their requirement criteria.

```php
function merchantBoardingFull(V1FullboardingRequest $body): ResponseFullboarding
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `body` | [`V1FullboardingRequest`](../../doc/models/v1-fullboarding-request.md) | Body, Required | - |

## Response Type

[`ResponseFullboarding`](../../doc/models/response-fullboarding.md)

## Example Usage

```php
$body = V1FullboardingRequestBuilder::init(
    'email@domain.com',
    'Discount Home Goods',
    '5555551234',
    OwnershipTypeEnum::LLP,
    '0000000000',
    15,
    150,
    100,
    '7629',
    'Yard services.',
    0,
    0,
    100,
    true,
    false,
    [
        Address81Builder::init(
            '121 E Main',
            'Dallas',
            'TX',
            '75087',
            'US',
            AddressTypeEnum::LOCATION
        )
            ->addressLine2('Apt 707')
            ->build()
    ],
    [
        OwnerBuilder::init(
            'James',
            'Bond',
            'CEO',
            '2021-12-01',
            '133 S Goliad St',
            'Suite 120',
            'Rockwall',
            'TX',
            '75429',
            'US',
            '000000000',
            100,
            '9042142323',
            'james@example.com',
            true,
            true
        )
            ->middleName('Tyler')
            ->build()
    ],
    [
        BankAccount1Builder::init(
            'James Bond',
            '111111111',
            '1234567',
            AccountType12Enum::CHECKING
        )
            ->isPrimary(true)
            ->build()
    ]
)
    ->templateId('1234YourTemplateCode')
    ->clientAppId('ABC123')
    ->legalName('Total Home Goods, LLP')
    ->website('http://www.example.com')
    ->ecMonthlyVolume(22)
    ->preferredLanguage(PreferredLanguageEnum::FRCA)
    ->signerIp('192.168.0.10')
    ->build();

$result = $fullBoardingController->merchantBoardingFull($body);
```

## Example Response *(as JSON)*

```json
{
  "type": "Fullboarding",
  "data": {
    "result": {
      "client_app_id": "ABC123",
      "dba_name": "Discount Home Goods",
      "email": "jtodd@example.com"
    },
    "status": {
      "response_code": "Received"
    }
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |
| 412 | Precondition Failed | [`Response412Exception`](../../doc/models/response-412-exception.md) |

