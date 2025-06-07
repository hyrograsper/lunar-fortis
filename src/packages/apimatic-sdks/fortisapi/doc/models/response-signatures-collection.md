
# Response Signatures Collection

## Structure

`ResponseSignaturesCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type68Enum)`](../../doc/models/type-68-enum.md) | Optional | Resource Type<br><br>**Default**: `Type68Enum::SIGNATURESCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List11[])`](../../doc/models/list-11.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "SignaturesCollection",
  "list": [
    {
      "signature": "signature0",
      "resource": "Recurring",
      "resource_id": "resource_id8",
      "id": "id2",
      "created_ts": 56
    },
    {
      "signature": "signature0",
      "resource": "Recurring",
      "resource_id": "resource_id8",
      "id": "id2",
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

