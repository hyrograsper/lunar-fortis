
# Response Transaction Ach Retry

## Structure

`ResponseTransactionAchRetry`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type94Enum)`](../../doc/models/type-94-enum.md) | Optional | Resource Type<br><br>**Default**: `Type94Enum::TRANSACTIONACHRETRY` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data25`](../../doc/models/data-25.md) | Optional | - | getData(): ?Data25 | setData(?Data25 data): void |

## Example (as JSON)

```json
{
  "type": "TransactionAchRetry",
  "data": {
    "rejected_transaction_id": "rejected_transaction_id4",
    "return_fee": 208,
    "id": "id0",
    "retry_transaction_id": "retry_transaction_id6",
    "return_fee_transaction_id": "return_fee_transaction_id4"
  }
}
```

