
# Response Ticket

## Structure

`ResponseTicket`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type82Enum)`](../../doc/models/type-82-enum.md) | Optional | Resource Type<br><br>**Default**: `Type82Enum::TICKET` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data23`](../../doc/models/data-23.md) | Optional | - | getData(): ?Data23 | setData(?Data23 data): void |

## Example (as JSON)

```json
{
  "type": "Ticket",
  "data": {
    "account_holder_name": "account_holder_name0",
    "exp_date": "exp_date8",
    "cvv": "cvv8",
    "account_number": "account_number0",
    "billing_address": {
      "postal_code": "postal_code0",
      "street": "street8"
    }
  }
}
```

