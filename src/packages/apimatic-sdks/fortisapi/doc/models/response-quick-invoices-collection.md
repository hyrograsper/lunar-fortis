
# Response Quick Invoices Collection

## Structure

`ResponseQuickInvoicesCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type57Enum)`](../../doc/models/type-57-enum.md) | Optional | Resource Type<br><br>**Default**: `Type57Enum::QUICKINVOICESCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List9[])`](../../doc/models/list-9.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "QuickInvoicesCollection",
  "list": [
    {
      "location_id": "location_id6",
      "title": "title8",
      "cc_product_transaction_id": "cc_product_transaction_id6",
      "ach_product_transaction_id": "ach_product_transaction_id4",
      "due_date": "due_date0"
    },
    {
      "location_id": "location_id6",
      "title": "title8",
      "cc_product_transaction_id": "cc_product_transaction_id6",
      "ach_product_transaction_id": "ach_product_transaction_id4",
      "due_date": "due_date0"
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

