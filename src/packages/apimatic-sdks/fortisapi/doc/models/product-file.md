
# Product File

Product file array

## Structure

`ProductFile`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `title` | `?string` | Optional | Title<br><br>**Constraints**: *Maximum Length*: `64` | getTitle(): ?string | setTitle(?string title): void |
| `productFileCredentialId` | `?string` | Optional | Product File Credential Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getProductFileCredentialId(): ?string | setProductFileCredentialId(?string productFileCredentialId): void |
| `freeBytes` | `?float` | Optional | Free Bytes | getFreeBytes(): ?float | setFreeBytes(?float freeBytes): void |
| `byteIncrement` | `?float` | Optional | Byte Increment | getByteIncrement(): ?float | setByteIncrement(?float byteIncrement): void |
| `maxFileSizeBytes` | `?float` | Optional | Max File Size Bytes | getMaxFileSizeBytes(): ?float | setMaxFileSizeBytes(?float maxFileSizeBytes): void |
| `incrementCost` | `?float` | Optional | Increment Cost | getIncrementCost(): ?float | setIncrementCost(?float incrementCost): void |
| `monthlyFee` | `?int` | Optional | Monthly Fee | getMonthlyFee(): ?int | setMonthlyFee(?int monthlyFee): void |
| `fileExtAllowed` | `?string` | Optional | File Ext Allowed<br><br>**Constraints**: *Maximum Length*: `64` | getFileExtAllowed(): ?string | setFileExtAllowed(?string fileExtAllowed): void |
| `container` | `?string` | Optional | Container<br><br>**Constraints**: *Maximum Length*: `128` | getContainer(): ?string | setContainer(?string container): void |
| `id` | `?string` | Optional | Product File Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `modifiedTs` | `?int` | Optional | Modified Time Stamp | getModifiedTs(): ?int | setModifiedTs(?int modifiedTs): void |
| `active` | `?bool` | Optional | Active | getActive(): ?bool | setActive(?bool active): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |

## Example (as JSON)

```json
{
  "title": "My terminal",
  "product_file_credential_id": "11e95f8ec39de8fbdb0a4f1a",
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "modified_ts": 1422040992,
  "active": true,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "free_bytes": 216.62,
  "byte_increment": 219.94,
  "max_file_size_bytes": 237.34
}
```

