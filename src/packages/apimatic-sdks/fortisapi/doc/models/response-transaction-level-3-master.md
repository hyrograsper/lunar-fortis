
# Response Transaction Level 3 Master

## Structure

`ResponseTransactionLevel3Master`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type109Enum)`](../../doc/models/type-109-enum.md) | Optional | Resource Type<br><br>**Default**: `Type109Enum::TRANSACTIONLEVEL3MASTER` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data28`](../../doc/models/data-28.md) | Optional | - | getData(): ?Data28 | setData(?Data28 data): void |

## Example (as JSON)

```json
{
  "type": "TransactionLevel3Master",
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

