
# Billing Address 24

Cardholder billing address object

## Structure

`BillingAddress24`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `city` | `?string` | Optional | The city of the Cardholder billing address associated with the card used for this purchase.<br><br>This field is required unless market or regional mandate restricts sending this information.<br><br>**Constraints**: *Maximum Length*: `50` | getCity(): ?string | setCity(?string city): void |
| `countryCode` | `?string` | Optional | The ISO 3166-1 alpha-3 or alpha-2 country of the Cardholder billing address associated with the card used for this purchase.<br><br>The field is required if Cardholder Billing Address State is present and unless market or regional mandate restricts sending this information.<br><br>**Constraints**: *Maximum Length*: `3` | getCountryCode(): ?string | setCountryCode(?string countryCode): void |
| `addressLine1` | `?string` | Optional | First line of the street address or equivalent local portion of the Cardholder billing address associated with the card use for this purchase.<br><br>This field is required unless market or regional mandate restricts sending this information.<br><br>**Constraints**: *Maximum Length*: `50` | getAddressLine1(): ?string | setAddressLine1(?string addressLine1): void |
| `addressLine2` | `?string` | Optional | Second line of the street address or equivalent local portion of the Cardholder billing address associated with the card use for this purchase.<br><br>This field is required unless market or regional mandate restricts sending this information.<br><br>**Constraints**: *Maximum Length*: `50` | getAddressLine2(): ?string | setAddressLine2(?string addressLine2): void |
| `addressLine3` | `?string` | Optional | Third line of the street address or equivalent local portion of the Cardholder billing address associated with the card use for this purchase.<br><br>This field is required unless market or regional mandate restricts sending this information.<br><br>**Constraints**: *Maximum Length*: `50` | getAddressLine3(): ?string | setAddressLine3(?string addressLine3): void |
| `postalCode` | `?string` | Optional | ZIP or other postal code of the Cardholder billing address associated with the card used for this purchase.<br><br>This field is required unless market or regional mandate restricts sending this information.<br><br>**Constraints**: *Maximum Length*: `16` | getPostalCode(): ?string | setPostalCode(?string postalCode): void |
| `state` | `?string` | Optional | The state or province of the Cardholder billing address associated with the card used for this purchase. The value should be the country subdivision code defined in ISO 3166-2.<br><br>This field is required unless State is not applicable for this country and unless market or regional mandate restricts sending this information.<br><br>**Constraints**: *Maximum Length*: `3` | getState(): ?string | setState(?string state): void |

## Example (as JSON)

```json
{
  "city": "Plano",
  "country_code": "USA",
  "address_line_1": "6111 W Plano Parkway",
  "address_line_2": "Suite 2700",
  "postal_code": "75093",
  "state": "TX",
  "address_line_3": "address_line_30"
}
```

