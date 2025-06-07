
# Response Quick Invoice

## Structure

`ResponseQuickInvoice`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type54Enum)`](../../doc/models/type-54-enum.md) | Optional | Resource Type<br><br>**Default**: `Type54Enum::QUICKINVOICE` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data17`](../../doc/models/data-17.md) | Optional | - | getData(): ?Data17 | setData(?Data17 data): void |

## Example (as JSON)

```json
{
  "type": "QuickInvoice",
  "data": {
    "location_id": "location_id4",
    "title": "title6",
    "cc_product_transaction_id": "cc_product_transaction_id2",
    "ach_product_transaction_id": "ach_product_transaction_id2",
    "due_date": "due_date8"
  }
}
```

