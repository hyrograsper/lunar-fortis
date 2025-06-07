
# Response Paylink

## Structure

`ResponsePaylink`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type48Enum)`](../../doc/models/type-48-enum.md) | Optional | Resource Type<br><br>**Default**: `Type48Enum::PAYLINK` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data15`](../../doc/models/data-15.md) | Optional | - | getData(): ?Data15 | setData(?Data15 data): void |

## Example (as JSON)

```json
{
  "type": "Paylink",
  "data": {
    "location_id": "location_id4",
    "cc_product_transaction_id": "cc_product_transaction_id2",
    "email": "email6",
    "amount_due": 196,
    "location_api_id": "location_api_id0"
  }
}
```

