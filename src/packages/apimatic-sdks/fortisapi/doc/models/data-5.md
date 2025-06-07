
# Data 5

## Structure

`Data5`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `id` | `?string` | Optional | Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `emailLogId` | `?string` | Optional | Email Log Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getEmailLogId(): ?string | setEmailLogId(?string emailLogId): void |
| `success` | `?bool` | Optional | Success | getSuccess(): ?bool | setSuccess(?bool success): void |
| `email` | `?string` | Optional | Email | getEmail(): ?string | setEmail(?string email): void |

## Example (as JSON)

```json
{
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "email_log_id": "11e95f8ec39de8fbdb0a4f1a",
  "email": "email@domain.com",
  "success": false
}
```

