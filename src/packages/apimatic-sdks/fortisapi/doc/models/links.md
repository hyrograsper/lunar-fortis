
# Links

Pagination page links

## Structure

`Links`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type2Enum)`](../../doc/models/type-2-enum.md) | Optional | Object type | getType(): ?string | setType(?string type): void |
| `first` | `?string` | Optional | Link to the first page | getFirst(): ?string | setFirst(?string first): void |
| `previous` | `?string` | Optional | Link to the previous page | getPrevious(): ?string | setPrevious(?string previous): void |
| `next` | `?string` | Optional | Link to the next page | getNext(): ?string | setNext(?string next): void |
| `last` | `?string` | Optional | Link to the last page | getLast(): ?string | setLast(?string last): void |

## Example (as JSON)

```json
{
  "type": "Links",
  "first": "/v1/endpoint?page[size]=10&page[number]=1",
  "previous": "/v1/endpoint?page[size]=10&page[number]=5",
  "next": "/v1/endpoint?page[size]=10&page[number]=7",
  "last": "/v1/endpoint?page[size]=10&page[number]=42"
}
```

