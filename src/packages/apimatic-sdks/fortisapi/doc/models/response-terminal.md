
# Response Terminal

## Structure

`ResponseTerminal`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type77Enum)`](../../doc/models/type-77-enum.md) | Optional | Resource Type<br><br>**Default**: `Type77Enum::TERMINAL` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data22`](../../doc/models/data-22.md) | Optional | - | getData(): ?Data22 | setData(?Data22 data): void |

## Example (as JSON)

```json
{
  "type": "Terminal",
  "data": {
    "location_id": "location_id4",
    "default_product_transaction_id": "default_product_transaction_id6",
    "terminal_application_id": "terminal_application_id0",
    "terminal_cvm_id": "terminal_cvm_id0",
    "terminal_manufacturer_code": "1"
  }
}
```

