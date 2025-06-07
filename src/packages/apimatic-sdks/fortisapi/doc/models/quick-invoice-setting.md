
# Quick Invoice Setting

Quick Invoice Setting Information on `expand`

## Structure

`QuickInvoiceSetting`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `locationApiId` | `?string` | Optional | Location API ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationApiId(): ?string | setLocationApiId(?string locationApiId): void |
| `locationId` | `?string` | Optional | Location ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationId(): ?string | setLocationId(?string locationId): void |
| `quickInvoiceTemplate` | `?string` | Optional | Quick Invoice Template<br><br>**Constraints**: *Maximum Length*: `5000` | getQuickInvoiceTemplate(): ?string | setQuickInvoiceTemplate(?string quickInvoiceTemplate): void |
| `defaultAllowPartialPay` | `?bool` | Optional | Default Quick Invoice Allow Partial Pay | getDefaultAllowPartialPay(): ?bool | setDefaultAllowPartialPay(?bool defaultAllowPartialPay): void |
| `defaultNotificationOnDueDate` | `?bool` | Optional | Default Quick Invoice Notification On Due Date | getDefaultNotificationOnDueDate(): ?bool | setDefaultNotificationOnDueDate(?bool defaultNotificationOnDueDate): void |
| `defaultNotificationDaysAfterDueDate` | `?float` | Optional | Default Quick Invoice Notification Days After Due Date<br><br>**Constraints**: `>= 0`, `<= 60` | getDefaultNotificationDaysAfterDueDate(): ?float | setDefaultNotificationDaysAfterDueDate(?float defaultNotificationDaysAfterDueDate): void |
| `defaultNotificationDaysBeforeDueDate` | `?float` | Optional | Default Quick Invoice Notification Days Before Due Date<br><br>**Constraints**: `>= 0`, `<= 60` | getDefaultNotificationDaysBeforeDueDate(): ?float | setDefaultNotificationDaysBeforeDueDate(?float defaultNotificationDaysBeforeDueDate): void |
| `showCustomFields` | `?bool` | Optional | Show Custom Fields | getShowCustomFields(): ?bool | setShowCustomFields(?bool showCustomFields): void |
| `id` | `?string` | Optional | Quick Invoice Settings ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |

## Example (as JSON)

```json
{
  "location_api_id": "11e95f8ec39de8fbdb0a4f1a",
  "location_id": "11e95f8ec39de8fbdb0a4f1a",
  "quick_invoice_template": "<html>Template<html>",
  "default_allow_partial_pay": true,
  "default_notification_on_due_date": true,
  "default_notification_days_after_due_date": 7,
  "default_notification_days_before_due_date": 3,
  "show_custom_fields": false,
  "id": "11e95f8ec39de8fbdb0a4f1a"
}
```

