
# Purchase

Contains purchase information

## Structure

`Purchase`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `purchaseInstallData` | `?int` | Optional | Indicates the maximum number of authorizations permitted for installment payments.<br><br>The fields is required if the Merchant and Cardholder have agreed to installment payments, i.e. if 3DS Requestor Authentication Indicator = 03. Omitted if not an installment payment authentication.<br><br>Starting from EMV 3DS 2.3.1:<br>Additionally this field is required for device_channel = 03 (3RI) if three_ri_ind = 02.<br><br>**Constraints**: `>= 2`, `<= 999` | getPurchaseInstallData(): ?int | setPurchaseInstallData(?int purchaseInstallData): void |
| `merchantRiskIndicator` | [`?MerchantRiskIndicator`](../../doc/models/merchant-risk-indicator.md) | Optional | Contains purchase information | getMerchantRiskIndicator(): ?MerchantRiskIndicator | setMerchantRiskIndicator(?MerchantRiskIndicator merchantRiskIndicator): void |
| `purchaseAmount` | `?int` | Optional | Purchase amount in minor units of currency with all punctuation removed. When used in conjunction with the Purchase Currency Exponent field, proper punctuation can be calculated. Example: If the purchase amount is USD 123.45, element will contain the value 12345. The field is limited to maximum 48 characters.<br><br>This field is required for 02-NPA message category if 3DS Requestor Authentication Indicator = 02 or 03.<br>This field is always required for message_category = 01 (PA).<br><br>Starting from EMV 3DS 2.3.1:<br>Additionally this field is required for message_category = 02-NPA if three_ri_ind = 01, 02, 06, 07, 08, 09, or 11. | getPurchaseAmount(): ?int | setPurchaseAmount(?int purchaseAmount): void |
| `purchaseCurrency` | `?string` | Optional | Currency in which purchase amount is expressed. The value is limited to 3 numeric characters and is represented by the ISO 4217 three-digit currency code, except 955-964 and 999.<br><br>This field is always required for message_category = 01-PA.  <br>It is required for message_category = 02-NPA if 3DS Requestor Authentication Indicator = 02 or 03.<br><br>Starting from EMV 3DS 2.3.1:<br>Additionally this field is required for message_category = 02-NPA if three_ri_ind = 01, 02, 06, 07, 08, 09, or 11.<br><br>**Constraints**: *Maximum Length*: `3` | getPurchaseCurrency(): ?string | setPurchaseCurrency(?string purchaseCurrency): void |
| `purchaseExponent` | `?int` | Optional | Minor units of currency as specified in the ISO 4217 currency exponent. The field is limited to 1 character.<br><br>This field is always required for message_category = 01-PA.  <br>It is required for message_category = 02-NPA if 3DS Requestor Authentication Indicator = 02 or 03.<br><br>Example: for currency USD the exponent should be 2, and for Yen the exponent should be 0.<br><br>Starting from EMV 3DS 2.3.1:<br>Additionally this field is required for message_category = 02-NPA if three_ri_ind = 01, 02, 06, 07, 08, 09, or 11.<br><br>**Constraints**: `>= 0`, `<= 9` | getPurchaseExponent(): ?int | setPurchaseExponent(?int purchaseExponent): void |
| `purchaseDate` | `?string` | Optional | Date and time of the purchase, converted into UTC. The field is limited to 14 characters, formatted as YYYYMMDDHHMMSS.<br><br>This field is always required for message_category = 01-PA.  <br>It is required for message_category = 02-NPA if 3DS Requestor Authentication Indicator = 02 or 03.<br><br>Starting from EMV 3DS 2.3.1:<br>Additionally this field is required for message_category = 02-NPA if three_ri_ind = 01, 02, 06, 07, 08, 09, or 11.<br><br>**Constraints**: *Maximum Length*: `14` | getPurchaseDate(): ?string | setPurchaseDate(?string purchaseDate): void |
| `recurringExpiry` | `?string` | Optional | Date after which no further authorizations shall be performed. This field is limited to 8 characters, and the accepted format is YYYYMMDD.<br><br>This field is required if 3DS Requestor Authentication Indicator = 02 or 03 and message_category = 01 or 02. Required if there is an end date.<br><br>**Constraints**: *Maximum Length*: `8` | getRecurringExpiry(): ?string | setRecurringExpiry(?string recurringExpiry): void |
| `recurringFrequency` | `?int` | Optional | Indicates the minimum number of days between authorizations.<br><br>This field is required if 3DS Requestor Authentication Indicator = 02 or 03 and frequency_ind = 01.<br><br>**Constraints**: `<= 9999` | getRecurringFrequency(): ?int | setRecurringFrequency(?int recurringFrequency): void |
| `transactionType` | [`?string(TransactionTypeEnum)`](../../doc/models/transaction-type-enum.md) | Optional | Identifies the type of transaction being authenticated. The values are derived from ISO 8583. This field is required in some markets. Otherwise, the field is optional.<br><br>> 01 - Goods / Service purchase<br>> <br>> 03 - Check Acceptance<br>> <br>> 10 - Account Funding<br>> <br>> 11 - Quasi-Cash Transaction<br>> <br>> 28 - Prepaid activation and Loan | getTransactionType(): ?string | setTransactionType(?string transactionType): void |
| `recurringAmount` | `?int` | Optional | Recurring amount after first/promotional payment in minor units of currency with all punctuation removed. Example: If the recurring amount is USD 123.45, element will contain the value 12345. The field is limited to maximum 48 characters.<br><br>The field is required if three_ds_requestor_authentication_ind = 02 or 03 AND three_ri_iInd = 01 or 02 AND amount_ind = 01.<br>Available for supporting EMV 3DS 2.3.1 and later versions. | getRecurringAmount(): ?int | setRecurringAmount(?int recurringAmount): void |
| `recurringCurrency` | `?string` | Optional | Currency in which recurring amount is expressed. The value is limited to 3 numeric characters and is represented by the ISO 4217 three-digit currency code, except 955-964 and 999.<br><br>This field is required if recurring_amount is present.<br>Available for supporting EMV 3DS 2.3.1 and later versions.<br><br>**Constraints**: *Maximum Length*: `3` | getRecurringCurrency(): ?string | setRecurringCurrency(?string recurringCurrency): void |
| `recurringExponent` | `?int` | Optional | Minor units of currency as specified in the ISO 4217 currency exponent. Example: USD = 2, Yen = 0. The value is limited to 1 numeric character.<br><br>This field is required if recurring_amount is present.<br>Available for supporting EMV 3DS 2.3.1 and later versions.<br><br>**Constraints**: `>= 0`, `<= 9` | getRecurringExponent(): ?int | setRecurringExponent(?int recurringExponent): void |
| `recurringDate` | `?string` | Optional | Effective date of new authorized amount following first/promotional payment in recurring transaction. The value is limited to 8 characters. Accepted format: YYYYMMDD.<br><br>This field is required if frequency_ind = 01.<br>Available for supporting EMV 3DS 2.3.1 and later versions.<br><br>**Constraints**: *Maximum Length*: `8` | getRecurringDate(): ?string | setRecurringDate(?string recurringDate): void |
| `amountInd` | [`?string(AmountIndEnum)`](../../doc/models/amount-ind-enum.md) | Optional | Part of the indication whether the recurring or installment payment has a fixed or variable amount.<br><br>Starting from EMV 3DS 2.3.1:<br>This field is required if three_ds_requestor.three_ds_requestor_authentication_ind = 02 or 03.<br>This field is required if three_ri_ind= 01 or 02.<br><br>> 01 - Fixed Purchase Amount<br>> <br>> 02 - Variable Purchase Amount<br>> <br>> 03 through 79 - Reserved for EMVCo future use (values invalid until defined by EMVCo)<br>> <br>> 80 through 99 - PS-specific value (dependent on the payment scheme type) | getAmountInd(): ?string | setAmountInd(?string amountInd): void |
| `frequencyInd` | `?string` | Optional | Part of the indication whether the recurring or instalment payment has a fixed or variable frequency.<br><br>Starting from EMV 3DS 2.3.1:<br>This field is required if three_ds_requestor.three_ds_requestor_authentication_ind = 02 or 03.<br>This field is required if three_ri_ind= 01 or 02. | getFrequencyInd(): ?string | setFrequencyInd(?string frequencyInd): void |

## Example (as JSON)

```json
{
  "purchase_amount": 12345,
  "purchase_currency": "840",
  "purchase_exponent": 2,
  "purchase_date": "20240329155618",
  "transaction_type": "01",
  "purchase_install_data": 236,
  "merchant_risk_indicator": {
    "ship_indicator": "05",
    "delivery_timeframe": "03",
    "delivery_email_address": "delivery_email_address6",
    "reorder_items_ind": "01",
    "pre_order_purchase_ind": "01"
  }
}
```

