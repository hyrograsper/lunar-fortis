
# Merchant Risk Indicator

Contains purchase information

## Structure

`MerchantRiskIndicator`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `shipIndicator` | [`?string(ShipIndicatorEnum)`](../../doc/models/ship-indicator-enum.md) | Optional | Indicates shipping method chosen for the transaction. Merchants must choose the Shipping Indicator code that most accurately describes the cardholder's specific transaction. If one or more items are included in the sale, use the Shipping Indicator code for the physical goods, or if all digital goods, use the code that describes the most expensive item.<br><br>> 01 - Ship to cardholder's billing address<br>> <br>> 02 - Ship to another verified address on file with merchant<br>> <br>> 03 - Ship to address that is different than the cardholder's billing address<br>> <br>> 04 - "Ship to Store" / Pick-up at local store (Store address shall be populated in shipping address fields)<br>> <br>> 05 - Digital goods (includes online services, electronic gift cards and redemption codes)<br>> <br>> 06 - Travel and Event tickets, not shipped<br>> <br>> 07 - Other (for example, Gaming, digital services not shipped, e-media subscriptions, etc.)<br>> <br>> 08 - Pick-up and go delivery. Availble in EMV 3DS 2.3.1 and later<br>> <br>> 09 - Locker delivery (or other automated pick-up). Availble in EMV 3DS 2.3.1 and later<br>> <br>> 80 - PS-specific value (dependent on the payment scheme type)<br>> <br>> 81 - PS-specific value (dependent on the payment scheme type) | getShipIndicator(): ?string | setShipIndicator(?string shipIndicator): void |
| `deliveryTimeframe` | [`?string(DeliveryTimeframeEnum)`](../../doc/models/delivery-timeframe-enum.md) | Optional | Indicates the merchandise delivery timeframe.<br><br>> 01 - Electronic Delivery<br>> <br>> 02 - Same day shipping<br>> <br>> 03 - Overnight shipping<br>> <br>> 04 - Two-day or more shipping | getDeliveryTimeframe(): ?string | setDeliveryTimeframe(?string deliveryTimeframe): void |
| `deliveryEmailAddress` | `?string` | Optional | For electronic delivery, the email address to which the merchandise was delivered. | getDeliveryEmailAddress(): ?string | setDeliveryEmailAddress(?string deliveryEmailAddress): void |
| `reorderItemsInd` | [`?string(ReorderItemsIndEnum)`](../../doc/models/reorder-items-ind-enum.md) | Optional | Indicates whether the cardholder is reordering previously purchased merchandise.<br><br>> 01 - First time ordered<br>> <br>> 02 - Reordered | getReorderItemsInd(): ?string | setReorderItemsInd(?string reorderItemsInd): void |
| `preOrderPurchaseInd` | [`?string(PreOrderPurchaseIndEnum)`](../../doc/models/pre-order-purchase-ind-enum.md) | Optional | Indicates whether Cardholder is placing an order for merchandise with a future availability or release date.<br><br>> 01 - Merchandise available<br>> <br>> 02 - Future availability | getPreOrderPurchaseInd(): ?string | setPreOrderPurchaseInd(?string preOrderPurchaseInd): void |
| `preOrderDate` | `?string` | Optional | For a pre-ordered purchase, the expected date that the merchandise will be available. Date format must be YYYYMMDD. | getPreOrderDate(): ?string | setPreOrderDate(?string preOrderDate): void |
| `giftCardAmount` | `?int` | Optional | For prepaid or gift card purchase, the purchase amount total of prepaid or gift card(s) in major units (for example, USD 123.45 is 123). | getGiftCardAmount(): ?int | setGiftCardAmount(?int giftCardAmount): void |
| `giftCardCurr` | `?string` | Optional | For prepaid or gift card purchase, the currency code of the card as defined in ISO 4217 except 955 - 964 and 999. | getGiftCardCurr(): ?string | setGiftCardCurr(?string giftCardCurr): void |
| `giftCardCount` | `?int` | Optional | For prepaid or gift card purchase, total count of individual prepaid or gift cards/codes purchased.<br><br>**Constraints**: `>= 0`, `<= 99` | getGiftCardCount(): ?int | setGiftCardCount(?int giftCardCount): void |
| `transChar` | [`?(string(TransCharEnum)[])`](../../doc/models/trans-char-enum.md) | Optional | Available starting in EMV 3DS 2.3.1.1.  Indicates to the ACS specific transactions identified by the Merchant.<br><br>> 01 - Cryptocurrency transaction<br>> <br>> 02 - NFT transaction<br><br>**Constraints**: *Minimum Items*: `1`, *Maximum Items*: `2` | getTransChar(): ?array | setTransChar(?array transChar): void |

## Example (as JSON)

```json
{
  "ship_indicator": "01",
  "delivery_timeframe": "02",
  "delivery_email_address": "fortis@example.com",
  "reorder_items_ind": "01",
  "pre_order_purchase_ind": "01"
}
```

