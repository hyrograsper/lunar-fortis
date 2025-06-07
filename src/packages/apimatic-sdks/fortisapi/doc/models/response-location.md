
# Response Location

## Structure

`ResponseLocation`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type43Enum)`](../../doc/models/type-43-enum.md) | Optional | Resource Type<br><br>**Default**: `Type43Enum::LOCATION` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data10`](../../doc/models/data-10.md) | Optional | - | getData(): ?Data10 | setData(?Data10 data): void |

## Example (as JSON)

```json
{
  "type": "Location",
  "data": {
    "id": "id0",
    "created_ts": 114,
    "modified_ts": 190,
    "account_number": "account_number0",
    "address": {
      "city": "city6",
      "state": "state2",
      "postal_code": "postal_code8",
      "country": "US",
      "street": "street6"
    }
  }
}
```

