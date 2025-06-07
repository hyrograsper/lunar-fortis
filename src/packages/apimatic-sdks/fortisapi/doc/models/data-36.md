
# Data 36

## Structure

`Data36`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `merchantSession` | `?string` | Optional | String formatted merchantSession object.  Needs to be passed to the session.completeMerchantValidation event in JS. | getMerchantSession(): ?string | setMerchantSession(?string merchantSession): void |

## Example (as JSON)

```json
{
  "merchantSession": "{\"epochTimestamp\":1689772866529,\"expiresAt\":1689776466529,\"merchantSessionIdentifier\":\"SSH3D9224\",\"nonce\":\"d70dbe8a\",\"merchantIdentifier\":\"46A940\",\"domainName\":\"paygistixcert.paymentlogistics.net\",\"displayName\":\"F\",\"signature\":\"30800609f6e2\",\"operationalAnalyticsIdentifier\":\"F:46A4E40\",\"retries\":0,\"pspId\":\"ADD36D\"}"
}
```

