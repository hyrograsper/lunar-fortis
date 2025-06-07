
# Response Quick Invoice Resend

## Structure

`ResponseQuickInvoiceResend`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type56Enum)`](../../doc/models/type-56-enum.md) | Optional | Resource Type<br><br>**Default**: `Type56Enum::QUICKINVOICERESEND` | getType(): ?string | setType(?string type): void |
| `data` | [`?Data18`](../../doc/models/data-18.md) | Optional | - | getData(): ?Data18 | setData(?Data18 data): void |

## Example (as JSON)

```json
{
  "type": "QuickInvoiceResend",
  "data": {
    "id": "id0",
    "email_log_id": "email_log_id2",
    "sms_log_id": "sms_log_id4",
    "success": false,
    "sms_success": false
  }
}
```

