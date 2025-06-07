
# Response Remove Verification

## Structure

`ResponseRemoveVerification`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type129Enum)`](../../doc/models/type-129-enum.md) | Optional | Resource Type<br><br>**Default**: `Type129Enum::REMOVEVERIFICATION` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data31`](../../doc/models/data-31.md) | Optional | - | getData(): ?Data31 | setData(?Data31 data): void |

## Example (as JSON)

```json
{
  "type": "RemoveVerification",
  "data": {
    "id": "id0",
    "user_id": "user_id8",
    "hash": "hash6",
    "created_ts": 114
  }
}
```

