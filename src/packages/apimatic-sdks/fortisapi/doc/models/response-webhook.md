
# Response Webhook

## Structure

`ResponseWebhook`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type133Enum)`](../../doc/models/type-133-enum.md) | Optional | Resource Type<br><br>**Default**: `Type133Enum::WEBHOOK` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data38`](../../doc/models/data-38.md) | Optional | - | getData(): ?Data38 | setData(?Data38 data): void |

## Example (as JSON)

```json
{
  "type": "Webhook",
  "data": {
    "attempt_interval": 300,
    "basic_auth_username": "basic_auth_username8",
    "basic_auth_password": "basic_auth_password0",
    "expands": "expands2",
    "format": "api-default"
  }
}
```

