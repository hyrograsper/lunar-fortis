
# Response 412 Exception

## Structure

`Response412Exception`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | `?string` | Optional | - | getType(): ?string | setType(?string type): void |
| `id` | `?string` | Optional | - | getId(): ?string | setId(?string id): void |
| `statusCode` | `?int` | Optional | Response code | getStatusCode(): ?int | setStatusCode(?int statusCode): void |
| `title` | `?string` | Optional | Error description | getTitle(): ?string | setTitle(?string title): void |
| `detail` | `?string` | Optional | Error details | getDetail(): ?string | setDetail(?string detail): void |
| `meta` | [`?Meta`](../../doc/models/meta.md) | Optional | - | getMeta(): ?Meta | setMeta(?Meta meta): void |

## Example (as JSON)

```json
{
  "type": "clj4ge1234004t9ptdoz34567",
  "id": "clj4ge1234004t9ptdoz34567",
  "statusCode": 412,
  "title": "Precondition Failed",
  "detail": "\"fieldName\" is required"
}
```

