
# Response Transaction Bin Info

## Structure

`ResponseTransactionBinInfo`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type108Enum)`](../../doc/models/type-108-enum.md) | Optional | Resource Type<br><br>**Default**: `Type108Enum::TRANSACTIONBININFO` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data27`](../../doc/models/data-27.md) | Optional | - | getData(): ?Data27 | setData(?Data27 data): void |

## Example (as JSON)

```json
{
  "type": "TransactionBinInfo",
  "data": {
    "issuer_bank_name": "issuer_bank_name6",
    "country_code": "country_code0",
    "detail_card_product": "detail_card_product2",
    "detail_card_indicator": "detail_card_indicator2",
    "fsa_indicator": "fsa_indicator8"
  }
}
```

