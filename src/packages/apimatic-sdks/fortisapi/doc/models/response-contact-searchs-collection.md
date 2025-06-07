
# Response Contact Searchs Collection

## Structure

`ResponseContactSearchsCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type6Enum)`](../../doc/models/type-6-enum.md) | Optional | Resource Type<br><br>**Default**: `Type6Enum::CONTACTSEARCHSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List1[])`](../../doc/models/list-1.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "ContactSearchsCollection",
  "list": [
    {
      "location_id": "location_id6",
      "account_number": "account_number2",
      "contact_api_id": "contact_api_id2",
      "first_name": "first_name2",
      "last_name": "last_name0"
    },
    {
      "location_id": "location_id6",
      "account_number": "account_number2",
      "contact_api_id": "contact_api_id2",
      "first_name": "first_name2",
      "last_name": "last_name0"
    },
    {
      "location_id": "location_id6",
      "account_number": "account_number2",
      "contact_api_id": "contact_api_id2",
      "first_name": "first_name2",
      "last_name": "last_name0"
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

