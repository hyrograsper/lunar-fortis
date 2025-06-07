
# Response Terminals Collection

## Structure

`ResponseTerminalsCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type78Enum)`](../../doc/models/type-78-enum.md) | Optional | Resource Type<br><br>**Default**: `Type78Enum::TERMINALSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List13[])`](../../doc/models/list-13.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "TerminalsCollection",
  "list": [
    {
      "location_id": "location_id6",
      "default_product_transaction_id": "default_product_transaction_id8",
      "terminal_application_id": "terminal_application_id2",
      "terminal_cvm_id": "terminal_cvm_id8",
      "terminal_manufacturer_code": "4"
    },
    {
      "location_id": "location_id6",
      "default_product_transaction_id": "default_product_transaction_id8",
      "terminal_application_id": "terminal_application_id2",
      "terminal_cvm_id": "terminal_cvm_id8",
      "terminal_manufacturer_code": "4"
    },
    {
      "location_id": "location_id6",
      "default_product_transaction_id": "default_product_transaction_id8",
      "terminal_application_id": "terminal_application_id2",
      "terminal_cvm_id": "terminal_cvm_id8",
      "terminal_manufacturer_code": "4"
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

