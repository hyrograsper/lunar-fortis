# Tickets

The tickets endpoint is used as a single use cc only token generation endpoint. If there is a need to store card numbers with cvv for a one time use, then this is the endpoint to perform that task.  Transactions run with the generated ticket_id can save card information for later with save_account.

```php
$ticketsController = $client->getTicketsController();
```

## Class Name

`TicketsController`

## Methods

* [Create a Ticket Record](../../doc/controllers/tickets.md#create-a-ticket-record)
* [List All Tickets Related](../../doc/controllers/tickets.md#list-all-tickets-related)
* [View Single Ticket Record](../../doc/controllers/tickets.md#view-single-ticket-record)


# Create a Ticket Record

```php
function createATicketRecord(V1TicketsRequest $body, ?array $expand = null): ResponseTicket
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `body` | [`V1TicketsRequest`](../../doc/models/v1-tickets-request.md) | Body, Required | - |
| `expand` | [`?(string(Expand41Enum)[])`](../../doc/models/expand-41-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |

## Response Type

[`ResponseTicket`](../../doc/models/response-ticket.md)

## Example Usage

```php
$body = V1TicketsRequestBuilder::init(
    '0722',
    '545454545454545'
)
    ->accountHolderName('John Smith')
    ->contactId('11e95f8ec39de8fbdb0a4f1a')
    ->build();

$result = $ticketsController->createATicketRecord($body);
```

## Example Response *(as JSON)*

```json
{
  "type": "Ticket",
  "data": {
    "account_holder_name": "John Smith",
    "exp_date": "0722",
    "cvv": null,
    "account_number": "545454545454545",
    "billing_address": {
      "postal_code": "48375"
    },
    "contact_id": "11e95f8ec39de8fbdb0a4f1a",
    "location_api_id": null,
    "id": "11e95f8ec39de8fbdb0a4f1a",
    "created_ts": 1422040992,
    "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |
| 412 | Precondition Failed | [`Response412Exception`](../../doc/models/response-412-exception.md) |


# List All Tickets Related

```php
function listAllTicketsRelated(
    ?Page $page = null,
    ?array $order = null,
    ?array $filterBy = null,
    ?array $expand = null,
    ?string $format = null,
    ?string $typeahead = null,
    ?array $fields = null
): ResponseTicketsCollection
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `page` | [`?Page`](../../doc/models/page.md) | Query, Optional | Use this field to specify paginate your results, by using page size and number. You can use one of the following methods:<br><br>> /endpoint?page={ "number": 1, "size": 50 }<br>> <br>> /endpoint?page[number]=1&page[size]=50 |
| `order` | [`?(Order20[])`](../../doc/models/order-20.md) | Query, Optional | Criteria used in query string parameters to order results.  Most fields from the endpoint results can be used as a `key`.  Unsupported fields or operators will return a `412`.  Must be encoded, or use syntax that does not require encoding.<br><br>> /endpoint?order[0][key]=created_ts&order[0][operator]=asc<br>> <br>> /endpoint?order=[{ "key": "created_ts", "operator": "asc"}]<br>> <br>> /endpoint?order=[{ "key": "balance", "operator": "desc"},{ "key": "created_ts", "operator": "asc"}]<br><br>**Constraints**: *Minimum Items*: `1` |
| `filterBy` | [`?(FilterBy[])`](../../doc/models/filter-by.md) | Query, Optional | Filter criteria that can be used in query string parameters.  Most fields from the endpoint results can be used as a `key`.  Unsupported fields or operators will return a `412`. Must be encoded, or use syntax that does not require encoding.<br><br>> ?filter_by[0][key]=first_name&filter_by[0][operator]==&filter_by[0][value]=Steve<br>> <br>> /endpoint?filter_by=[{ "key": "first_name", "operator": "=", "value": "Fred" }]<br>> <br>> /endpoint?filter_by=[{ "key": "account_type", "operator": "=", "value": "VISA" }]<br>> <br>> /endpoint?filter_by=[{ "key": "created_ts", "operator": ">=", "value": "946702799" }, { "key": "created_ts", "operator": "<=", value: "1695061891" }]<br>> <br>> /endpoint?filter_by=[{ "key": "last_name", "operator": "IN", "value": "Williams,Brown,Allman" }]<br><br>**Constraints**: *Minimum Items*: `1` |
| `expand` | [`?(string(Expand41Enum)[])`](../../doc/models/expand-41-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |
| `format` | [`?string(Format1Enum)`](../../doc/models/format-1-enum.md) | Query, Optional | Reporting format, valid values: csv, tsv |
| `typeahead` | `?string` | Query, Optional | You can use any `field_name` from this endpoint results to order the list using the value provided as filter for the same `field_name`. It will be ordered using the following rules: 1) Exact match, 2) Starts with, 3) Contains.<br><br>> /endpoint?filter={ "field_name": "Value" }&_typeahead=field_name |
| `fields` | [`?(string(Field48Enum)[])`](../../doc/models/field-48-enum.md) | Query, Optional | You can use any `field_name` from this endpoint results to filter the list of fields returned on the response. |

## Response Type

[`ResponseTicketsCollection`](../../doc/models/response-tickets-collection.md)

## Example Usage

```php
$page = PageBuilder::init()
    ->number(1)
    ->size(50)
    ->build();

$order = [
    Order20Builder::init(
        'first_name',
        OperatorEnum::ASC
    )->build()
];

$filterBy = [
    FilterByBuilder::init(
        'first_name',
        Operator1Enum::ENUM_1,
        'Fred'
    )->build()
];

$result = $ticketsController->listAllTicketsRelated(
    $page,
    $order,
    $filterBy
);
```

## Example Response *(as JSON)*

```json
{
  "type": "TicketsCollection",
  "list": [
    {
      "account_holder_name": "John Smith",
      "exp_date": "0722",
      "cvv": null,
      "account_number": "545454545454545",
      "billing_address": {
        "postal_code": "48375"
      },
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "location_api_id": null,
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
    }
  ],
  "links": {
    "type": "Links",
    "first": "/v1/endpoint?page[size]=10&page[number]=1",
    "previous": "/v1/endpoint?page[size]=10&page[number]=5",
    "next": "/v1/endpoint?page[size]=10&page[number]=7",
    "last": "/v1/endpoint?page[size]=10&page[number]=42"
  },
  "pagination": {
    "type": "Pagination",
    "total_count": 423,
    "page_count": 42,
    "page_number": 6,
    "page_size": 10
  },
  "sort": {
    "type": "Sorting",
    "fields": [
      {
        "field": "last_name",
        "order": "asc"
      }
    ]
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |


# View Single Ticket Record

```php
function viewSingleTicketRecord(string $ticketId, ?array $expand = null, ?array $fields = null): ResponseTicket
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `ticketId` | `string` | Template, Required | A unique, system-generated identifier for the Ticket.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` |
| `expand` | [`?(string(Expand41Enum)[])`](../../doc/models/expand-41-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |
| `fields` | [`?(string(Field48Enum)[])`](../../doc/models/field-48-enum.md) | Query, Optional | You can use any `field_name` from this endpoint results to filter the list of fields returned on the response. |

## Response Type

[`ResponseTicket`](../../doc/models/response-ticket.md)

## Example Usage

```php
$ticketId = '11e95f8ec39de8fbdb0a4f1a';

$result = $ticketsController->viewSingleTicketRecord($ticketId);
```

## Example Response *(as JSON)*

```json
{
  "type": "Ticket",
  "data": {
    "account_holder_name": "John Smith",
    "exp_date": "0722",
    "cvv": null,
    "account_number": "545454545454545",
    "billing_address": {
      "postal_code": "48375"
    },
    "contact_id": "11e95f8ec39de8fbdb0a4f1a",
    "location_api_id": null,
    "id": "11e95f8ec39de8fbdb0a4f1a",
    "created_ts": 1422040992,
    "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |

