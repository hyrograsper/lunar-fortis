
# Response Fullboarding

## Structure

`ResponseFullboarding`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type30Enum)`](../../doc/models/type-30-enum.md) | Optional | Resource Type<br><br>**Default**: `Type30Enum::FULLBOARDING` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data9`](../../doc/models/data-9.md) | Optional | - | getData(): ?Data9 | setData(?Data9 data): void |

## Example (as JSON)

```json
{
  "type": "Fullboarding",
  "data": {
    "result": {
      "client_app_id": "client_app_id2",
      "dba_name": "dba_name4",
      "email": "email0"
    },
    "status": {
      "response_code": "response_code0"
    }
  }
}
```

