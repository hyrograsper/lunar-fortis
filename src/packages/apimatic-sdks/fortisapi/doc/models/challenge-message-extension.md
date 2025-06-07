
# Challenge Message Extension

## Structure

`ChallengeMessageExtension`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `id` | `?string` | Optional | A unique identifier for the extension. Payment System Registered Application Provider Identifier (RID) is required as prefix of the ID. The maximum length is 64 characters.<br><br>**Constraints**: *Maximum Length*: `64` | getId(): ?string | setId(?string id): void |
| `name` | `?string` | Optional | The name of the extension data set as defined by the extension owner. Maximum length is 64 characters.<br><br>**Constraints**: *Maximum Length*: `64` | getName(): ?string | setName(?string name): void |
| `criticalityIndicator` | `?bool` | Optional | A boolean value indicating whether the recipient must understand the contents of the extension to interpret the entire message. | getCriticalityIndicator(): ?bool | setCriticalityIndicator(?bool criticalityIndicator): void |
| `data` | `?array` | Optional | The data carried in the extension as. The maximum length is 8059 characters. | getData(): ?array | setData(?array data): void |

## Example (as JSON)

```json
{
  "id": "id8",
  "name": "name8",
  "criticality_indicator": false,
  "data": {
    "key1": "val1",
    "key2": "val2"
  }
}
```

