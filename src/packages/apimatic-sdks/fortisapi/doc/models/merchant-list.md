
# Merchant List

## Structure

`MerchantList`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `merchantNameListed` | `string` | Required | Name of the listed merchant as used in the authorization message as defined in ISO 8583.<br><br>**Constraints**: *Maximum Length*: `40` | getMerchantNameListed(): string | setMerchantNameListed(string merchantNameListed): void |
| `acquirerMerchantIdListed` | `?string` | Optional | Acquirer-assigned Merchant Listed Identifier.<br>This may be the same value that is used in authorisation requests sent on behalf of the 3DS Requestor and is represented in ISO 8583 formatting requirements.<br><br>**Constraints**: *Maximum Length*: `15` | getAcquirerMerchantIdListed(): ?string | setAcquirerMerchantIdListed(?string acquirerMerchantIdListed): void |
| `merchantAmount` | `?string` | Optional | Purchase amount for the merchant in minor units of currency with all punctuation removed.<br>When used in conjunction with the Purchase Currency Exponent field, proper punctuation can be calculated.<br><br>**Constraints**: *Maximum Length*: `48` | getMerchantAmount(): ?string | setMerchantAmount(?string merchantAmount): void |
| `merchantCurrency` | `?string` | Optional | Currency Code in which purchase Merchant Amount is expressed. Must be provided as ISO 4217 three-digit currency code except values 955-964 and 999.<br><br>**Constraints**: *Minimum Length*: `3`, *Maximum Length*: `3` | getMerchantCurrency(): ?string | setMerchantCurrency(?string merchantCurrency): void |
| `merchantExponent` | `?string` | Optional | Minor units of Merchant Currency as specified in the ISO 4217 currency exponent.<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `1` | getMerchantExponent(): ?string | setMerchantExponent(?string merchantExponent): void |

## Example (as JSON)

```json
{
  "merchant_name_listed": "merchant_name_listed4",
  "acquirer_merchant_id_listed": "acquirer_merchant_id_listed4",
  "merchant_amount": "merchant_amount2",
  "merchant_currency": "merchant_currency0",
  "merchant_exponent": "merchant_exponent4"
}
```

