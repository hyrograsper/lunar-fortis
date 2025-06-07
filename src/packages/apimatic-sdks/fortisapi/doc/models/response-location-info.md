
# Response Location Info

## Structure

`ResponseLocationInfo`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type44Enum)`](../../doc/models/type-44-enum.md) | Optional | Resource Type<br><br>**Default**: `Type44Enum::LOCATIONINFO` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data11`](../../doc/models/data-11.md) | Optional | - | getData(): ?Data11 | setData(?Data11 data): void |

## Example (as JSON)

```json
{
  "type": "LocationInfo",
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

