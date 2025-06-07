
# Multi Transaction

Additional transaction information in case of multiple transactions or merchants.

Available for supporting EMV 3DS 2.3.1 and later versions.

## Structure

`MultiTransaction`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `merchantList` | [`MerchantList[]`](../../doc/models/merchant-list.md) | Required | Contains the details of each merchant involved in the transaction<br><br>**Constraints**: *Minimum Items*: `1`, *Maximum Items*: `50` | getMerchantList(): array | setMerchantList(array merchantList): void |
| `avValidityTime` | `?int` | Optional | Number of days that the AV (Authentication Value) is valid.<br><br>**Constraints**: `>= 0`, `<= 999` | getAvValidityTime(): ?int | setAvValidityTime(?int avValidityTime): void |
| `avNumberUse` | `?int` | Optional | Number of times that the AV (Authentication Value) is valid.<br><br>**Constraints**: `>= 0`, `<= 99` | getAvNumberUse(): ?int | setAvNumberUse(?int avNumberUse): void |

## Example (as JSON)

```json
{
  "merchant_list": [
    {
      "merchant_name_listed": "merchant_name_listed0",
      "acquirer_merchant_id_listed": "acquirer_merchant_id_listed0",
      "merchant_amount": "merchant_amount8",
      "merchant_currency": "merchant_currency6",
      "merchant_exponent": "merchant_exponent0"
    }
  ],
  "av_validity_time": 168,
  "av_number_use": 8
}
```

