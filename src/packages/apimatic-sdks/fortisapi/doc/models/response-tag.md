
# Response Tag

## Structure

`ResponseTag`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type72Enum)`](../../doc/models/type-72-enum.md) | Optional | Resource Type<br><br>**Default**: `Type72Enum::TAG` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data21`](../../doc/models/data-21.md) | Optional | - | getData(): ?Data21 | setData(?Data21 data): void |

## Example (as JSON)

```json
{
  "type": "Tag",
  "data": {
    "location_id": "location_id4",
    "title": "title6",
    "id": "id0",
    "created_ts": 114,
    "modified_ts": 190
  }
}
```

