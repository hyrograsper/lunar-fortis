
# Terminal Cvm

Terminal Cvm Information on `expand`

## Structure

`TerminalCvm`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `terminalManufacturerCode` | `?float` | Optional | Terminal Manufacturer Code | getTerminalManufacturerCode(): ?float | setTerminalManufacturerCode(?float terminalManufacturerCode): void |
| `title` | `?string` | Optional | Title<br><br>**Constraints**: *Maximum Length*: `32` | getTitle(): ?string | setTitle(?string title): void |
| `contactData` | `?string` | Optional | Contact Data<br><br>**Constraints**: *Maximum Length*: `100000` | getContactData(): ?string | setContactData(?string contactData): void |
| `contactlessData` | `?string` | Optional | Contactless Data<br><br>**Constraints**: *Maximum Length*: `100000` | getContactlessData(): ?string | setContactlessData(?string contactlessData): void |
| `id` | `?string` | Optional | Terminal Manufacturer Cvms Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `modifiedTs` | `?int` | Optional | Modified Time Stamp | getModifiedTs(): ?int | setModifiedTs(?int modifiedTs): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |
| `modifiedUserId` | `?string` | Optional | Last User ID that updated the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getModifiedUserId(): ?string | setModifiedUserId(?string modifiedUserId): void |

## Example (as JSON)

```json
{
  "terminal_manufacturer_code": 4.0,
  "title": "CVM One",
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "modified_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "contact_data": "contact_data8",
  "contactless_data": "contactless_data0"
}
```

