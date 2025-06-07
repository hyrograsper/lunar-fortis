
# Product Recurring

Product recurring array

## Structure

`ProductRecurring`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `title` | `?string` | Optional | Title<br><br>**Constraints**: *Maximum Length*: `64` | getTitle(): ?string | setTitle(?string title): void |
| `locationId` | `?string` | Optional | Location ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationId(): ?string | setLocationId(?string locationId): void |
| `locationApiId` | `?string` | Optional | Location Api ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationApiId(): ?string | setLocationApiId(?string locationApiId): void |
| `sendDeclinedNotifications` | `?bool` | Optional | Send Declined Notifications | getSendDeclinedNotifications(): ?bool | setSendDeclinedNotifications(?bool sendDeclinedNotifications): void |
| `requireFullPayment` | `?bool` | Optional | Require Full Payment | getRequireFullPayment(): ?bool | setRequireFullPayment(?bool requireFullPayment): void |
| `expireNotificationEmailEnable` | `?bool` | Optional | Expire Notification Email Enable | getExpireNotificationEmailEnable(): ?bool | setExpireNotificationEmailEnable(?bool expireNotificationEmailEnable): void |
| `expireNotificationSmsEnable` | `?bool` | Optional | Expire Notification SMS Enable | getExpireNotificationSmsEnable(): ?bool | setExpireNotificationSmsEnable(?bool expireNotificationSmsEnable): void |
| `notificationDaysDefault` | `?int` | Optional | Notification Days Default<br><br>**Constraints**: `>= 0`, `<= 365` | getNotificationDaysDefault(): ?int | setNotificationDaysDefault(?int notificationDaysDefault): void |
| `id` | `?string` | Optional | Product Recurring Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `modifiedTs` | `?int` | Optional | Modified Time Stamp | getModifiedTs(): ?int | setModifiedTs(?int modifiedTs): void |
| `createdUserId` | `?string` | Optional | Created User Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |
| `modifiedUserId` | `?string` | Optional | Modified User Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getModifiedUserId(): ?string | setModifiedUserId(?string modifiedUserId): void |

## Example (as JSON)

```json
{
  "title": "Fortispay RbYN6y",
  "location_id": "11e95f8ec39de8fbdb0a4f1a",
  "location_api_id": "11e95f8ec39de8fbdb0a4f1a",
  "send_declined_notifications": true,
  "require_full_payment": true,
  "expire_notification_email_enable": true,
  "expire_notification_sms_enable": true,
  "notification_days_default": 1,
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "modified_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
}
```

