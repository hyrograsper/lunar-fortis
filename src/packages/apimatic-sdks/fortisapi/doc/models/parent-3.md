
# Parent 3

Parent Information on `expand`

## Structure

`Parent3`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `id` | `?string` | Optional | Location ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `modifiedTs` | `?int` | Optional | Modified Time Stamp | getModifiedTs(): ?int | setModifiedTs(?int modifiedTs): void |
| `accountNumber` | `?string` | Optional | Account number<br><br>**Constraints**: *Maximum Length*: `32`, *Pattern*: `^[a-zA-Z0-9\-_]+$` | getAccountNumber(): ?string | setAccountNumber(?string accountNumber): void |
| `address` | [`?Address1`](../../doc/models/address-1.md) | Optional | Address | getAddress(): ?Address1 | setAddress(?Address1 address): void |
| `brandingDomainId` | `?string` | Optional | GUID for Branding Domain<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getBrandingDomainId(): ?string | setBrandingDomainId(?string brandingDomainId): void |
| `contactEmailTrxReceiptDefault` | `?bool` | Optional | If true, will email contact receipt for any transaction | getContactEmailTrxReceiptDefault(): ?bool | setContactEmailTrxReceiptDefault(?bool contactEmailTrxReceiptDefault): void |
| `defaultAch` | `?string` | Optional | GUID for Location's default ACH Product Transaction<br><br>**Constraints**: *Minimum Length*: `24`, *Maximum Length*: `36` | getDefaultAch(): ?string | setDefaultAch(?string defaultAch): void |
| `defaultCc` | `?string` | Optional | GUID for Location's default CC Product Transaction<br><br>**Constraints**: *Minimum Length*: `24`, *Maximum Length*: `36` | getDefaultCc(): ?string | setDefaultCc(?string defaultCc): void |
| `emailReplyTo` | `?string` | Optional | Used as from email address when sending various notifications<br><br>**Constraints**: *Maximum Length*: `64` | getEmailReplyTo(): ?string | setEmailReplyTo(?string emailReplyTo): void |
| `fax` | `?string` | Optional | Fax number<br><br>**Constraints**: *Minimum Length*: `10`, *Maximum Length*: `10`, *Pattern*: `^\d{10}$` | getFax(): ?string | setFax(?string fax): void |
| `locationApiId` | `?string` | Optional | Location api ID<br><br>**Constraints**: *Maximum Length*: `36` | getLocationApiId(): ?string | setLocationApiId(?string locationApiId): void |
| `locationApiKey` | `?string` | Optional | Location api key<br><br>**Constraints**: *Maximum Length*: `36` | getLocationApiKey(): ?string | setLocationApiKey(?string locationApiKey): void |
| `locationC1` | `?string` | Optional | Can be used to store custom information for location.<br><br>**Constraints**: *Maximum Length*: `128` | getLocationC1(): ?string | setLocationC1(?string locationC1): void |
| `locationC2` | `?string` | Optional | Can be used to store custom information for location.<br><br>**Constraints**: *Maximum Length*: `128` | getLocationC2(): ?string | setLocationC2(?string locationC2): void |
| `locationC3` | `?string` | Optional | Can be used to store custom information for location.<br><br>**Constraints**: *Maximum Length*: `128` | getLocationC3(): ?string | setLocationC3(?string locationC3): void |
| `name` | `?string` | Optional | Name of the company<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `64` | getName(): ?string | setName(?string name): void |
| `officePhone` | `?string` | Optional | Office phone number<br><br>**Constraints**: *Minimum Length*: `10`, *Maximum Length*: `10` | getOfficePhone(): ?string | setOfficePhone(?string officePhone): void |
| `officeExtPhone` | `?string` | Optional | Office phone extension number<br><br>**Constraints**: *Maximum Length*: `10` | getOfficeExtPhone(): ?string | setOfficeExtPhone(?string officeExtPhone): void |
| `tz` | `?string` | Optional | Time zone<br><br>**Constraints**: *Maximum Length*: `30` | getTz(): ?string | setTz(?string tz): void |
| `parentId` | `?string` | Optional | Location GUID of the parent location<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getParentId(): ?string | setParentId(?string parentId): void |
| `showContactNotes` | `?bool` | Optional | If set to true will show 'Notes' tab on Contact | getShowContactNotes(): ?bool | setShowContactNotes(?bool showContactNotes): void |
| `showContactFiles` | `?bool` | Optional | If set to true will show 'Files' tab on Contact | getShowContactFiles(): ?bool | setShowContactFiles(?bool showContactFiles): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |
| `locationType` | [`?string(LocationTypeEnum)`](../../doc/models/location-type-enum.md) | Optional | Location Type | getLocationType(): ?string | setLocationType(?string locationType): void |
| `parentName` | `?string` | Optional | Name of the parent location | getParentName(): ?string | setParentName(?string parentName): void |
| `ticketHashKey` | `?string` | Optional | Ticket Hash Key<br><br>**Constraints**: *Maximum Length*: `36` | getTicketHashKey(): ?string | setTicketHashKey(?string ticketHashKey): void |

## Example (as JSON)

```json
{
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "modified_ts": 1422040992,
  "account_number": "5454545454545454",
  "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
  "contact_email_trx_receipt_default": true,
  "default_ach": "11e608a7d515f1e093242bb2",
  "default_cc": "11e608a442a5f1e092242dda",
  "email_reply_to": "email@domain.com",
  "fax": "3339998822",
  "location_api_id": "location-111111",
  "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
  "location_c1": "custom 1",
  "location_c2": "custom 2",
  "location_c3": "custom data 3",
  "name": "Sample Company Headquarters",
  "office_phone": "2481234567",
  "office_ext_phone": "1021021209",
  "tz": "America/New_York",
  "parent_id": "11e95f8ec39de8fbdb0a4f1a",
  "show_contact_notes": true,
  "show_contact_files": true,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "location_type": "merchant",
  "ticket_hash_key": "A5F443CADF4AE34BBCAADF4",
  "address": {
    "city": "city6",
    "state": "state2",
    "postal_code": "postal_code8",
    "country": "US",
    "street": "street6"
  }
}
```

