
# Response Payment Card Reader Token

## Structure

`ResponsePaymentCardReaderToken`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type53Enum)`](../../doc/models/type-53-enum.md) | Optional | Resource Type<br><br>**Default**: `Type53Enum::PAYMENTCARDREADERTOKEN` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data16`](../../doc/models/data-16.md) | Optional | - | getData(): ?Data16 | setData(?Data16 data): void |

## Example (as JSON)

```json
{
  "type": "PaymentCardReaderToken",
  "data": {
    "token": "token4"
  }
}
```

