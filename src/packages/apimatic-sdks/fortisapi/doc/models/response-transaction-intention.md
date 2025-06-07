
# Response Transaction Intention

## Structure

`ResponseTransactionIntention`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type28Enum)`](../../doc/models/type-28-enum.md) | Optional | Resource Type<br><br>**Default**: `Type28Enum::TRANSACTIONINTENTION` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data8`](../../doc/models/data-8.md) | Optional | - | getData(): ?Data8 | setData(?Data8 data): void |

## Example (as JSON)

```json
{
  "type": "TransactionIntention",
  "data": {
    "action": "refund",
    "digitalWalletsOnly": false,
    "methods": [
      {
        "type": "ach",
        "product_transaction_id": "product_transaction_id4"
      }
    ],
    "amount": 236,
    "tax_amount": 62
  }
}
```

