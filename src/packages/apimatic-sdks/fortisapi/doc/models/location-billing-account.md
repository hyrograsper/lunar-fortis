
# Location Billing Account

## Structure

`LocationBillingAccount`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `title` | `?string` | Optional | Title<br><br>**Constraints**: *Maximum Length*: `64` | getTitle(): ?string | setTitle(?string title): void |
| `locationId` | `?string` | Optional | Location ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationId(): ?string | setLocationId(?string locationId): void |
| `locationApiId` | `?string` | Optional | Location Api Id | getLocationApiId(): ?string | setLocationApiId(?string locationApiId): void |
| `achSecCode` | `?string` | Optional | Ach Sec Code | getAchSecCode(): ?string | setAchSecCode(?string achSecCode): void |
| `accountNumber` | `?string` | Optional | Account number | getAccountNumber(): ?string | setAccountNumber(?string accountNumber): void |
| `routing` | `?string` | Optional | Routing | getRouting(): ?string | setRouting(?string routing): void |
| `expDate` | `?string` | Optional | Exp Date | getExpDate(): ?string | setExpDate(?string expDate): void |
| `billingZip` | `?string` | Optional | Billing Zip<br><br>**Constraints**: *Minimum Length*: `3`, *Maximum Length*: `10`, *Pattern*: `^[a-zA-Z0-9\-\s]+$` | getBillingZip(): ?string | setBillingZip(?string billingZip): void |
| `accountType` | `?string` | Optional | Account Type | getAccountType(): ?string | setAccountType(?string accountType): void |
| `accountHolderName` | `?string` | Optional | Account Holder Name | getAccountHolderName(): ?string | setAccountHolderName(?string accountHolderName): void |
| `id` | `?string` | Optional | Location Billing Account Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `modifiedTs` | `?int` | Optional | Modified Time Stamp | getModifiedTs(): ?int | setModifiedTs(?int modifiedTs): void |
| `createdUserId` | `?string` | Optional | Created User Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |
| `modifiedUserId` | `?string` | Optional | Modified User Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getModifiedUserId(): ?string | setModifiedUserId(?string modifiedUserId): void |
| `billingDescriptor` | `?string` | Optional | Billing Descriptor | getBillingDescriptor(): ?string | setBillingDescriptor(?string billingDescriptor): void |
| `paymentMethod` | `?string` | Optional | Billing Descriptor | getPaymentMethod(): ?string | setPaymentMethod(?string paymentMethod): void |
| `portfolioId` | `?string` | Optional | Portfolio Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getPortfolioId(): ?string | setPortfolioId(?string portfolioId): void |

## Example (as JSON)

```json
{
  "title": "Billing Acccount Title",
  "location_id": "11e95f8ec39de8fbdb0a4f1a",
  "exp_date": "0722",
  "billing_zip": "48375",
  "account_holder_name": "John Smith",
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "modified_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "portfolio_id": "11e95f8ec39de8fbdb0a4f1a",
  "location_api_id": "location_api_id4",
  "ach_sec_code": "ach_sec_code6",
  "account_number": "account_number4"
}
```

