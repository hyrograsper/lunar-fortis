
# Owner

Array of merchant owners.

## Structure

`Owner`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `firstName` | `string` | Required | Owner's first name.<br><br>**Constraints**: *Maximum Length*: `20` | getFirstName(): string | setFirstName(string firstName): void |
| `lastName` | `string` | Required | Owner's last name.<br><br>**Constraints**: *Maximum Length*: `20` | getLastName(): string | setLastName(string lastName): void |
| `middleName` | `?string` | Optional | Owner's middle name.<br><br>**Constraints**: *Maximum Length*: `20` | getMiddleName(): ?string | setMiddleName(?string middleName): void |
| `title` | `string` | Required | Owner's title.<br><br>**Constraints**: *Maximum Length*: `20` | getTitle(): string | setTitle(string title): void |
| `dateOfBirth` | `string` | Required | Owner's date of birth.<br><br>**Constraints**: *Maximum Length*: `10`, *Pattern*: `^[\d]{4}-[\d]{2}-[\d]{2}$` | getDateOfBirth(): string | setDateOfBirth(string dateOfBirth): void |
| `addressLine1` | `string` | Required | Owner's street address.<br><br>**Constraints**: *Maximum Length*: `100` | getAddressLine1(): string | setAddressLine1(string addressLine1): void |
| `addressLine2` | `string` | Required | Line 2 of owner's address.<br><br>**Constraints**: *Maximum Length*: `20` | getAddressLine2(): string | setAddressLine2(string addressLine2): void |
| `city` | `string` | Required | Owner's address city.<br><br>**Constraints**: *Maximum Length*: `50` | getCity(): string | setCity(string city): void |
| `stateProvince` | `string` | Required | Owner's address state/province.<br><br>**Constraints**: *Maximum Length*: `2` | getStateProvince(): string | setStateProvince(string stateProvince): void |
| `postalCode` | `string` | Required | Owner's address postal code.<br><br>**Constraints**: *Maximum Length*: `10` | getPostalCode(): string | setPostalCode(string postalCode): void |
| `countryCode` | `string` | Required | Owner's address country.<br><br>**Constraints**: *Maximum Length*: `2` | getCountryCode(): string | setCountryCode(string countryCode): void |
| `ssn` | `string` | Required | Owner's full SSN.<br><br>**Constraints**: *Maximum Length*: `10` | getSsn(): string | setSsn(string ssn): void |
| `ownershipPercent` | `int` | Required | Owner's ownership percent.<br><br>**Constraints**: `>= 0`, `<= 100` | getOwnershipPercent(): int | setOwnershipPercent(int ownershipPercent): void |
| `phoneNumber` | `string` | Required | Owner's phone number.<br><br>**Constraints**: *Maximum Length*: `20` | getPhoneNumber(): string | setPhoneNumber(string phoneNumber): void |
| `emailAddress` | `string` | Required | Owner's email address.<br><br>**Constraints**: *Maximum Length*: `100` | getEmailAddress(): string | setEmailAddress(string emailAddress): void |
| `isController` | `bool` | Required | Flag indicating whether this owner is the control owner. Maximum of 1 owner can be marked as control..<br><br>> Flag indicating whether this owner is the control owner. Maximum of 1 owner can be marked as control. | getIsController(): bool | setIsController(bool isController): void |
| `isSigner` | `bool` | Required | Flag indicating whether or not the owner is a signer for the business. Maximum of 1 owner can be marked as signer.<br><br>> Flag indicating whether or not the owner is a signer for the business. Maximum of 1 owner can be marked as signer. | getIsSigner(): bool | setIsSigner(bool isSigner): void |

## Example (as JSON)

```json
{
  "first_name": "James",
  "last_name": "Bond",
  "middle_name": "Tyler",
  "title": "CEO",
  "date_of_birth": "2021-12-01",
  "address_line_1": "133 S Goliad St",
  "address_line_2": "Suite 120",
  "city": "Rockwall",
  "state_province": "TX",
  "postal_code": "75429",
  "country_code": "US",
  "ssn": "000000000",
  "ownership_percent": 100,
  "phone_number": "9042142323",
  "email_address": "james@example.com",
  "is_controller": true,
  "is_signer": true
}
```

