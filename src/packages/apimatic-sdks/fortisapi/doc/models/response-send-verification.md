
# Response Send Verification

## Structure

`ResponseSendVerification`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type130Enum)`](../../doc/models/type-130-enum.md) | Optional | Resource Type<br><br>**Default**: `Type130Enum::SENDVERIFICATION` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data31`](../../doc/models/data-31.md) | Optional | - | getData(): ?Data31 | setData(?Data31 data): void |

## Example (as JSON)

```json
{
  "type": "SendVerification",
  "data": {
    "id": "id0",
    "user_id": "user_id8",
    "hash": "hash6",
    "created_ts": 114
  }
}
```

