
# Detail 1

Error details

## Structure

`Detail1`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `threeDsServerTransId` | `?string` | Optional | Universally unique transaction identifier assigned by the 3DS Server to identify a single transaction. It has the same value as the corresponding received authentication request. This value has 36 characters in a format defined in IETF RFC 4122. | getThreeDsServerTransId(): ?string | setThreeDsServerTransId(?string threeDsServerTransId): void |
| `acsTransId` | `?string` | Optional | Universally Unique transaction identifier assigned by the ACS to identify a single transaction. | getAcsTransId(): ?string | setAcsTransId(?string acsTransId): void |
| `dsTransId` | `?string` | Optional | Universally unique transaction identifier assigned by the DS to identify a single transaction. | getDsTransId(): ?string | setDsTransId(?string dsTransId): void |
| `errorCode` | `?string` | Optional | Code indicating the type of problem identified.<br><br>> 101 - Message received invalid<br>> <br>> 102 - Message version number not supported<br>> <br>> 103 - Sent messages limit exceeded. Only used for PReq<br>> <br>> 201 - Required element missing<br>> <br>> 202 - Critical message extension not recognized<br>> <br>> 203 - Format on one or more elements is invalid according to the specs<br>> <br>> 204 - Duplicate data element<br>> <br>> 205 - Card range overlap<br>> <br>> 206 - Card range action indicator<br>> <br>> 207 - Value in the reserved value range<br>> <br>> 301 - Transaction id is not recognized<br>> <br>> 302 - Data decryption failure<br>> <br>> 303 - Access denied, invalid endpoint<br>> <br>> 304 - ISO code is not valid<br>> <br>> 305 - Transaction data is not valid<br>> <br>> 306 - Merchant category code is not valid for payment system<br>> <br>> 307 - Serial number is not valid<br>> <br>> 308 - Signature verification failure<br>> <br>> 309 - Validation against content security policies failure<br>> <br>> 310 - Incorrect cryptographic algorithm<br>> <br>> 311 - Incorrect kid<br>> <br>> 312 - Duplicate message<br>> <br>> 313 - Inconsistent RReq message<br>> <br>> 314 - Multiple creq messages are not supported<br>> <br>> 315 - CReq message received after the RReq message<br>> <br>> 402 - Transaction timed out<br>> <br>> 403 - Transient system failure<br>> <br>> 404 - Permanent system failure<br>> <br>> 405 - System connection failure<br>> <br>> 911 - UnionPay specific error code. Present when Data fields relevance check failed (ECI value and AV appearance are inconsistent with transaction status).<br>> <br>> 912 - UnionPay specific error code. Present when duplicated transaction ID (Transaction ID should be unique for each AReq request). | getErrorCode(): ?string | setErrorCode(?string errorCode): void |
| `errorComponent` | `?string` | Optional | Code indicating the 3-D Secure component that identified the error.<br><br>> C - 3DS SDK<br>> <br>> S - 3DS Server<br>> <br>> D - DS<br>> <br>> A - ACS | getErrorComponent(): ?string | setErrorComponent(?string errorComponent): void |
| `errorDescription` | `?string` | Optional | Text describing the problem identified. | getErrorDescription(): ?string | setErrorDescription(?string errorDescription): void |
| `errorDetail` | `?string` | Optional | Additional detail regarding the problem identified. | getErrorDetail(): ?string | setErrorDetail(?string errorDetail): void |
| `sdkTransId` | `?string` | Optional | Universally unique transaction identifier assigned by the 3DS SDK to identify a single transaction. | getSdkTransId(): ?string | setSdkTransId(?string sdkTransId): void |
| `errorMessageType` | `?string` | Optional | The Message Type that was identified as erroneous.<br><br>> AReq - Authentication request<br>> <br>> ARes - Authentication response<br>> <br>> CReq - Challenge request<br>> <br>> CRes - Challenge response<br>> <br>> PReq - Preparation request<br>> <br>> PRes - Preparation response<br>> <br>> RReq - Results request<br>> <br>> RRes - Results response<br>> <br>> Erro - Error<br>> <br>> 3dsMethodReq - 3DS method request<br>> <br>> OReq - Operation request. Since 2.3.1<br>> <br>> ORes - Operation response. Since 2.3.1 | getErrorMessageType(): ?string | setErrorMessageType(?string errorMessageType): void |

## Example (as JSON)

```json
{
  "three_ds_server_trans_id": "three_ds_server_trans_id0",
  "acs_trans_id": "acs_trans_id2",
  "ds_trans_id": "ds_trans_id8",
  "error_code": "error_code0",
  "error_component": "error_component6"
}
```

