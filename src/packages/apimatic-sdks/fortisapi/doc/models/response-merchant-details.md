
# Response Merchant Details

## Structure

`ResponseMerchantDetails`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type132Enum)`](../../doc/models/type-132-enum.md) | Optional | Resource Type<br><br>**Default**: `Type132Enum::MERCHANTDETAILS` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data37`](../../doc/models/data-37.md) | Optional | - | getData(): ?Data37 | setData(?Data37 data): void |

## Example (as JSON)

```json
{
  "type": "MerchantDetails",
  "data": {
    "resultCode": false,
    "merchantID": "merchantID8",
    "applePay": false,
    "googlePay": false,
    "applePayDomains": [
      {
        "key1": "val1",
        "key2": "val2"
      }
    ]
  }
}
```

