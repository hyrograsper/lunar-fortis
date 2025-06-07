
# Data 4

## Structure

`Data4`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `declinedRecurringTransactionId` | `?string` | Optional | Declined Recurring Transaction Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getDeclinedRecurringTransactionId(): ?string | setDeclinedRecurringTransactionId(?string declinedRecurringTransactionId): void |
| `accountNumber` | `?string` | Optional | Account Number<br><br>**Constraints**: *Minimum Length*: `13`, *Maximum Length*: `19` | getAccountNumber(): ?string | setAccountNumber(?string accountNumber): void |
| `accountHolderName` | `?string` | Optional | Account Holder Name | getAccountHolderName(): ?string | setAccountHolderName(?string accountHolderName): void |
| `expDate` | `?string` | Optional | Exp Date<br><br>**Constraints**: *Maximum Length*: `4` | getExpDate(): ?string | setExpDate(?string expDate): void |
| `transactionAmount` | `?int` | Optional | Transaction Amount<br><br>**Constraints**: `>= 0`, `<= 999999999` | getTransactionAmount(): ?int | setTransactionAmount(?int transactionAmount): void |
| `description` | `?string` | Optional | Description<br><br>**Constraints**: *Maximum Length*: `255` | getDescription(): ?string | setDescription(?string description): void |
| `billingAddress` | [`?BillingAddress`](../../doc/models/billing-address.md) | Optional | Billing Address Object | getBillingAddress(): ?BillingAddress | setBillingAddress(?BillingAddress billingAddress): void |
| `tags` | `?(string[])` | Optional | Tags | getTags(): ?array | setTags(?array tags): void |
| `id` | `?string` | Optional | Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `firstSix` | `?string` | Optional | First Six<br><br>**Constraints**: *Maximum Length*: `6` | getFirstSix(): ?string | setFirstSix(?string firstSix): void |
| `lastFour` | `?string` | Optional | Last Four<br><br>**Constraints**: *Maximum Length*: `4` | getLastFour(): ?string | setLastFour(?string lastFour): void |
| `routing` | `?string` | Optional | Routing | getRouting(): ?string | setRouting(?string routing): void |
| `statusId` | `?float` | Optional | Status Id | getStatusId(): ?float | setStatusId(?float statusId): void |
| `reasonCodeId` | [`?int(ReasonCodeIdEnum)`](../../doc/models/reason-code-id-enum.md) | Optional | Reason Code Id | getReasonCodeId(): ?int | setReasonCodeId(?int reasonCodeId): void |
| `typeId` | `?float` | Optional | Type Id | getTypeId(): ?float | setTypeId(?float typeId): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |

## Example (as JSON)

```json
{
  "declined_recurring_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "account_number": "5454545454545454",
  "account_holder_name": "John Doe",
  "exp_date": "0722",
  "transaction_amount": 0,
  "description": "Description",
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "first_six": "700953",
  "last_four": "3657",
  "reason_code_id": 1000,
  "created_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
}
```

