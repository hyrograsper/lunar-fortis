
# V1 Tokens Cc Request 1

## Structure

`V1TokensCcRequest1`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `accountHolderName` | `?string` | Optional | Account holder name<br><br>> For CC, this is the 'Name (as it appears) on Card'. For ACH, this is the 'Name on Account'.<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `32` | getAccountHolderName(): ?string | setAccountHolderName(?string accountHolderName): void |
| `accountVaultApiId` | `?string` | Optional | This field can be used to correlate Tokens in our system to data within an outside software integration<br><br>> Must be unique per location. When running a transaction and using a stored token, this field can be used in place of account_vault_id.<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `64` | getAccountVaultApiId(): ?string | setAccountVaultApiId(?string accountVaultApiId): void |
| `tokenApiId` | `?string` | Optional | This field can be used to correlate Tokens in our system to data within an outside software integration<br><br>> Must be unique per location. When running a transaction and using a stored token, this field can be used in place of token_id.<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `64` | getTokenApiId(): ?string | setTokenApiId(?string tokenApiId): void |
| `accountvaultC1` | `?string` | Optional | DEPRECATED (Use token_c1 instead)<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `128` | getAccountvaultC1(): ?string | setAccountvaultC1(?string accountvaultC1): void |
| `accountvaultC2` | `?string` | Optional | DEPRECATED (Use token_c2 instead)<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `128` | getAccountvaultC2(): ?string | setAccountvaultC2(?string accountvaultC2): void |
| `accountvaultC3` | `?string` | Optional | DEPRECATED (Use token_c3 instead)<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `128` | getAccountvaultC3(): ?string | setAccountvaultC3(?string accountvaultC3): void |
| `tokenC1` | `?string` | Optional | Custom field 1 for API users to store custom data<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `128` | getTokenC1(): ?string | setTokenC1(?string tokenC1): void |
| `tokenC2` | `?string` | Optional | Custom field 2 for API users to store custom data<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `128` | getTokenC2(): ?string | setTokenC2(?string tokenC2): void |
| `tokenC3` | `?string` | Optional | Custom field 3 for API users to store custom data<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `128` | getTokenC3(): ?string | setTokenC3(?string tokenC3): void |
| `achSecCode` | [`?string(AchSecCode3Enum)`](../../doc/models/ach-sec-code-3-enum.md) | Optional | SEC code for the account | getAchSecCode(): ?string | setAchSecCode(?string achSecCode): void |
| `billingAddress` | [`?BillingAddress`](../../doc/models/billing-address.md) | Optional | Billing Address Object | getBillingAddress(): ?BillingAddress | setBillingAddress(?BillingAddress billingAddress): void |
| `contactId` | `?string` | Optional | Used to associate the Token with a Contact.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getContactId(): ?string | setContactId(?string contactId): void |
| `customerId` | `?string` | Optional | Used to store a customer identification number.<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `50` | getCustomerId(): ?string | setCustomerId(?string customerId): void |
| `identityVerification` | [`?IdentityVerification2`](../../doc/models/identity-verification-2.md) | Optional | Identity verification | getIdentityVerification(): ?IdentityVerification2 | setIdentityVerification(?IdentityVerification2 identityVerification): void |
| `locationId` | `?string` | Optional | A valid Location Id associated with the Contact for this Token<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationId(): ?string | setLocationId(?string locationId): void |
| `previousAccountVaultApiId` | `?string` | Optional | Can be used to pull payment info from a previous token api id.<br><br>**Constraints**: *Maximum Length*: `64` | getPreviousAccountVaultApiId(): ?string | setPreviousAccountVaultApiId(?string previousAccountVaultApiId): void |
| `previousTokenApiId` | `?string` | Optional | Can be used to pull payment info from a previous token api id.<br><br>**Constraints**: *Maximum Length*: `64` | getPreviousTokenApiId(): ?string | setPreviousTokenApiId(?string previousTokenApiId): void |
| `previousAccountVaultId` | `?string` | Optional | Can be used to pull payment info from a previous token.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getPreviousAccountVaultId(): ?string | setPreviousAccountVaultId(?string previousAccountVaultId): void |
| `previousTokenId` | `?string` | Optional | Can be used to pull payment info from a previous token.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getPreviousTokenId(): ?string | setPreviousTokenId(?string previousTokenId): void |
| `previousTransactionId` | `?string` | Optional | Can be used to pull payment info from a previous transaction.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getPreviousTransactionId(): ?string | setPreviousTransactionId(?string previousTransactionId): void |
| `accountNumber` | `?string` | Optional | Account number<br><br>> For CC transactions, a credit card number. For ACH transactions, a bank account number. String lengths are conditional, CC should be 13-19 and ACH should be 4-19.<br><br>**Constraints**: *Minimum Length*: `4`, *Maximum Length*: `19`, *Pattern*: `^[\d]+$` | getAccountNumber(): ?string | setAccountNumber(?string accountNumber): void |
| `termsAgree` | `?bool` | Optional | Terms agreement. | getTermsAgree(): ?bool | setTermsAgree(?bool termsAgree): void |
| `termsAgreeIp` | `?string` | Optional | The ip address of the client that agreed to terms. | getTermsAgreeIp(): ?string | setTermsAgreeIp(?string termsAgreeIp): void |
| `title` | `?string` | Optional | Used to describe the Token for easier identification within our UI.<br><br>**Constraints**: *Minimum Length*: `1`, *Maximum Length*: `16` | getTitle(): ?string | setTitle(?string title): void |
| `tokenImportId` | `?string` | Optional | Token Import Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getTokenImportId(): ?string | setTokenImportId(?string tokenImportId): void |
| `secureDirectoryServerTransactionId` | `?string` | Optional | (ECOMM) Directory Server Transaction ID (Such as XID, TAVV) | getSecureDirectoryServerTransactionId(): ?string | setSecureDirectoryServerTransactionId(?string secureDirectoryServerTransactionId): void |
| `secureProtocolVersion` | `?int` | Optional | (ECOMM)  Secure Program Protocol Version | getSecureProtocolVersion(): ?int | setSecureProtocolVersion(?int secureProtocolVersion): void |
| `secureAuthData` | `?string` | Optional | (ECOMM) The token authentication value associated with 3D secure transactions (Such as CAVV, UCAF auth data) | getSecureAuthData(): ?string | setSecureAuthData(?string secureAuthData): void |
| `secureCollectionIndicator` | `?int` | Optional | (ECOMM) Used for UCAF collection indicator or Discover Autentication type | getSecureCollectionIndicator(): ?int | setSecureCollectionIndicator(?int secureCollectionIndicator): void |
| `threeDsServerTransId` | `?string` | Optional | 3DS Server Transaction ID.  If this field is sent and 3DS authentication was done with Fortis, the 3DS fields secure_directory_server_transaction_id, secure_protocol_version, and secure_collection_indicator will be pre-populated. | getThreeDsServerTransId(): ?string | setThreeDsServerTransId(?string threeDsServerTransId): void |
| `acsTransactionId` | `?string` | Optional | ACS Transaction ID<br><br>**Constraints**: *Maximum Length*: `36` | getAcsTransactionId(): ?string | setAcsTransactionId(?string acsTransactionId): void |
| `joi` | [`?Joi4`](../../doc/models/joi-4.md) | Optional | - | getJoi(): ?Joi4 | setJoi(?Joi4 joi): void |
| `expDate` | `?string` | Optional | Required for CC. The Expiration Date for the credit card. (MMYY format).<br><br>**Constraints**: *Pattern*: `^(0[1-9]\|1[0-2])[0-9]{2}$` | getExpDate(): ?string | setExpDate(?string expDate): void |

