
# Response Transactions Collection

## Structure

`ResponseTransactionsCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type112Enum)`](../../doc/models/type-112-enum.md) | Optional | Resource Type<br><br>**Default**: `Type112Enum::TRANSACTIONSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List17[])`](../../doc/models/list-17.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "TransactionsCollection",
  "list": [
    {
      "additional_amounts": [
        {
          "type": "cashback",
          "amount": 6,
          "account_type": "cash_benefit",
          "currency": 154.64
        }
      ],
      "billing_address": {
        "city": "city2",
        "state": "state6",
        "postal_code": "postal_code0",
        "street": "street8",
        "phone": "phone2"
      },
      "checkin_date": "checkin_date4",
      "checkout_date": "checkout_date6",
      "clerk_number": "clerk_number6"
    },
    {
      "additional_amounts": [
        {
          "type": "cashback",
          "amount": 6,
          "account_type": "cash_benefit",
          "currency": 154.64
        }
      ],
      "billing_address": {
        "city": "city2",
        "state": "state6",
        "postal_code": "postal_code0",
        "street": "street8",
        "phone": "phone2"
      },
      "checkin_date": "checkin_date4",
      "checkout_date": "checkout_date6",
      "clerk_number": "clerk_number6"
    }
  ],
  "links": {
    "type": "Links",
    "first": "first0",
    "previous": "previous2",
    "next": "next2",
    "last": "last4"
  },
  "pagination": {
    "type": "Pagination",
    "total_count": 100,
    "page_count": 212,
    "page_number": 28,
    "page_size": 6
  },
  "sort": {
    "type": "Sorting",
    "fields": [
      {
        "field": "field2",
        "order": "asc"
      },
      {
        "field": "field2",
        "order": "asc"
      },
      {
        "field": "field2",
        "order": "asc"
      }
    ]
  }
}
```

