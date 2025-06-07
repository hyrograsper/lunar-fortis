
# Device Binding Status Enum

Enables the communication of Device Binding Status between the ACS, the DS and the 3DS Requestor. For bound devices (value = 11–14), Device Binding Status also conveys the type of binding that was performed.

> 01 - Device is not bound by Cardholder
> 
> 02 - Not eligible as determined by Issuer
> 
> 03 - Pending confirmation by Cardholder
> 
> 04 - Cardholder rejected
> 
> 05 - Device Binding Status unknown, unavailable, or does not apply
> 
> 06 through 10 - Reserved for EMVCo future use (values invalid until defined by EMVCo)
> 
> 11 - Device is bound by Cardholder (device is bound using hardware / SIM internal to the Consumer Device. For instance, keys stored in a secure element on the device)
> 
> 12 - Device is bound by Cardholder (device is bound using hardware external to the Consumer Device. For example, an external FIDO Authenticator
> 
> 13 - Device is bound by Cardholder (device is bound using data that includes dynamically generated data and could include a unique device ID)
> 
> 14 - Device is bound by Cardholder (device is bound using static device data that has been obtained from the Consumer Device
> 
> 15 - Device is bound by Cardholder (Other method)
> 
> 16 through 79 - Reserved for EMVCo future use (values invalid until defined by EMVCo)
> 
> 80 through 99 - Reserved for DS use

## Enumeration

`DeviceBindingStatusEnum`

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
| `ENUM_15` |
| `ENUM_16` |
| `ENUM_17` |
| `ENUM_18` |
| `ENUM_19` |
| `ENUM_20` |
| `ENUM_21` |
| `ENUM_22` |
| `ENUM_23` |
| `ENUM_24` |
| `ENUM_25` |
| `ENUM_26` |
| `ENUM_27` |
| `ENUM_28` |
| `ENUM_29` |
| `ENUM_30` |
| `ENUM_31` |
| `ENUM_32` |
| `ENUM_33` |
| `ENUM_34` |
| `ENUM_35` |
| `ENUM_36` |
| `ENUM_37` |
| `ENUM_38` |
| `ENUM_39` |
| `ENUM_40` |
| `ENUM_41` |
| `ENUM_42` |
| `ENUM_43` |
| `ENUM_44` |
| `ENUM_45` |
| `ENUM_46` |
| `ENUM_47` |
| `ENUM_48` |
| `ENUM_49` |
| `ENUM_50` |
| `ENUM_51` |
| `ENUM_52` |
| `ENUM_53` |
| `ENUM_54` |
| `ENUM_55` |
| `ENUM_56` |
| `ENUM_57` |
| `ENUM_58` |
| `ENUM_59` |
| `ENUM_60` |
| `ENUM_61` |
| `ENUM_62` |
| `ENUM_63` |
| `ENUM_64` |
| `ENUM_65` |
| `ENUM_66` |
| `ENUM_67` |
| `ENUM_68` |
| `ENUM_69` |
| `ENUM_70` |
| `ENUM_71` |
| `ENUM_72` |
| `ENUM_73` |
| `ENUM_74` |
| `ENUM_75` |
| `ENUM_76` |
| `ENUM_77` |
| `ENUM_78` |
| `ENUM_79` |
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

