
# Joi 4

## Structure

`Joi4`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `conditions` | [Conditions4](../../doc/models/conditions-4.md)\|[Conditions4](../../doc/models/conditions-4.md)1\|[Conditions4](../../doc/models/conditions-4.md)2\|[Conditions4](../../doc/models/conditions-4.md)3\|null | Optional | This is a container for any-of cases. | getConditions(): | setConditions( conditions): void |

## Example (as JSON)

```json
{
  "conditions": {
    "method": "oxor",
    "values": "token_api_id"
  }
}
```

