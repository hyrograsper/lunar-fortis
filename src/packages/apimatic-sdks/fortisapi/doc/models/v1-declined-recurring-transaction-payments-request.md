
# V1 Declined Recurring Transaction Payments Request

## Structure

`V1DeclinedRecurringTransactionPaymentsRequest`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `declinedRecurringTransactionId` | `string` | Required | Declined Recurring Transaction Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getDeclinedRecurringTransactionId(): string | setDeclinedRecurringTransactionId(string declinedRecurringTransactionId): void |
| `accountNumber` | `string` | Required | Account Number<br><br>**Constraints**: *Minimum Length*: `13`, *Maximum Length*: `19` | getAccountNumber(): string | setAccountNumber(string accountNumber): void |
| `accountHolderName` | `?string` | Optional | Account Holder Name | getAccountHolderName(): ?string | setAccountHolderName(?string accountHolderName): void |
| `expDate` | `string` | Required | Exp Date<br><br>**Constraints**: *Maximum Length*: `4` | getExpDate(): string | setExpDate(string expDate): void |
| `transactionAmount` | `int` | Required | Transaction Amount<br><br>**Constraints**: `>= 0`, `<= 999999999` | getTransactionAmount(): int | setTransactionAmount(int transactionAmount): void |
| `description` | `?string` | Optional | Description<br><br>**Constraints**: *Maximum Length*: `255` | getDescription(): ?string | setDescription(?string description): void |
| `billingAddress` | [`?BillingAddress`](../../doc/models/billing-address.md) | Optional | Billing Address Object | getBillingAddress(): ?BillingAddress | setBillingAddress(?BillingAddress billingAddress): void |
| `tags` | `?(string[])` | Optional | Tags | getTags(): ?array | setTags(?array tags): void |
| `replaceAccountVault` | `?bool` | Optional | Replace AccountVault | getReplaceAccountVault(): ?bool | setReplaceAccountVault(?bool replaceAccountVault): void |
| `saveAccount` | `?bool` | Optional | Specifies to save account to contacts profile if account_number/track_data is present with either contact_id or contact_api_id in params. | getSaveAccount(): ?bool | setSaveAccount(?bool saveAccount): void |
| `saveAccountTitle` | `?string` | Optional | If saving token while running a transaction, this will be the title of the token.<br><br>**Constraints**: *Maximum Length*: `16` | getSaveAccountTitle(): ?string | setSaveAccountTitle(?string saveAccountTitle): void |
| `subtotalAmount` | `?int` | Optional | Subtotal Amount<br><br>**Constraints**: `>= 0`, `<= 999999999` | getSubtotalAmount(): ?int | setSubtotalAmount(?int subtotalAmount): void |
| `surchargeAmount` | `?int` | Optional | Surcharge Amount<br><br>**Constraints**: `>= 0`, `<= 999999999` | getSurchargeAmount(): ?int | setSurchargeAmount(?int surchargeAmount): void |

## Example (as JSON)

```json
{
  "declined_recurring_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "account_number": "5454545454545454",
  "account_holder_name": "John Doe",
  "exp_date": "0722",
  "transaction_amount": 0,
  "description": "Description",
  "save_account_title": "John Account",
  "subtotal_amount": 599,
  "surcharge_amount": 599,
  "billing_address": {
    "postal_code": "postal_code0",
    "street": "street8",
    "city": "city2",
    "state": "state6",
    "phone": "phone2"
  },
  "tags": [
    "tags3"
  ],
  "replace_account_vault": false
}
```

