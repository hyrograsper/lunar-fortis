
# Three Ds Requestor Dec Req Ind Enum

Indicates whether the 3DS Requestor requests the ACS to utilise Decoupled Authentication and agrees to utilise Decoupled Authentication if the ACS confirms its use.

Value "F" and "B" are only valid for EMV 3DS 2.3.1 or later.

The field is optional and if value is not present, the expected action is for the ACS to interpret as "N".
Available for supporting EMV 3DS 2.2.0 and later versions.

> Y - Decoupled Authentication is supported and preferred if challenge is necessary.
> 
> N - Do not use Decoupled Authentication.
> 
> F - Decoupled Authentication is supported and is to be used only as a fallback challenge method if a challenge is necessary (Transaction Status = D in RReq). Available in EMV 3DS 2.3.1 and later.
> 
> B - Decoupled Authentication is supported and can be used as a primary or fallback challenge method if a challenge is necessary (Transaction Status = D in either ARes or RReq). Available in EMV 3DS 2.3.1 and later.

## Enumeration

`ThreeDsRequestorDecReqIndEnum`

## Fields

| Name |
|  --- |
| `Y` |
| `N` |
| `F` |
| `B` |