## Example (as JSON)

```json
{
  "account_holder_name": "John Smith",
  "account_vault_api_id": "accountvaultabcd",
  "token_api_id": "tokenabcd",
  "accountvault_c1": "accountvault custom 1",
  "accountvault_c2": "accountvault custom 2",
  "accountvault_c3": "accountvault custom 3",
  "token_c1": "token custom 1",
  "token_c2": "token custom 2",
  "token_c3": "token custom 3",
  "ach_sec_code": "WEB",
  "contact_id": "11e95f8ec39de8fbdb0a4f1a",
  "customer_id": "123456",
  "location_id": "11e95f8ec39de8fbdb0a4f1a",
  "previous_account_vault_api_id": "previousaccountvault123456",
  "previous_token_api_id": "previousaccountvault123456",
  "previous_account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
  "previous_token_id": "11e95f8ec39de8fbdb0a4f1a",
  "previous_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "account_number": "545454545454545",
  "terms_agree": true,
  "terms_agree_ip": "192.168.0.10",
  "title": "Test CC Account",
  "token_import_id": "11e95f8ec39de8fbdb0a4f1a",
  "secure_directory_server_transaction_id": "d65e93c3-35ab-41ba-b307-767bfc19eae",
  "secure_protocol_version": 2,
  "secure_auth_data": "vVwL7UNHCf8W8M2LAfvRChNHN7c%3D",
  "three_ds_server_trans_id": "d65e93c3-35ab-41ba-b307-767bfc19eae",
  "acs_transaction_id": "13c701a3-5a88-4c45-89e9-ef65e50a8bf9",
  "exp_date": "0929"
}
```

