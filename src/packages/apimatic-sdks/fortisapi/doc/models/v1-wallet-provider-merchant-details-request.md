
# V1 Wallet Provider Merchant Details Request

## Structure

`V1WalletProviderMerchantDetailsRequest`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `merchantOrigin` | `string` | Required | Domain name where the Apple or Google pay button is or will be displayed. Full domain name including subdomain. | getMerchantOrigin(): string | setMerchantOrigin(string merchantOrigin): void |

## Example (as JSON)

```json
{
  "merchantOrigin": "dev.pay.site"
}
```

