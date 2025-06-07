
# Response Signature

## Structure

`ResponseSignature`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type67Enum)`](../../doc/models/type-67-enum.md) | Optional | Resource Type<br><br>**Default**: `Type67Enum::SIGNATURE` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data20`](../../doc/models/data-20.md) | Optional | - | getData(): ?Data20 | setData(?Data20 data): void |

## Example (as JSON)

```json
{
  "type": "Signature",
  "data": {
    "signature": "signature8",
    "resource": "AccountVault",
    "resource_id": "resource_id6",
    "id": "id0",
    "created_ts": 114
  }
}
```

