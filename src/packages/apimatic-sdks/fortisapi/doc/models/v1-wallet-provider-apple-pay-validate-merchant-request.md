
# V1 Wallet Provider Apple Pay Validate Merchant Request

## Structure

`V1WalletProviderApplePayValidateMerchantRequest`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `url` | `string` | Required | Url obtained in the ApplePay button click event. Apple's URL that needs to be called to validate merchant. | getUrl(): string | setUrl(string url): void |
| `merchantId` | `string` | Required | Merchant ID | getMerchantId(): string | setMerchantId(string merchantId): void |
| `domainName` | `string` | Required | Domain Name | getDomainName(): string | setDomainName(string domainName): void |
| `displayName` | `string` | Required | Title | getDisplayName(): string | setDisplayName(string displayName): void |

## Example (as JSON)

```json
{
  "url": "https://apple-pay-gateway-cert.apple.com/paymentservices/startSession",
  "merchantId": "abc1234",
  "domainName": "website.domain.com",
  "displayName": "Sandwich Market"
}
```

