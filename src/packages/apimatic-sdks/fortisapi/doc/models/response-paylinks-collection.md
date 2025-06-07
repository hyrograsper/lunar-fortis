
# Response Paylinks Collection

## Structure

`ResponsePaylinksCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type49Enum)`](../../doc/models/type-49-enum.md) | Optional | Resource Type<br><br>**Default**: `Type49Enum::PAYLINKSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List8[])`](../../doc/models/list-8.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "PaylinksCollection",
  "list": [
    {
      "location_id": "location_id6",
      "cc_product_transaction_id": "cc_product_transaction_id6",
      "email": "email4",
      "amount_due": 138,
      "location_api_id": "location_api_id2"
    },
    {
      "location_id": "location_id6",
      "cc_product_transaction_id": "cc_product_transaction_id6",
      "email": "email4",
      "amount_due": 138,
      "location_api_id": "location_api_id2"
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

