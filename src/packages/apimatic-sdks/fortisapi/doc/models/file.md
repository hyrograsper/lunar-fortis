
# File

## Structure

`File`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `file` | `?array` | Optional | File Object | getFile(): ?array | setFile(?array file): void |
| `resourceId` | `?string` | Optional | Resource Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getResourceId(): ?string | setResourceId(?string resourceId): void |
| `resource` | [`?string(Resource2Enum)`](../../doc/models/resource-2-enum.md) | Optional | Resource | getResource(): ?string | setResource(?string resource): void |
| `productFileId` | `?string` | Optional | Product File Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getProductFileId(): ?string | setProductFileId(?string productFileId): void |
| `fileCategoryId` | `?string` | Optional | File Category Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getFileCategoryId(): ?string | setFileCategoryId(?string fileCategoryId): void |
| `visibilityGroupId` | `?string` | Optional | Visibility Group Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getVisibilityGroupId(): ?string | setVisibilityGroupId(?string visibilityGroupId): void |
| `fileDescription` | `?string` | Optional | File Description<br><br>**Constraints**: *Maximum Length*: `128` | getFileDescription(): ?string | setFileDescription(?string fileDescription): void |
| `id` | `?string` | Optional | Resource<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `fileName` | `?string` | Optional | File Name | getFileName(): ?string | setFileName(?string fileName): void |
| `fileExtension` | `?string` | Optional | File Extension<br><br>**Constraints**: *Maximum Length*: `4` | getFileExtension(): ?string | setFileExtension(?string fileExtension): void |
| `fileSizeBytes` | `?int` | Optional | File Size Bytes | getFileSizeBytes(): ?int | setFileSizeBytes(?int fileSizeBytes): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `modifiedTs` | `?int` | Optional | Modified Time Stamp | getModifiedTs(): ?int | setModifiedTs(?int modifiedTs): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |

## Example (as JSON)

```json
{
  "resource_id": "11e95f8ec39de8fbdb0a4f1a",
  "resource": "Contact",
  "product_file_id": "11e95f8ec39de8fbdb0a4f1a",
  "file_category_id": "11e95f8ec39de8fbdb0a4f1a",
  "visibility_group_id": "11e95f8ec39de8fbdb0a4f1a",
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "modified_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "file": {
    "key1": "val1",
    "key2": "val2"
  }
}
```

