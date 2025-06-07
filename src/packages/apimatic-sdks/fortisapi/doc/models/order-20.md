
# Order 20

## Structure

`Order20`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `key` | `string` | Required | Resource key to order by. | getKey(): string | setKey(string key): void |
| `operator` | [`string(OperatorEnum)`](../../doc/models/operator-enum.md) | Required | The order. Ascending or descending. | getOperator(): string | setOperator(string operator): void |

## Example (as JSON)

```json
{
  "key": "first_name",
  "operator": "asc"
}
```

