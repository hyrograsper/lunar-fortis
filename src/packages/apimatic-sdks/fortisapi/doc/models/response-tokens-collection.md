
# Response Tokens Collection

## Structure

`ResponseTokensCollection`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `type` | [`?string(Type89Enum)`](../../doc/models/type-89-enum.md) | Optional | Resource Type<br><br>**Default**: `Type89Enum::TOKENSCOLLECTION` | getType(): ?string | setType(?string type): void |
| `list` | [`?(List15[])`](../../doc/models/list-15.md) | Optional | Resource Members | getList(): ?array | setList(?array list): void |
| `links` | [`?Links`](../../doc/models/links.md) | Optional | Pagination page links | getLinks(): ?Links | setLinks(?Links links): void |
| `pagination` | [`?Pagination`](../../doc/models/pagination.md) | Optional | Pagination info | getPagination(): ?Pagination | setPagination(?Pagination pagination): void |
| `sort` | [`?Sort`](../../doc/models/sort.md) | Optional | Sort information used on the results | getSort(): ?Sort | setSort(?Sort sort): void |

## Example (as JSON)

```json
{
  "type": "TokensCollection",
  "list": [
    {
      "account_holder_name": "account_holder_name8",
      "account_vault_api_id": "account_vault_api_id6",
      "token_api_id": "token_api_id2",
      "accountvault_c1": "accountvault_c16",
      "accountvault_c2": "accountvault_c20"
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

