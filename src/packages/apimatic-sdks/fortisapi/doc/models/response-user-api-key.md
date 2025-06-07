
# Response User Api Key

## Structure

`ResponseUserApiKey`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type123Enum)`](../../doc/models/type-123-enum.md) | Optional | Resource Type<br><br>**Default**: `Type123Enum::USERAPIKEY` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data32`](../../doc/models/data-32.md) | Optional | - | getData(): ?Data32 | setData(?Data32 data): void |

## Example (as JSON)

```json
{
  "type": "UserApiKey",
  "data": {
    "user_api_key": "user_api_key2"
  }
}
```

