
# Preferred Protocol Version Enum

Specifies the preferred version of 3D Secure protocol to be utilized while executing 3D Secure authentication. 3DS Server initiates an authentication request with the preferred version and if this version is not supported by other 3D Secure components, it falls back to the next supported version(s) and continues authentication.
If the preferred version is enforced by setting  #enforcePreferredProtocolVersion flag, but this version is not supported by one of the 3D Secure components, 3DS Server does not initiate an authentication and provides corresponding error message to the customer.
If no value is provided, EMV 3DS 2.1.0 version will be used by default.

> 2.1.0 - prefer authentication with 2.1.0 version
> 
> 2.2.0 - prefer authentication with 2.2.0 version
> 
> 2.3.1 - prefer authentication with 2.3.1 version

## Enumeration

`PreferredProtocolVersionEnum`

## Fields

| Name |
|  --- |
| `ENUM_210` |
| `ENUM_220` |
| `ENUM_231` |

## Example

```
2.2.0
```

