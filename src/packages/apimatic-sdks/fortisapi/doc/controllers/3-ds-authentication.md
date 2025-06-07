# 3 DS Authentication

```php
$m3DSAuthenticationController = $client->getM3DSAuthenticationController();
```

## Class Name

`M3DSAuthenticationController`


# 3 DS Authentication Request

Makes a 3DS Authentication request to authenticate a card or begin the challenge flow.  If a challenge is required, a POST should be made to acs_url using the value of base64_encoded_challenge_request for the value of "creq" using x-www-form-urlencoded for the challenge request to the ACS.

```php
function m3DSAuthenticationRequest(
    V1MerchantThreedsecureAuthenticationRequest $body
): ResponseThreeDSAuthentication
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `body` | [`V1MerchantThreedsecureAuthenticationRequest`](../../doc/models/v1-merchant-threedsecure-authentication-request.md) | Body, Required | - |

## Response Type

[`ResponseThreeDSAuthentication`](../../doc/models/response-three-ds-authentication.md)

## Example Usage

```php
$body = V1MerchantThreedsecureAuthenticationRequestBuilder::init(
    '11ee3860e2fc7f5ea67d36b3',
    DeviceChannelEnum::ENUM_02,
    MessageCategoryEnum::ENUM_01,
    ThreeDsRequestorBuilder::init(
        ThreeDsRequestorAuthenticationIndEnum::ENUM_01
    )
        ->threeDsRequestorChallengeInd(
            [
                ThreeDsRequestorChallengeIndEnum::ENUM_03
            ]
        )
        ->build(),
    CardholderAccountBuilder::init(
        '5454545454545454',
        SchemeIdEnum::VISA
    )
        ->expireDate('2508')
        ->build()
)
    ->preferredProtocolVersion(PreferredProtocolVersionEnum::ENUM_220)
    ->enforcePreferredProtocolVersion(true)
    ->threeDsCompInd('Y')
    ->build();

$result = $m3DSAuthenticationController->m3DSAuthenticationRequest($body);
```

## Example Response *(as JSON)*

```json
{
  "type": "ThreeDSAuthentication",
  "data": {
    "three_ds_server_trans_id": "ea0c9973-4cd0-4f9f-83b3-609bf22c69e7",
    "acs_url": "https://api.sandbox.fortis.tech/v1/acs/challenge",
    "transaction_status": "C",
    "ds_trans_id": "278c7508-cac6-4233-902d-b90f31f741c6",
    "acs_trans_id": "d7c1ee99-9478-44a6-b1f2-391e29c6b340",
    "message_version": "2.2.0",
    "acs_challenge_mandated": "Y",
    "purchase_date": "20240509111532",
    "base64_encoded_challenge_request": "eyJtZXNzYWdlVHlwZSI6IkNSZXEiLCJ0aHJlZURTU2VydmVyVHJhbnNJRCI6ImVhMGM5OTczLTRjZDAtNGY5Zi04M2IzLTYwOWJmMjJjNjllNyIsImFjc1RyYW5zSUQiOiIwNWViNTE2OS02ZTFkLTQ5NTMtYWQ3NC1hZWU5YmQ4ZTc1YmIiLCJjaGFsbGVuZ2VXaW5kb3dTaXplIjoiMDEiLCJtZXNzYWdlVmVyc2lvbiI6IjIuMi4wIn0="
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 400 | Bad Request | [`ResponseErrorException`](../../doc/models/response-error-exception.md) |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |
| 412 | Precondition Failed | [`Response412Exception`](../../doc/models/response-412-exception.md) |

