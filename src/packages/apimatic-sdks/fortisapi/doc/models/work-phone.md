
# Work Phone

The work phone provided by the Cardholder. Refer to ITU-E.164 for additional information on format and length.

This field is required if available, unless market or regional mandate restricts sending this information.

## Structure

`WorkPhone`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `cc` | `?string` | Optional | Country Code of the phone<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `3` | getCc(): ?string | setCc(?string cc): void |
| `subscriber` | `?string` | Optional | Subscriber section of the number<br><br>**Constraints**: *Maximum Length*: `15` | getSubscriber(): ?string | setSubscriber(?string subscriber): void |

## Example (as JSON)

```json
{
  "cc": "cc4",
  "subscriber": "subscriber6"
}
```

