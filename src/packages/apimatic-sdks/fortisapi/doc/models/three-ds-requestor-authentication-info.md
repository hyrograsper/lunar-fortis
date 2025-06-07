
# Three Ds Requestor Authentication Info

## Structure

`ThreeDsRequestorAuthenticationInfo`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `threeDsReqAuthMethod` | [`?string(ThreeDsReqAuthMethodEnum)`](../../doc/models/three-ds-req-auth-method-enum.md) | Optional | Mechanism used by the Cardholder to authenticate to the 3DS Requestor. "07" and "08" are accepted as well if 3DS Server initiates authentication with EMV 3DS 2.2.0 version or greater (required protocol version can be set in the preferred_protocol_version field)<br><br>> 01 - No 3DS Requestor authentication occurred (i.e. cardholder "logged in" as guest)<br>> <br>> 02 - Login to the cardholder account at the 3DS Requestor system using 3DS Requestor's own credentials<br>> <br>> 03 - Login to the cardholder account at the 3DS Requestor system using federated ID<br>> <br>> 04 - Login to the cardholder account at the 3DS Requestor system using issuer credentials<br>> <br>> 05 - Login to the cardholder account at the 3DS Requestor system using third-party authentication<br>> <br>> 06 - Login to the cardholder account at the 3DS Requestor system using FIDO Authenticator<br>> <br>> 07 - Login to the cardholder account at the 3DS Requestor system using FIDO Authenticator (FIDO assurance data signed) (EMV 3DS 2.2.0 version or greater)<br>> <br>> 08 - SRC Assurance Data (EMV 3DS 2.2.0 version or greater)<br>> <br>> 80 through 99 - can be used for PS-specific values, regardless of protocol version | getThreeDsReqAuthMethod(): ?string | setThreeDsReqAuthMethod(?string threeDsReqAuthMethod): void |
| `threeDsReqAuthTimestamp` | `?string` | Optional | Date and time converted into UTC of the cardholder authentication. Field is limited to 12 characters and accepted format is YYYYMMDDHHMM<br><br>**Constraints**: *Maximum Length*: `12` | getThreeDsReqAuthTimestamp(): ?string | setThreeDsReqAuthTimestamp(?string threeDsReqAuthTimestamp): void |
| `threeDsReqAuthData` | `?string` | Optional | Stringified array of objects that documents and supports a specific authentication process. In the current version of the specification, this data element is not defined in detail, however the intention is that for each 3DS Requestor Authentication Method, this field carry data that the ACS can use to verify the authentication process. For example, if the 3DS<br>Requestor Authentication Method is:<br>03 -> then this element can carry information about the provider of the federated ID and related information<br>06 -> then this element can carry the FIDO attestation data (incl. the signature)<br>07 -> then this element can carry FIDO Attestation data with the FIDO assurance data signed.<br>08 -> then this element can carry the SRC assurance data.<br>In versions prior to 2.3.1, this array is limited to a single object.<br>Starting from EMVCo version 2.3.1, the array may have 1-3 elements.<br><br>This field is optional, but recommended to include.<br><br>**Constraints**: *Maximum Length*: `20000` | getThreeDsReqAuthData(): ?string | setThreeDsReqAuthData(?string threeDsReqAuthData): void |

## Example (as JSON)

```json
{
  "three_ds_req_auth_method": "04",
  "three_ds_req_auth_timestamp": "202403291556",
  "three_ds_req_auth_data": "three_ds_req_auth_data8"
}
```

