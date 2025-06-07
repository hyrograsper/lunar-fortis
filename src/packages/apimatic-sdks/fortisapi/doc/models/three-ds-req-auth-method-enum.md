
# Three Ds Req Auth Method Enum

Mechanism used by the Cardholder to authenticate to the 3DS Requestor. "07" and "08" are accepted as well if 3DS Server initiates authentication with EMV 3DS 2.2.0 version or greater (required protocol version can be set in the preferred_protocol_version field)

> 01 - No 3DS Requestor authentication occurred (i.e. cardholder "logged in" as guest)
> 
> 02 - Login to the cardholder account at the 3DS Requestor system using 3DS Requestor's own credentials
> 
> 03 - Login to the cardholder account at the 3DS Requestor system using federated ID
> 
> 04 - Login to the cardholder account at the 3DS Requestor system using issuer credentials
> 
> 05 - Login to the cardholder account at the 3DS Requestor system using third-party authentication
> 
> 06 - Login to the cardholder account at the 3DS Requestor system using FIDO Authenticator
> 
> 07 - Login to the cardholder account at the 3DS Requestor system using FIDO Authenticator (FIDO assurance data signed) (EMV 3DS 2.2.0 version or greater)
> 
> 08 - SRC Assurance Data (EMV 3DS 2.2.0 version or greater)
> 
> 80 through 99 - can be used for PS-specific values, regardless of protocol version

## Enumeration

`ThreeDsReqAuthMethodEnum`

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

## Example

```
04
```

