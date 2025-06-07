
# Data 3

## Structure

`Data3`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `id` | `?string` | Optional | Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `declinedTransactionId` | `?string` | Optional | Declined Transaction Id | getDeclinedTransactionId(): ?string | setDeclinedTransactionId(?string declinedTransactionId): void |
| `paymentTransactionId` | `?string` | Optional | Payment Transaction Id | getPaymentTransactionId(): ?string | setPaymentTransactionId(?string paymentTransactionId): void |
| `status` | [`?string(Status3Enum)`](../../doc/models/status-3-enum.md) | Optional | Status | getStatus(): ?string | setStatus(?string status): void |
| `recurringId` | `?string` | Optional | Recurring Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getRecurringId(): ?string | setRecurringId(?string recurringId): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |
| `modifiedTs` | `?int` | Optional | Modified Time Stamp | getModifiedTs(): ?int | setModifiedTs(?int modifiedTs): void |
| `modifiedUserId` | `?string` | Optional | Last User ID that updated the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getModifiedUserId(): ?string | setModifiedUserId(?string modifiedUserId): void |

## Example (as JSON)

```json
{
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "status": "paid",
  "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "modified_ts": 1422040992,
  "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "declined_transaction_id": "declined_transaction_id4",
  "payment_transaction_id": "payment_transaction_id8"
}
```

