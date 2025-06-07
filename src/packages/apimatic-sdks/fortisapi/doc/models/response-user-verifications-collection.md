
# Response User Verifications Collection

## Structure

`ResponseUserVerificationsCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type119Enum)`](../../doc/models/type-119-enum.md) | Optional | Resource Type<br><br>**Default**: `Type119Enum::USERVERIFICATIONSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List18[])`](../../doc/models/list-18.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "UserVerificationsCollection",
  "list": [
    {
      "id": "id2",
      "user_id": "user_id0",
      "hash": "hash8",
      "created_ts": 56
    },
    {
      "id": "id2",
      "user_id": "user_id0",
      "hash": "hash8",
      "created_ts": 56
    },
    {
      "id": "id2",
      "user_id": "user_id0",
      "hash": "hash8",
      "created_ts": 56
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

