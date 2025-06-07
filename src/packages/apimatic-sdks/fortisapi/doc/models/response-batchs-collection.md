
# Response Batchs Collection

## Structure

`ResponseBatchsCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type1Enum)`](../../doc/models/type-1-enum.md) | Optional | Resource Type<br><br>**Default**: `Type1Enum::BATCHSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(MList[])`](../../doc/models/m-list.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "BatchsCollection",
  "list": [
    {
      "id": "id2",
      "created_ts": 56,
      "product_transaction_id": "product_transaction_id6",
      "processing_status_id": 5,
      "batch_num": 224
    },
    {
      "id": "id2",
      "created_ts": 56,
      "product_transaction_id": "product_transaction_id6",
      "processing_status_id": 5,
      "batch_num": 224
    },
    {
      "id": "id2",
      "created_ts": 56,
      "product_transaction_id": "product_transaction_id6",
      "processing_status_id": 5,
      "batch_num": 224
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

