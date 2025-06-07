
# Three Ds Requestor Prior Authentication Info

## Structure

`ThreeDsRequestorPriorAuthenticationInfo`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `threeDsReqPriorRef` | `?string` | Optional | This data element provides additional information to the ACS to determine the best approach for handling a request. The field is limited to 36 characters containing ACS Transaction ID for a prior authenticated transaction (for example, the first recurring transaction that was authenticated with the cardholder).<br><br>**Constraints**: *Maximum Length*: `36` | getThreeDsReqPriorRef(): ?string | setThreeDsReqPriorRef(?string threeDsReqPriorRef): void |
| `threeDsReqPriorAuthMethod` | [`?string(ThreeDsReqPriorAuthMethodEnum)`](../../doc/models/three-ds-req-prior-auth-method-enum.md) | Optional | Mechanism used by the Cardholder to previously authenticate to the 3DS Requestor.<br><br>> 01 - Frictionless authentication occurred by ACS<br>> <br>> 02 - Cardholder challenge occurred by ACS<br>> <br>> 03 - AVS verified<br>> <br>> 04 - Other issuer methods<br>> <br>> 80 through 99 - PS-specific value (dependent on the payment scheme type).<br><br>**Constraints**: *Maximum Length*: `12` | getThreeDsReqPriorAuthMethod(): ?string | setThreeDsReqPriorAuthMethod(?string threeDsReqPriorAuthMethod): void |
| `threeDsReqPriorAuthTimestamp` | `?string` | Optional | Date and time converted into UTC of the prior authentication. Accepted date format is YYYYMMDDHHMM. | getThreeDsReqPriorAuthTimestamp(): ?string | setThreeDsReqPriorAuthTimestamp(?string threeDsReqPriorAuthTimestamp): void |
| `threeDsReqPriorAuthData` | `?string` | Optional | Data that documents and supports a specific authentication process. In the current version of the specification this data element is not defined in detail, however the intention is that for each 3DS Requestor Authentication Method, this field carry data that the ACS can use to verify the authentication process. In future versions of the application, these details are expected to be included.<br><br>**Constraints**: *Maximum Length*: `2048` | getThreeDsReqPriorAuthData(): ?string | setThreeDsReqPriorAuthData(?string threeDsReqPriorAuthData): void |

## Example (as JSON)

```json
{
  "three_ds_req_prior_ref": "three_ds_req_prior_ref8",
  "three_ds_req_prior_auth_method": "98",
  "three_ds_req_prior_auth_timestamp": "three_ds_req_prior_auth_timestamp8",
  "three_ds_req_prior_auth_data": "three_ds_req_prior_auth_data6"
}
```

