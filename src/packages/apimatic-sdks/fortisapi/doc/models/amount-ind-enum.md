
# Amount Ind Enum

Part of the indication whether the recurring or installment payment has a fixed or variable amount.

Starting from EMV 3DS 2.3.1:
This field is required if three_ds_requestor.three_ds_requestor_authentication_ind = 02 or 03.
This field is required if three_ri_ind= 01 or 02.

> 01 - Fixed Purchase Amount
> 
> 02 - Variable Purchase Amount
> 
> 03 through 79 - Reserved for EMVCo future use (values invalid until defined by EMVCo)
> 
> 80 through 99 - PS-specific value (dependent on the payment scheme type)

## Enumeration

`AmountIndEnum`

## Fields

| Name |
|  --- |
| `ENUM_01` |
| `ENUM_02` |
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

