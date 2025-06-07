
# Data 25

## Structure

`Data25`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `rejectedTransactionId` | `?string` | Optional | Rejected Transaction ID.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getRejectedTransactionId(): ?string | setRejectedTransactionId(?string rejectedTransactionId): void |
| `returnFee` | `?int` | Optional | Return Fee.<br><br>**Constraints**: `>= 0`, `<= 999999999` | getReturnFee(): ?int | setReturnFee(?int returnFee): void |
| `id` | `?string` | Optional | Transaction ACH Retry ID.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `retryTransactionId` | `?string` | Optional | Retry Transaction ID.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getRetryTransactionId(): ?string | setRetryTransactionId(?string retryTransactionId): void |
| `returnFeeTransactionId` | `?string` | Optional | Return Fee Transaction ID.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getReturnFeeTransactionId(): ?string | setReturnFeeTransactionId(?string returnFeeTransactionId): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |
| `rejectedTransaction` | [`?RejectedTransaction`](../../doc/models/rejected-transaction.md) | Optional | Transaction Information on `expand` | getRejectedTransaction(): ?RejectedTransaction | setRejectedTransaction(?RejectedTransaction rejectedTransaction): void |
| `retryTransaction` | [`?RetryTransaction`](../../doc/models/retry-transaction.md) | Optional | Transaction Information on `expand` | getRetryTransaction(): ?RetryTransaction | setRetryTransaction(?RetryTransaction retryTransaction): void |
| `returnFeeTransaction` | [`?ReturnFeeTransaction`](../../doc/models/return-fee-transaction.md) | Optional | Transaction Information on `expand` | getReturnFeeTransaction(): ?ReturnFeeTransaction | setReturnFeeTransaction(?ReturnFeeTransaction returnFeeTransaction): void |
| `createdUser` | [`?CreatedUser`](../../doc/models/created-user.md) | Optional | User Information on `expand` | getCreatedUser(): ?CreatedUser | setCreatedUser(?CreatedUser createdUser): void |
| `changelogs` | [`?(Changelog[])`](../../doc/models/changelog.md) | Optional | Changelog Information on `expand` | getChangelogs(): ?array | setChangelogs(?array changelogs): void |

## Example (as JSON)

```json
{
  "rejected_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "retry_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "return_fee_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "return_fee": 98
}
```

