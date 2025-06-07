
# Item List 4

## Structure

`ItemList4`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `name` | `string` | Required | Item's Name, must be unique on the list<br><br>**Constraints**: *Maximum Length*: `100` | getName(): string | setName(string name): void |
| `amount` | `int` | Required | Item's Amount<br><br>**Constraints**: `>= -999999999`, `<= 999999999` | getAmount(): int | setAmount(int amount): void |

## Example (as JSON)

```json
{
  "name": "Bread",
  "amount": 2015
}
```

