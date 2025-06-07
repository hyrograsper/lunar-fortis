
# Three Ri Ind Enum

Indicates the type of 3RI request. This data element provides additional information to the ACS to determine the best approach for handling a 3RI request.

Values 06 through 12 are accepted as well if 3DS Server initiates authentication with EMV 3DS 2.2.0 version or greater (required protocol version can be set in the preferred_protocol_version field).

Starting from EMV 3DS 2.3.1:
Required if device_channel = 03 and message_category = 01 or 02.
Values 13 and 14 can be used.

> 01 - Recurring transaction
> 
> 02 - Installment transaction
> 
> 03 - Add card
> 
> 04 - Maintain card information
> 
> 05 - Account verification
> 
> 06 - Split/delayed shipment (EMV 3DS 2.2.0 version or greater)
> 
> 07 - Top-up (EMV 3DS 2.2.0 version or greater)
> 
> 08 - Mail order (EMV 3DS 2.2.0 version or greater)
> 
> 09 - Telephone order (EMV 3DS 2.2.0 version or greater)
> 
> 10 - Whitelist status check (EMV 3DS 2.2.0 version or greater)
> 
> 11 - Other payment (EMV 3DS 2.2.0 version or greater)
> 
> 12 - Billing agreement (EMV 3DS 2.2.0 version or greater)
> 
> 13 - Device Binding status check (EMV 3DS 2.3.1 version or greater)
> 
> 14 - Card Security Code status check (EMV 3DS 2.3.1 version or greater)
> 
> 80 through 99 - PS-specific values, regardless of protocol version

## Enumeration

`ThreeRiIndEnum`

## Fields

| Name |
|  --- |
| `ENUM_01` |
| `ENUM_02` |
| `ENUM_03` |
| `ENUM_04` |
| `ENUM_05` |
| `ENUM_06` |
| `ENUM_07` |
| `ENUM_08` |
| `ENUM_09` |
| `ENUM_10` |
| `ENUM_11` |
| `ENUM_12` |
| `ENUM_13` |
| `ENUM_14` |
| `ENUM_80` |
| `ENUM_81` |
| `ENUM_82` |
| `ENUM_83` |
| `ENUM_84` |
| `ENUM_85` |
| `ENUM_86` |
| `ENUM_87` |
| `ENUM_88` |
| `ENUM_89` |
| `ENUM_90` |
| `ENUM_91` |
| `ENUM_92` |
| `ENUM_93` |
| `ENUM_94` |
| `ENUM_95` |
| `ENUM_96` |
| `ENUM_97` |
| `ENUM_98` |
| `ENUM_99` |

