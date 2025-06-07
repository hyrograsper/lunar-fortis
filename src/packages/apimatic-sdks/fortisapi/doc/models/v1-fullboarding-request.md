
# V1 Fullboarding Request

## Structure

`V1FullboardingRequest`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `parentId` | `?string` | Optional | Location ID | getParentId(): ?string | setParentId(?string parentId): void |
| `templateId` | `?string` | Optional | The ID of the template to be used - this value will be provided by Fortis.<br><br>**Constraints**: *Maximum Length*: `20`, *Pattern*: `^[a-zA-Z0-9]*$` | getTemplateId(): ?string | setTemplateId(?string templateId): void |
| `clientAppId` | `?string` | Optional | Client Issues Id to track that can be used to track each submitted merchant application. This id should be generated and sent in the request payload, and will be returned in the response payload. If no id is submitted in the payload request, this field will be null in the response.<br><br>**Constraints**: *Maximum Length*: `50` | getClientAppId(): ?string | setClientAppId(?string clientAppId): void |
| `email` | `string` | Required | Merchant email address.<br><br>**Constraints**: *Maximum Length*: `100` | getEmail(): string | setEmail(string email): void |
| `dbaName` | `string` | Required | Merchant 'Doing Business As' name.<br><br>**Constraints**: *Maximum Length*: `100` | getDbaName(): string | setDbaName(string dbaName): void |
| `legalName` | `?string` | Optional | Merchant legal name.<br><br>> (leave blank if same as DBA name).<br><br>**Constraints**: *Maximum Length*: `100` | getLegalName(): ?string | setLegalName(?string legalName): void |
| `website` | `?string` | Optional | Merchant's business website.<br><br>> (Required if "ecommerce_percent" is greater than 0).<br><br>**Constraints**: *Maximum Length*: `100` | getWebsite(): ?string | setWebsite(?string website): void |
| `phoneNumber` | `string` | Required | Merchant's phone number.<br><br>**Constraints**: *Maximum Length*: `10` | getPhoneNumber(): string | setPhoneNumber(string phoneNumber): void |
| `ownershipType` | [`string(OwnershipTypeEnum)`](../../doc/models/ownership-type-enum.md) | Required | The Ownership Type of the merchant's business.<br><br>**Constraints**: *Maximum Length*: `10` | getOwnershipType(): string | setOwnershipType(string ownershipType): void |
| `fedTaxId` | `string` | Required | Federal Tax ID (EIN).<br><br>**Constraints**: *Maximum Length*: `10` | getFedTaxId(): string | setFedTaxId(string fedTaxId): void |
| `averageTicket` | `int` | Required | Average Transaction Amount.<br><br>> Average transaction amount rounded to the next dollar.<br><br>**Constraints**: `>= 1`, `<= 99999` | getAverageTicket(): int | setAverageTicket(int averageTicket): void |
| `highTicket` | `int` | Required | Highest transaction amount rounded to the next dollar<br><br>> Highest transaction amount rounded to the next dollar (No decimal).<br><br>**Constraints**: `>= 1`, `<= 30000` | getHighTicket(): int | setHighTicket(int highTicket): void |
| `ccMonthlyVolume` | `int` | Required | Average monthly credit card volume rounded to the next dollar.<br><br>> Average monthly credit card volume rounded to the next dollar.<br><br>**Constraints**: `>= 1` | getCcMonthlyVolume(): int | setCcMonthlyVolume(int ccMonthlyVolume): void |
| `ecMonthlyVolume` | `?int` | Optional | Average monthly echeck volume rounded to the next dollar.<br><br>> Average monthly echeck volume rounded to the next dollar.<br><br>**Constraints**: `>= 1` | getEcMonthlyVolume(): ?int | setEcMonthlyVolume(?int ecMonthlyVolume): void |
| `mccCode` | `string` | Required | Merchant's MCC code.<br><br>**Constraints**: *Maximum Length*: `10` | getMccCode(): string | setMccCode(string mccCode): void |
| `businessDescription` | `string` | Required | Description of Goods or Services.<br><br>**Constraints**: *Maximum Length*: `200` | getBusinessDescription(): string | setBusinessDescription(string businessDescription): void |
| `swipedPercent` | `int` | Required | Card present/swiped percentage<br><br>> The sum total of "swiped_percent", "keyed_percent" and "ecommerce_percent" must add up to 100.<br><br>**Constraints**: `>= 0`, `<= 100` | getSwipedPercent(): int | setSwipedPercent(int swipedPercent): void |
| `keyedPercent` | `int` | Required | Card not present/keyed percentage<br><br>> The sum total of "swiped_percent", "keyed_percent" and "ecommerce_percent" must add up to 100.<br><br>**Constraints**: `>= 0`, `<= 100` | getKeyedPercent(): int | setKeyedPercent(int keyedPercent): void |
| `ecommercePercent` | `int` | Required | eCommerce percentage.<br><br>> The sum total of "swiped_percent", "keyed_percent" and "ecommerce_percent" must add up to 100.<br><br>**Constraints**: `>= 0`, `<= 100` | getEcommercePercent(): int | setEcommercePercent(int ecommercePercent): void |
| `isForeignEntity` | `bool` | Required | Indicates whether or not the merchant is a foreign entity. | getIsForeignEntity(): bool | setIsForeignEntity(bool isForeignEntity): void |
| `personallyGuaranteed` | `bool` | Required | Indicates whether or not the merchant is personally guaranteed. | getPersonallyGuaranteed(): bool | setPersonallyGuaranteed(bool personallyGuaranteed): void |
| `preferredLanguage` | [`?string(PreferredLanguageEnum)`](../../doc/models/preferred-language-enum.md) | Optional | Merchant preferred language. English(“en-US”) will be used if no value is supplied.<br><br>> Merchant preferred language. English(“en-US”) will be used if no value is supplied. | getPreferredLanguage(): ?string | setPreferredLanguage(?string preferredLanguage): void |
| `addresses` | [`Address81[]`](../../doc/models/address-81.md) | Required | - | getAddresses(): array | setAddresses(array addresses): void |
| `owners` | [`Owner[]`](../../doc/models/owner.md) | Required | - | getOwners(): array | setOwners(array owners): void |
| `bankAccounts` | [`BankAccount1[]`](../../doc/models/bank-account-1.md) | Required | - | getBankAccounts(): array | setBankAccounts(array bankAccounts): void |
| `documents` | [`?(Document[])`](../../doc/models/document.md) | Optional | - | getDocuments(): ?array | setDocuments(?array documents): void |
| `pricingElements` | [`?(PricingElement[])`](../../doc/models/pricing-element.md) | Optional | - | getPricingElements(): ?array | setPricingElements(?array pricingElements): void |
| `kycResponseObjects` | [`?(KycResponseObject[])`](../../doc/models/kyc-response-object.md) | Optional | - | getKycResponseObjects(): ?array | setKycResponseObjects(?array kycResponseObjects): void |
| `metadata` | `?array` | Optional | Valid JSON of metadata related to merchant. | getMetadata(): ?array | setMetadata(?array metadata): void |
| `signerIp` | `?string` | Optional | Signer IP address. | getSignerIp(): ?string | setSignerIp(?string signerIp): void |
| `secCodes` | [`?(string(SecCodeEnum)[])`](../../doc/models/sec-code-enum.md) | Optional | Array of SEC codes that will be allowed, Only applicable for ACH. Valid values are 'PPD', 'WEB', 'TEL', 'CCD'. | getSecCodes(): ?array | setSecCodes(?array secCodes): void |

