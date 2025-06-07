
# Data

## Structure

`Data`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `code` | `?string` | Optional | A [UUID v4](https://datatracker.ietf.org/doc/html/rfc4122) that's unique for the Async Request | getCode(): ?string | setCode(?string code): void |
| `type` | `?string` | Optional | The @type from the original request. | getType(): ?string | setType(?string type): void |
| `id` | `?string` | Optional | After a sucessfully processing, the system will fill with the final ID for the document | getId(): ?string | setId(?string id): void |
| `progress` | `?int` | Optional | The current percentage progress<br><br>**Constraints**: `>= 0`, `<= 100` | getProgress(): ?int | setProgress(?int progress): void |
| `error` | `?string` | Optional | In case of error processing, it will contain the error details | getError(): ?string | setError(?string error): void |
| `ttl` | `?int` | Optional | The date (in [Epoch Time](https://en.wikipedia.org/wiki/Unix_time)) this status register is set to expire. Usually 30 days after the request. | getTtl(): ?int | setTtl(?int ttl): void |

## Example (as JSON)

```json
{
  "code": "406c66c3-21cb-47fb-80fc-843bc42507fb",
  "type": "Transaction",
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "progress": 100,
  "ttl": 7956886942,
  "error": "error8"
}
```

