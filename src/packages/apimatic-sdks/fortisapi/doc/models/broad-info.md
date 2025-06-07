
# Broad Info

Until EMV 3DS 2.2.0:

Unstructured information sent between the 3DS Server, the DS and the ACS.

This field is not required to be filled by the Requestor and the requirements for the presence of this field are DS specific.

Starting from EMV 3DS 2.3.1:

Structured information sent between the 3DS Server, the DS and the ACS. 2.3.1 structure is defined below. Accepted value length is maximum 4096 characters. This field is optional.

## Structure

`BroadInfo`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `category` | [`?string(CategoryEnum)`](../../doc/models/category-enum.md) | Optional | Indicates the category/type of information. This field is required.<br><br>> 01 - General<br>> <br>> 02 - Certificate expiry<br>> <br>> 03 - Fraud alert<br>> <br>> 04 - Operational alert<br>> <br>> 05 - Transactional data<br>> <br>> 06 - Other<br>> <br>> 80 through 99 - PS-specific value (dependent on the payment scheme type) | getCategory(): ?string | setCategory(?string category): void |
| `description` | `?string` | Optional | Information to be broadcasted to the recipients. Accepted value length is maximum 4000 characters. This field is optional.<br><br>**Constraints**: *Maximum Length*: `4000` | getDescription(): ?string | setDescription(?string description): void |
| `expireDate` | `?string` | Optional | The date after which the relevance of the broadcasted information (e.g., ceritifacte expiration dates) expires. The accepted value length is 8 characters. The accepted format is YYYYMMDD.<br><br>**Constraints**: *Maximum Length*: `8` | getExpireDate(): ?string | setExpireDate(?string expireDate): void |
| `severity` | [`?string(SeverityEnum)`](../../doc/models/severity-enum.md) | Optional | Indicates the importance/severity level of the broadcasted information. This field is required.<br><br>> 01 - Critical. Immediate action to be taken by recipient<br>> <br>> 02 - Major. Major impact; Upcoming action to be taken by recipient<br>> <br>> 03 - Minor. Minor impact; Upcoming action to be taken by recipient<br>> <br>> 04 - Informational. Informational only with no immediate action by recipient | getSeverity(): ?string | setSeverity(?string severity): void |
| `recipients` | [`?string(RecipientsEnum)`](../../doc/models/recipients-enum.md) | Optional | Indicates the intended recipient(s) of the broadcasted information. This field is required.<br><br>> 01 - 3DS SDK<br>> <br>> 02 - 3DS Server<br>> <br>> 03 - DS<br>> <br>> 04 - ACS | getRecipients(): ?string | setRecipients(?string recipients): void |
| `source` | [`?string(SourceEnum)`](../../doc/models/source-enum.md) | Optional | Indicates the source of the broadcasted information. This field is required.<br><br>> 01 - 3DS Server<br>> <br>> 02 - DS<br>> <br>> 03 - ACS | getSource(): ?string | setSource(?string source): void |

## Example (as JSON)

```json
{
  "category": "98",
  "description": "description2",
  "expire_date": "expire_date0",
  "severity": "03",
  "recipients": "01"
}
```

