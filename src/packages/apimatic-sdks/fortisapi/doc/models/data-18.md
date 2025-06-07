
# Data 18

## Structure

`Data18`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `id` | `?string` | Optional | Quick Invoice ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `emailLogId` | `?string` | Optional | Email Log Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getEmailLogId(): ?string | setEmailLogId(?string emailLogId): void |
| `smsLogId` | `?string` | Optional | SMS Log Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getSmsLogId(): ?string | setSmsLogId(?string smsLogId): void |
| `success` | `?bool` | Optional | Success | getSuccess(): ?bool | setSuccess(?bool success): void |
| `smsSuccess` | `?bool` | Optional | SMS Success | getSmsSuccess(): ?bool | setSmsSuccess(?bool smsSuccess): void |
| `email` | `?string` | Optional | Email<br><br>**Constraints**: *Maximum Length*: `64` | getEmail(): ?string | setEmail(?string email): void |

## Example (as JSON)

```json
{
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "email_log_id": "11e95f8ec39de8fbdb0a4f1a",
  "sms_log_id": "11e95f8ec39de8fbdb0a4f1a",
  "success": true,
  "sms_success": true,
  "email": "email@domain.com"
}
```

