
# Sort

Sort information used on the results

## Structure

`Sort`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type4Enum)`](../../doc/models/type-4-enum.md) | Optional | Object type | getType(): ?string | setType(?string type): void |
| `fields` | [`?(Field[])`](../../doc/models/field.md) | Optional | [object Object] | getFields(): ?array | setFields(?array fields): void |

## Example (as JSON)

```json
{
  "type": "Sorting",
  "fields": [
    {
      "field": "field2",
      "order": "asc"
    },
    {
      "field": "field2",
      "order": "asc"
    }
  ]
}
```

