
# Response User Verification

## Structure

`ResponseUserVerification`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type118Enum)`](../../doc/models/type-118-enum.md) | Optional | Resource Type<br><br>**Default**: `Type118Enum::USERVERIFICATION` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data31`](../../doc/models/data-31.md) | Optional | - | getData(): ?Data31 | setData(?Data31 data): void |

## Example (as JSON)

```json
{
  "type": "UserVerification",
  "data": {
    "id": "id0",
    "user_id": "user_id8",
    "hash": "hash6",
    "created_ts": 114
  }
}
```

