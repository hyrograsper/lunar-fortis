
# Document

Array of document objects.

## Structure

`Document`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `documentName` | `string` | Required | Array of bank account objects.<br><br>**Constraints**: *Maximum Length*: `32` | getDocumentName(): string | setDocumentName(string documentName): void |
| `documentData` | `string` | Required | Base64 encoded document content.<br><br>> Base64 encoded document content. | getDocumentData(): string | setDocumentData(string documentData): void |
| `mimeType` | `string` | Required | Mime type of document.<br><br>**Constraints**: *Maximum Length*: `20` | getMimeType(): string | setMimeType(string mimeType): void |

## Example (as JSON)

```json
{
  "document_name": "ImportantDoc.txt",
  "document_data": "alskj;asijia;eiom;weirj;iomj",
  "mime_type": "application/json"
}
```

