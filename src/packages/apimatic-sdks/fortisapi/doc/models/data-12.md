
# Data 12

## Structure

`Data12`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `threeDsServerTransId` | `?string` | Optional | Universally unique transaction identifier assigned by the 3DS Server to identify a single transaction. It has the same value as the corresponding received authentication request. This value has 36 characters in a format defined in IETF RFC 4122.<br><br>**Constraints**: *Maximum Length*: `36` | getThreeDsServerTransId(): ?string | setThreeDsServerTransId(?string threeDsServerTransId): void |
| `acsUrl` | `?string` | Optional | Fully qualified URL of the ACS in case the authentication response message indicates that further Cardholder interaction is required to complete the authentication.<br><br>This field is only present in Browser flow. | getAcsUrl(): ?string | setAcsUrl(?string acsUrl): void |
| `transactionStatus` | [`?string(TransactionStatusEnum)`](../../doc/models/transaction-status-enum.md) | Optional | Indicates whether a transaction qualifies as an authenticated transaction.<br><br>> Y - Authentication / Account verification successful<br>> <br>> N - Not authenticated / Account not verified; Transaction denied<br>> <br>> U - Authentication / Account verification could not be performed; technical or other problem<br>> <br>> C - In order to complete the authentication, a challenge is required<br>> <br>> R - Authentication / Account verification Rejected. Issuer is rejecting authentication/verification and request that authorization not be attempted<br>> <br>> A - Attempts processing performed; Not authenticated / verified, but a proof of attempt authentication / verification is provided<br>> <br>> D - In order to complete the authentication, a challenge is required. Decoupled Authentication confirmed. (Only if the 3DS Server has initiated authentication with EMV 3DS 2.2.0 version or greater)<br>> <br>> I - Informational Only; 3DS Requestor challenge preference acknowledged. (Only if the 3DS Server has initiated authentication with EMV 3DS 2.2.0 version or greater) | getTransactionStatus(): ?string | setTransactionStatus(?string transactionStatus): void |
| `authenticationValue` | `?string` | Optional | Payment System-specific value provided as part of the ACS registration for each supported DS. Authentication Value may be used to provide proof of authentication. | getAuthenticationValue(): ?string | setAuthenticationValue(?string authenticationValue): void |
| `eci` | `?string` | Optional | Payment System-specific value provided by the ACS to indicate the results of the attempt to authenticate the Cardholder. | getEci(): ?string | setEci(?string eci): void |
| `dsTransId` | `?string` | Optional | Universally unique transaction identifier assigned by the DS to identify a single transaction. | getDsTransId(): ?string | setDsTransId(?string dsTransId): void |
| `acsTransId` | `?string` | Optional | Universally unique transaction identifier assigned by the ACS to identify a single transaction. | getAcsTransId(): ?string | setAcsTransId(?string acsTransId): void |
| `messageVersion` | `?string` | Optional | Protocol version identifier This shall be the Protocol Version Number of the specification utilised by the system creating this message.<br>The Message Version Number is set by the 3DS Server which originates the protocol with the AReq message. The Message Version Number does not change during a 3DS transaction. | getMessageVersion(): ?string | setMessageVersion(?string messageVersion): void |
| `acsChallengeMandated` | [`?string(AcsChallengeMandatedEnum)`](../../doc/models/acs-challenge-mandated-enum.md) | Optional | Indication of whether a challenge is required for the transaction to be authorised due to local/regional mandates or other variable.<br><br>> Y - Challenge is mandated<br>> <br>> N - Challenge is not mandated | getAcsChallengeMandated(): ?string | setAcsChallengeMandated(?string acsChallengeMandated): void |
| `purchaseDate` | `?string` | Optional | Date and time of the purchase, converted into UTC. The field is limited to 14 characters, formatted as YYYYMMDDHHMMSS.<br><br>**Constraints**: *Maximum Length*: `14` | getPurchaseDate(): ?string | setPurchaseDate(?string purchaseDate): void |
| `base64EncodedChallengeRequest` | `?string` | Optional | Base64-encoded Challenge Request object in case the authentication response message indicates that further Cardholder interaction is required to complete the authentication. | getBase64EncodedChallengeRequest(): ?string | setBase64EncodedChallengeRequest(?string base64EncodedChallengeRequest): void |

## Example (as JSON)

```json
{
  "three_ds_server_trans_id": "ea0c9973-4cd0-4f9f-83b3-609bf22c69e7",
  "acs_url": "https://api.sandbox.fortis.tech/v1/acs/challenge",
  "transaction_status": "C",
  "ds_trans_id": "278c7508-cac6-4233-902d-b90f31f741c6",
  "acs_trans_id": "d7c1ee99-9478-44a6-b1f2-391e29c6b340",
  "message_version": "2.2.0",
  "acs_challenge_mandated": "Y",
  "purchase_date": "20240509111532",
  "base64_encoded_challenge_request": "eyJtZXNzYWdlVHlwZSI6IkNSZXEiLCJ0aHJlZURTU2VydmVyVHJhbnNJRCI6ImVhMGM5OTczLTRjZDAtNGY5Zi04M2IzLTYwOWJmMjJjNjllNyIsImFjc1RyYW5zSUQiOiIwNWViNTE2OS02ZTFkLTQ5NTMtYWQ3NC1hZWU5YmQ4ZTc1YmIiLCJjaGFsbGVuZ2VXaW5kb3dTaXplIjoiMDEiLCJtZXNzYWdlVmVyc2lvbiI6IjIuMi4wIn0=",
  "authentication_value": "authentication_value0",
  "eci": "eci8"
}
```

