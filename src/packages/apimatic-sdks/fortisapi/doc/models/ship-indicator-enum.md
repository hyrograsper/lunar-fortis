
# Ship Indicator Enum

Indicates shipping method chosen for the transaction. Merchants must choose the Shipping Indicator code that most accurately describes the cardholder's specific transaction. If one or more items are included in the sale, use the Shipping Indicator code for the physical goods, or if all digital goods, use the code that describes the most expensive item.

> 01 - Ship to cardholder's billing address
> 
> 02 - Ship to another verified address on file with merchant
> 
> 03 - Ship to address that is different than the cardholder's billing address
> 
> 04 - "Ship to Store" / Pick-up at local store (Store address shall be populated in shipping address fields)
> 
> 05 - Digital goods (includes online services, electronic gift cards and redemption codes)
> 
> 06 - Travel and Event tickets, not shipped
> 
> 07 - Other (for example, Gaming, digital services not shipped, e-media subscriptions, etc.)
> 
> 08 - Pick-up and go delivery. Availble in EMV 3DS 2.3.1 and later
> 
> 09 - Locker delivery (or other automated pick-up). Availble in EMV 3DS 2.3.1 and later
> 
> 80 - PS-specific value (dependent on the payment scheme type)
> 
> 81 - PS-specific value (dependent on the payment scheme type)

## Enumeration

`ShipIndicatorEnum`

## Fields

| Name |
|  --- |
| `ENUM_01` |
| `ENUM_02` |
| `ENUM_03` |
| `ENUM_04` |
| `ENUM_05` |
| `ENUM_07` |
| `ENUM_08` |
| `ENUM_09` |
| `ENUM_80` |
| `ENUM_81` |

## Example

```
01
```

