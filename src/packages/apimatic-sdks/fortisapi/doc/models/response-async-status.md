
# Response Async Status

## Structure

`ResponseAsyncStatus`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(TypeEnum)`](../../doc/models/type-enum.md) | Optional | Resource Type<br><br>**Default**: `TypeEnum::ASYNCSTATUS` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data`](../../doc/models/data.md) | Optional | - | getData(): ?Data | setData(?Data data): void |

## Example (as JSON)

```json
{
  "type": "AsyncStatus",
  "data": {
    "code": "00001e1c-0000-0000-0000-000000000000",
    "type": "type0",
    "id": "id0",
    "progress": 100,
    "error": "error4"
  }
}
```