## Example (as JSON)

```json
{
  "template_id": "1234YourTemplateCode",
  "client_app_id": "ABC123",
  "email": "email@domain.com",
  "dba_name": "Discount Home Goods",
  "legal_name": "Total Home Goods, LLP",
  "website": "http://www.example.com",
  "phone_number": "5555551234",
  "ownership_type": "llp",
  "fed_tax_id": "0000000000",
  "average_ticket": 15,
  "high_ticket": 150,
  "cc_monthly_volume": 100,
  "ec_monthly_volume": 22,
  "mcc_code": "7629",
  "business_description": "Yard services.",
  "swiped_percent": 0,
  "keyed_percent": 0,
  "ecommerce_percent": 100,
  "is_foreign_entity": true,
  "personally_guaranteed": false,
  "preferred_language": "fr-CA",
  "addresses": [
    {
      "address_line_1": "121 E Main",
      "address_line_2": "Apt 707",
      "city": "Dallas",
      "state_province": "TX",
      "postal_code": "75087",
      "country_code": "US",
      "address_type": "location"
    }
  ],
  "owners": [
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
  ],
  "bank_accounts": [
    {
      "account_holder_name": "James Bond",
      "routing_number": "111111111",
      "account_number": "1234567",
      "is_primary": true,
      "account_type": "checking",
      "alt_deposit_types": [
        "alt_deposit_types0"
      ]
    }
  ],
  "signer_ip": "192.168.0.10",
  "parent_id": "parent_id8"
}
```

