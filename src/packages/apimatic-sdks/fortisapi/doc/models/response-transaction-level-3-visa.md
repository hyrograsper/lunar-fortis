
# Response Transaction Level 3 Visa

## Structure

`ResponseTransactionLevel3Visa`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type110Enum)`](../../doc/models/type-110-enum.md) | Optional | Resource Type<br><br>**Default**: `Type110Enum::TRANSACTIONLEVEL3VISA` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data28`](../../doc/models/data-28.md) | Optional | - | getData(): ?Data28 | setData(?Data28 data): void |

## Example (as JSON)

```json
{
  "type": "TransactionLevel3Visa",
  "data": {
    "id": "id0",
    "transaction_id": "transaction_id8",
    "level3_data": {
      "destination_country_code": "destination_country_code4",
      "duty_amount": 182,
      "freight_amount": 60,
      "national_tax": 999999998900,
      "sales_tax": 999999998900
    }
  }
}
```

