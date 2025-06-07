
# Response Location Infos Collection

## Structure

`ResponseLocationInfosCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type39Enum)`](../../doc/models/type-39-enum.md) | Optional | Resource Type<br><br>**Default**: `Type39Enum::LOCATIONINFOSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List5[])`](../../doc/models/list-5.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "LocationInfosCollection",
  "list": [
    {
      "id": "id2",
      "created_ts": 56,
      "modified_ts": 124,
      "account_number": "account_number2",
      "address": {
        "city": "city6",
        "state": "state2",
        "postal_code": "postal_code8",
        "country": "US",
        "street": "street6"
      }
    },
    {
      "id": "id2",
      "created_ts": 56,
      "modified_ts": 124,
      "account_number": "account_number2",
      "address": {
        "city": "city6",
        "state": "state2",
        "postal_code": "postal_code8",
        "country": "US",
        "street": "street6"
      }
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

