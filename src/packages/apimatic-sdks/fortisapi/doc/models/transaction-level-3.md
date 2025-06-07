
# Transaction Level 3

Transaction Level3 Information on `expand`

## Structure

`TransactionLevel3`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `id` | `?string` | Optional | Level 3 ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `transactionId` | `?string` | Optional | Transaction ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getTransactionId(): ?string | setTransactionId(?string transactionId): void |
| `level3Data` | [`?Level3Data`](../../doc/models/level-3-data.md) | Optional | Level 3 data object | getLevel3Data(): ?Level3Data | setLevel3Data(?Level3Data level3Data): void |

## Example (as JSON)

```json
{
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "level3_data": {
    "destination_country_code": "destination_country_code4",
    "duty_amount": 182,
    "freight_amount": 60,
    "national_tax": 999999998900,
    "sales_tax": 999999998900
  }
}
```

