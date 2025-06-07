
# Response Transaction Ach Retrys Collection

## Structure

`ResponseTransactionAchRetrysCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type98Enum)`](../../doc/models/type-98-enum.md) | Optional | Resource Type<br><br>**Default**: `Type98Enum::TRANSACTIONACHRETRYSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List16[])`](../../doc/models/list-16.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "TransactionAchRetrysCollection",
  "list": [
    {
      "rejected_transaction_id": "rejected_transaction_id6",
      "return_fee": 150,
      "id": "id2",
      "retry_transaction_id": "retry_transaction_id8",
      "return_fee_transaction_id": "return_fee_transaction_id6"
    },
    {
      "rejected_transaction_id": "rejected_transaction_id6",
      "return_fee": 150,
      "id": "id2",
      "retry_transaction_id": "retry_transaction_id8",
      "return_fee_transaction_id": "return_fee_transaction_id6"
    },
    {
      "rejected_transaction_id": "rejected_transaction_id6",
      "return_fee": 150,
      "id": "id2",
      "retry_transaction_id": "retry_transaction_id8",
      "return_fee_transaction_id": "return_fee_transaction_id6"
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

