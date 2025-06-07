
# Account Type 16 Enum

Required for ACH transactions if account_vault_id is not provided.

> For ACH, allowed values are 'checking' or 'savings'. For CC, this field is read only. The system will identify card type and generate a value for this field automatically. possible values are: visa, mc, disc, amex, jcb, diners, and debit.

## Enumeration

`AccountType16Enum`

## Fields

| Name |
|  --- |
| `CHECKING` |
| `SAVINGS` |

## Example

```
checking
```

