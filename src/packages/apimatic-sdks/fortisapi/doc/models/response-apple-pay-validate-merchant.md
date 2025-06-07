
# Response Apple Pay Validate Merchant

## Structure

`ResponseApplePayValidateMerchant`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type131Enum)`](../../doc/models/type-131-enum.md) | Optional | Resource Type<br><br>**Default**: `Type131Enum::APPLEPAYVALIDATEMERCHANT` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data36`](../../doc/models/data-36.md) | Optional | - | getData(): ?Data36 | setData(?Data36 data): void |

## Example (as JSON)

```json
{
  "type": "ApplePayValidateMerchant",
  "data": {
    "merchantSession": "merchantSession0"
  }
}
```

