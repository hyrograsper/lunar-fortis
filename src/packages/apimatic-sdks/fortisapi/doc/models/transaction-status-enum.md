
# Transaction Status Enum

Indicates whether a transaction qualifies as an authenticated transaction.

> Y - Authentication / Account verification successful
> 
> N - Not authenticated / Account not verified; Transaction denied
> 
> U - Authentication / Account verification could not be performed; technical or other problem
> 
> C - In order to complete the authentication, a challenge is required
> 
> R - Authentication / Account verification Rejected. Issuer is rejecting authentication/verification and request that authorization not be attempted
> 
> A - Attempts processing performed; Not authenticated / verified, but a proof of attempt authentication / verification is provided
> 
> D - In order to complete the authentication, a challenge is required. Decoupled Authentication confirmed. (Only if the 3DS Server has initiated authentication with EMV 3DS 2.2.0 version or greater)
> 
> I - Informational Only; 3DS Requestor challenge preference acknowledged. (Only if the 3DS Server has initiated authentication with EMV 3DS 2.2.0 version or greater)

## Enumeration

`TransactionStatusEnum`

## Fields

| Name |
|  --- |
| `Y` |
| `N` |
| `U` |
| `C` |
| `R` |
| `A` |
| `D` |
| `I` |

## Example

```
C
```

