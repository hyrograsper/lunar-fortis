
# Filter By

## Structure

`FilterBy`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `key` | `string` | Required | Resource key to filter by | getKey(): string | setKey(string key): void |
| `operator` | string([Operator1Enum](../../doc/models/operator-1-enum.md)) | Required | This is a container for one-of cases. | getOperator(): string | setOperator(string operator): void |
| `value` | float\|string\|bool | Required | This is a container for one-of cases. | getValue(): | setValue( value): void |

## Example (as JSON)

```json
{
  "key": "first_name",
  "operator": "<=",
  "value": "Fred"
}
```

