
# V1 Elements Transaction Intention Request

## Structure

`V1ElementsTransactionIntentionRequest`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `action` | [`?string(ActionEnum)`](../../doc/models/action-enum.md) | Optional | The action to be performed<br><br>**Default**: `ActionEnum::SALE` | getAction(): ?string | setAction(?string action): void |
| `digitalWalletsOnly` | `?bool` | Optional | **Default**: `false` | getDigitalWalletsOnly(): ?bool | setDigitalWalletsOnly(?bool digitalWalletsOnly): void |
| `methods` | [`?(Method50[])`](../../doc/models/method-50.md) | Optional | By default the system will try to offer all the availables payment methods from your account. But if you like, you can specify exactly what services you want to use.<br><br>**Constraints**: *Minimum Items*: `1`, *Unique Items Required* | getMethods(): ?array | setMethods(?array methods): void |
| `amount` | `?int` | Optional | The total amount to be charged. Allowed on the actions: `sale`, `auth-only`, `refund`<br><br>**Constraints**: `>= 1`, `<= 999999999` | getAmount(): ?int | setAmount(?int amount): void |
| `taxAmount` | int\|null | Optional | This is a container for any-of cases.<br><br>**Constraints**: `>= 1`, `<= 999999999` | getTaxAmount(): ?int | setTaxAmount(?int taxAmount): void |
| `secondaryAmount` | int\|null | Optional | This is a container for any-of cases.<br><br>**Constraints**: `>= 0`, `<= 999999999` | getSecondaryAmount(): ?int | setSecondaryAmount(?int secondaryAmount): void |
| `locationId` | `?string` | Optional | Location ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationId(): ?string | setLocationId(?string locationId): void |
| `contactId` | `?string` | Optional | Contact ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getContactId(): ?string | setContactId(?string contactId): void |
| `saveAccount` | bool\|null | Optional | This is a container for any-of cases. | getSaveAccount(): ?bool | setSaveAccount(?bool saveAccount): void |
| `saveAccountTitle` | string\|null | Optional | This is a container for any-of cases.<br><br>**Constraints**: *Maximum Length*: `16` | getSaveAccountTitle(): ?string | setSaveAccountTitle(?string saveAccountTitle): void |
| `title` | string\|null | Optional | This is a container for any-of cases.<br><br>**Constraints**: *Maximum Length*: `16` | getTitle(): ?string | setTitle(?string title): void |
| `achSecCode` | [`?string(AchSecCodeEnum)`](../../doc/models/ach-sec-code-enum.md) | Optional | SEC code for the transaction if it's an ACH transaction<br><br>**Default**: `AchSecCodeEnum::WEB` | getAchSecCode(): ?string | setAchSecCode(?string achSecCode): void |
| `bankFundedOnlyOverride` | bool\|null | Optional | This is a container for any-of cases. | getBankFundedOnlyOverride(): ?bool | setBankFundedOnlyOverride(?bool bankFundedOnlyOverride): void |
| `allowPartialAuthorizationOverride` | bool\|null | Optional | This is a container for any-of cases. | getAllowPartialAuthorizationOverride(): ?bool | setAllowPartialAuthorizationOverride(?bool allowPartialAuthorizationOverride): void |
| `autoDeclineCvvOverride` | bool\|null | Optional | This is a container for any-of cases. | getAutoDeclineCvvOverride(): ?bool | setAutoDeclineCvvOverride(?bool autoDeclineCvvOverride): void |
| `autoDeclineStreetOverride` | bool\|null | Optional | This is a container for any-of cases. | getAutoDeclineStreetOverride(): ?bool | setAutoDeclineStreetOverride(?bool autoDeclineStreetOverride): void |
| `autoDeclineZipOverride` | bool\|null | Optional | This is a container for any-of cases. | getAutoDeclineZipOverride(): ?bool | setAutoDeclineZipOverride(?bool autoDeclineZipOverride): void |
| `message` | `?string` | Optional | A custom text message that displays after the payment is processed.<br><br>**Constraints**: *Maximum Length*: `120` | getMessage(): ?string | setMessage(?string message): void |

## Example (as JSON)

```json
{
  "action": "sale",
  "digitalWalletsOnly": false,
  "amount": 1099,
  "location_id": "11e95f8ec39de8fbdb0a4f1a",
  "contact_id": "11e95f8ec39de8fbdb0a4f1a",
  "ach_sec_code": "WEB",
  "methods": [
    {
      "type": "ach",
      "product_transaction_id": "product_transaction_id4"
    }
  ],
  "tax_amount": 50
}
```

