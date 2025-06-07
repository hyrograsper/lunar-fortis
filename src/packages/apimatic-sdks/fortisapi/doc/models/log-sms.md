
# Log Sms

Log Sms Information on `expand`

## Structure

`LogSms`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `id` | `?string` | Optional | Log Sms Id<br><br>**Constraints**: *Maximum Length*: `24`, *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `body` | `?string` | Optional | Body | getBody(): ?string | setBody(?string body): void |
| `reasonModel` | `?string` | Optional | Reason Model<br><br>**Constraints**: *Maximum Length*: `24` | getReasonModel(): ?string | setReasonModel(?string reasonModel): void |
| `reasonModelId` | `?string` | Optional | Reason Model ID<br><br>**Constraints**: *Maximum Length*: `36` | getReasonModelId(): ?string | setReasonModelId(?string reasonModelId): void |
| `providerId` | `?string` | Optional | Provider ID<br><br>**Constraints**: *Maximum Length*: `60` | getProviderId(): ?string | setProviderId(?string providerId): void |
| `status` | `?string` | Optional | Status<br><br>**Constraints**: *Maximum Length*: `10` | getStatus(): ?string | setStatus(?string status): void |
| `sender` | `?string` | Optional | Sender<br><br>**Constraints**: *Maximum Length*: `10` | getSender(): ?string | setSender(?string sender): void |
| `recipient` | `?string` | Optional | Recipient<br><br>**Constraints**: *Maximum Length*: `10` | getRecipient(): ?string | setRecipient(?string recipient): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Maximum Length*: `36`, *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |

## Example (as JSON)

```json
{
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "body": "body2",
  "reason_model": "reason_model2",
  "reason_model_id": "reason_model_id8",
  "provider_id": "provider_id8"
}
```

