
# Response Error Exception

## Structure

`ResponseErrorException`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | `?string` | Optional | Message type | getType(): ?string | setType(?string type): void |
| `id` | `?string` | Optional | Unique identifier for this API call | getId(): ?string | setId(?string id): void |
| `statusCode` | `?int` | Optional | HTTP status code | getStatusCode(): ?int | setStatusCode(?int statusCode): void |
| `title` | `?string` | Optional | Error title | getTitle(): ?string | setTitle(?string title): void |
| `detail` | [`?Detail1`](../../doc/models/detail-1.md) | Optional | Error details | getDetail(): ?Detail1 | setDetail(?Detail1 detail): void |
| `meta` | `?array` | Optional | Object with additional error details | getMeta(): ?array | setMeta(?array meta): void |

## Example (as JSON)

```json
{
  "type": "Error",
  "id": "c7f03d44-c966-4578-93ff-295f3ef6a467",
  "statusCode": 400,
  "title": "Bad Request",
  "detail": {
    "three_ds_server_trans_id": "three_ds_server_trans_id6",
    "acs_trans_id": "acs_trans_id8",
    "ds_trans_id": "ds_trans_id4",
    "error_code": "error_code4",
    "error_component": "error_component2"
  }
}
```

