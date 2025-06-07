
# Response Declined Recurring Transaction Resend

## Structure

`ResponseDeclinedRecurringTransactionResend`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type21Enum)`](../../doc/models/type-21-enum.md) | Optional | Resource Type<br><br>**Default**: `Type21Enum::DECLINEDRECURRINGTRANSACTIONRESEND` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data5`](../../doc/models/data-5.md) | Optional | - | getData(): ?Data5 | setData(?Data5 data): void |

## Example (as JSON)

```json
{
  "type": "DeclinedRecurringTransactionResend",
  "data": {
    "id": "id0",
    "email_log_id": "email_log_id2",
    "success": false,
    "email": "email6"
  }
}
```

