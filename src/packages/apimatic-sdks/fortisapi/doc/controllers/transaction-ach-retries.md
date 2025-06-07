# Transaction ACH Retries

```php
$transactionACHRetriesController = $client->getTransactionACHRetriesController();
```

## Class Name

`TransactionACHRetriesController`

## Methods

* [View Single Transaction ACH Retry Record](../../doc/controllers/transaction-ach-retries.md#view-single-transaction-ach-retry-record)
* [Create a Transaction ACH Retry](../../doc/controllers/transaction-ach-retries.md#create-a-transaction-ach-retry)
* [List All Transaction ACH Retries Related](../../doc/controllers/transaction-ach-retries.md#list-all-transaction-ach-retries-related)


# View Single Transaction ACH Retry Record

```php
function viewSingleTransactionACHRetryRecord(
    string $transactionAchRetryId,
    ?array $expand = null,
    ?array $fields = null
): ResponseTransactionAchRetry
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `transactionAchRetryId` | `string` | Template, Required | Transaction ACH Retry ID.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` |
| `expand` | [`?(string(Expand54Enum)[])`](../../doc/models/expand-54-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |
| `fields` | [`?(string(Field52Enum)[])`](../../doc/models/field-52-enum.md) | Query, Optional | You can use any `field_name` from this endpoint results to filter the list of fields returned on the response. |

## Response Type

[`ResponseTransactionAchRetry`](../../doc/models/response-transaction-ach-retry.md)

## Example Usage

```php
$transactionAchRetryId = '11e95f8ec39de8fbdb0a4f1a';

$result = $transactionACHRetriesController->viewSingleTransactionACHRetryRecord($transactionAchRetryId);
```

## Example Response *(as JSON)*

```json
{
  "type": "TransactionAchRetry",
  "data": {
    "rejected_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
    "id": "11e95f8ec39de8fbdb0a4f1a",
    "retry_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
    "return_fee_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
    "created_ts": 1422040992,
    "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
    "rejected_transaction": {
      "additional_amounts": [
        {
          "type": "cashback",
          "amount": 10,
          "account_type": "credit",
          "currency": 840
        }
      ],
      "billing_address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "phone": "3339998822",
        "country": "USA"
      },
      "checkin_date": "2021-12-01",
      "checkout_date": "2021-12-01",
      "clerk_number": "AE1234",
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "custom_data": {},
      "customer_id": "customerid",
      "description": "some description",
      "identity_verification": {
        "dl_state": "MI",
        "dl_number": "1235567",
        "dob_year": "1980"
      },
      "iias_ind": 1,
      "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "installment": true,
      "installment_number": 1,
      "installment_count": 1,
      "location_api_id": "location-api-id-florida-2",
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "advance_deposit": false,
      "no_show": false,
      "notification_email_address": "johnsmith@smiths.com",
      "order_number": "433659378839",
      "po_number": "555555553123",
      "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
      "recurring": false,
      "recurring_number": 1,
      "room_num": "303",
      "room_rate": 95,
      "save_account": false,
      "save_account_title": "John Account",
      "subtotal_amount": 599,
      "surcharge_amount": 100,
      "tags": [
        "Walk-in Customer"
      ],
      "tax": 0,
      "tip_amount": 0,
      "transaction_amount": 0,
      "secondary_amount": 0,
      "transaction_api_id": "transaction-payment-abcd123",
      "transaction_c1": "custom-data-1",
      "transaction_c2": "custom-data-2",
      "transaction_c3": "custom-data-3",
      "bank_funded_only_override": false,
      "allow_partial_authorization_override": false,
      "auto_decline_cvv_override": false,
      "auto_decline_street_override": false,
      "auto_decline_zip_override": false,
      "ebt_type": "food_stamp",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_holder_name": "smith",
      "account_type": "checking",
      "token_id": "11e95f8ec39de8fbdb0a4f1a",
      "ach_identifier": "P",
      "ach_sec_code": "C21",
      "auth_amount": 1,
      "auth_code": "BR349K",
      "avs": "BAD",
      "avs_enhanced": "N",
      "cardholder_present": true,
      "card_present": true,
      "check_number": "8520748520963",
      "customer_ip": "192.168.0.10",
      "cvv_response": "N",
      "entry_mode_id": "C",
      "emv_receipt_data": {
        "AID": "a0000000042203",
        "APPLAB": "US Maestro",
        "APPN": "US Maestro",
        "CVM": "Pin Verified",
        "TSI": "e800",
        "TVR": "0800008000"
      },
      "first_six": "545454",
      "last_four": "5454",
      "payment_method": "cc",
      "terminal_serial_number": "1234567890",
      "transaction_settlement_status": null,
      "charge_back_date": "2021-12-01",
      "is_recurring": true,
      "notification_email_sent": true,
      "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
      "reason_code_id": 1000,
      "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
      "settle_date": "2021-12-01",
      "status_code": 101,
      "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
      "verbiage": "APPROVED",
      "voucher_number": "1234",
      "void_date": "2021-12-01",
      "batch": "2",
      "terms_agree": true,
      "response_message": null,
      "return_date": "2021-12-01",
      "trx_source_id": 8,
      "routing_number": "051904524",
      "trx_source_code": 8,
      "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
      "is_accountvault": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "effective_date": "2021-12-01",
      "is_token": true,
      "installment_total": 1,
      "installment_counter": 1,
      "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
      "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "retry_transaction": {
      "additional_amounts": [
        {
          "type": "cashback",
          "amount": 10,
          "account_type": "credit",
          "currency": 840
        }
      ],
      "billing_address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "phone": "3339998822",
        "country": "USA"
      },
      "checkin_date": "2021-12-01",
      "checkout_date": "2021-12-01",
      "clerk_number": "AE1234",
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "custom_data": {},
      "customer_id": "customerid",
      "description": "some description",
      "identity_verification": {
        "dl_state": "MI",
        "dl_number": "1235567",
        "dob_year": "1980"
      },
      "iias_ind": 1,
      "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "installment": true,
      "installment_number": 1,
      "installment_count": 1,
      "location_api_id": "location-api-id-florida-2",
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "advance_deposit": false,
      "no_show": false,
      "notification_email_address": "johnsmith@smiths.com",
      "order_number": "433659378839",
      "po_number": "555555553123",
      "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
      "recurring": false,
      "recurring_number": 1,
      "room_num": "303",
      "room_rate": 95,
      "save_account": false,
      "save_account_title": "John Account",
      "subtotal_amount": 599,
      "surcharge_amount": 100,
      "tags": [
        "Walk-in Customer"
      ],
      "tax": 0,
      "tip_amount": 0,
      "transaction_amount": 0,
      "secondary_amount": 0,
      "transaction_api_id": "transaction-payment-abcd123",
      "transaction_c1": "custom-data-1",
      "transaction_c2": "custom-data-2",
      "transaction_c3": "custom-data-3",
      "bank_funded_only_override": false,
      "allow_partial_authorization_override": false,
      "auto_decline_cvv_override": false,
      "auto_decline_street_override": false,
      "auto_decline_zip_override": false,
      "ebt_type": "food_stamp",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_holder_name": "smith",
      "account_type": "checking",
      "token_id": "11e95f8ec39de8fbdb0a4f1a",
      "ach_identifier": "P",
      "ach_sec_code": "C21",
      "auth_amount": 1,
      "auth_code": "BR349K",
      "avs": "BAD",
      "avs_enhanced": "N",
      "cardholder_present": true,
      "card_present": true,
      "check_number": "8520748520963",
      "customer_ip": "192.168.0.10",
      "cvv_response": "N",
      "entry_mode_id": "C",
      "emv_receipt_data": {
        "AID": "a0000000042203",
        "APPLAB": "US Maestro",
        "APPN": "US Maestro",
        "CVM": "Pin Verified",
        "TSI": "e800",
        "TVR": "0800008000"
      },
      "first_six": "545454",
      "last_four": "5454",
      "payment_method": "cc",
      "terminal_serial_number": "1234567890",
      "transaction_settlement_status": null,
      "charge_back_date": "2021-12-01",
      "is_recurring": true,
      "notification_email_sent": true,
      "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
      "reason_code_id": 1000,
      "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
      "settle_date": "2021-12-01",
      "status_code": 101,
      "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
      "verbiage": "APPROVED",
      "voucher_number": "1234",
      "void_date": "2021-12-01",
      "batch": "2",
      "terms_agree": true,
      "response_message": null,
      "return_date": "2021-12-01",
      "trx_source_id": 8,
      "routing_number": "051904524",
      "trx_source_code": 8,
      "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
      "is_accountvault": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "effective_date": "2021-12-01",
      "is_token": true,
      "installment_total": 1,
      "installment_counter": 1,
      "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
      "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "return_fee_transaction": {
      "additional_amounts": [
        {
          "type": "cashback",
          "amount": 10,
          "account_type": "credit",
          "currency": 840
        }
      ],
      "billing_address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "phone": "3339998822",
        "country": "USA"
      },
      "checkin_date": "2021-12-01",
      "checkout_date": "2021-12-01",
      "clerk_number": "AE1234",
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "custom_data": {},
      "customer_id": "customerid",
      "description": "some description",
      "identity_verification": {
        "dl_state": "MI",
        "dl_number": "1235567",
        "dob_year": "1980"
      },
      "iias_ind": 1,
      "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "installment": true,
      "installment_number": 1,
      "installment_count": 1,
      "location_api_id": "location-api-id-florida-2",
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "advance_deposit": false,
      "no_show": false,
      "notification_email_address": "johnsmith@smiths.com",
      "order_number": "433659378839",
      "po_number": "555555553123",
      "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
      "recurring": false,
      "recurring_number": 1,
      "room_num": "303",
      "room_rate": 95,
      "save_account": false,
      "save_account_title": "John Account",
      "subtotal_amount": 599,
      "surcharge_amount": 100,
      "tags": [
        "Walk-in Customer"
      ],
      "tax": 0,
      "tip_amount": 0,
      "transaction_amount": 0,
      "secondary_amount": 0,
      "transaction_api_id": "transaction-payment-abcd123",
      "transaction_c1": "custom-data-1",
      "transaction_c2": "custom-data-2",
      "transaction_c3": "custom-data-3",
      "bank_funded_only_override": false,
      "allow_partial_authorization_override": false,
      "auto_decline_cvv_override": false,
      "auto_decline_street_override": false,
      "auto_decline_zip_override": false,
      "ebt_type": "food_stamp",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_holder_name": "smith",
      "account_type": "checking",
      "token_id": "11e95f8ec39de8fbdb0a4f1a",
      "ach_identifier": "P",
      "ach_sec_code": "C21",
      "auth_amount": 1,
      "auth_code": "BR349K",
      "avs": "BAD",
      "avs_enhanced": "N",
      "cardholder_present": true,
      "card_present": true,
      "check_number": "8520748520963",
      "customer_ip": "192.168.0.10",
      "cvv_response": "N",
      "entry_mode_id": "C",
      "emv_receipt_data": {
        "AID": "a0000000042203",
        "APPLAB": "US Maestro",
        "APPN": "US Maestro",
        "CVM": "Pin Verified",
        "TSI": "e800",
        "TVR": "0800008000"
      },
      "first_six": "545454",
      "last_four": "5454",
      "payment_method": "cc",
      "terminal_serial_number": "1234567890",
      "transaction_settlement_status": null,
      "charge_back_date": "2021-12-01",
      "is_recurring": true,
      "notification_email_sent": true,
      "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
      "reason_code_id": 1000,
      "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
      "settle_date": "2021-12-01",
      "status_code": 101,
      "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
      "verbiage": "APPROVED",
      "voucher_number": "1234",
      "void_date": "2021-12-01",
      "batch": "2",
      "terms_agree": true,
      "response_message": null,
      "return_date": "2021-12-01",
      "trx_source_id": 8,
      "routing_number": "051904524",
      "trx_source_code": 8,
      "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
      "is_accountvault": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "effective_date": "2021-12-01",
      "is_token": true,
      "installment_total": 1,
      "installment_counter": 1,
      "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
      "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "created_user": {
      "account_number": "5454545454545454",
      "branding_domain_url": "{branding_domain_url}",
      "cell_phone": "3339998822",
      "company_name": "Fortis Payment Systems, LLC",
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "date_of_birth": "2021-12-01",
      "domain_id": "11e95f8ec39de8fbdb0a4f1a",
      "email": "email@domain.com",
      "email_trx_receipt": true,
      "home_phone": "3339998822",
      "first_name": "John",
      "last_name": "Smith",
      "locale": "en-US",
      "office_phone": "3339998822",
      "office_ext_phone": "5",
      "primary_location_id": "11e95f8ec39de8fbdb0a4f1a",
      "requires_new_password": null,
      "terms_condition_code": "20220308",
      "tz": "America/New_York",
      "ui_prefs": {
        "entry_page": "dashboard",
        "page_size": 2,
        "report_export_type": "csv",
        "process_method": "virtual_terminal",
        "default_terminal": "11e95f8ec39de8fbdb0a4f1a"
      },
      "username": "{user_name}",
      "user_api_key": "234bas8dfn8238f923w2",
      "user_hash_key": null,
      "user_type_code": 100,
      "password": null,
      "zip": "48375",
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "status_code": 1,
      "api_only": false,
      "is_invitation": false,
      "address": {
        "city": "Novi",
        "state": "MI",
        "postal_code": "48375",
        "country": "US"
      },
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "status": true,
      "login_attempts": 0,
      "last_login_ts": 1422040992,
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "terms_accepted_ts": 1422040992,
      "terms_agree_ip": "192.168.0.10",
      "current_date_time": "2019-03-11T10:38:26-0700",
      "current_login": 1422040992,
      "log_api_response_body_ts": 1422040992
    },
    "changelogs": [
      {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "action": "CREATE",
        "model": "TransactionRequest",
        "model_id": "11ec829598f0d4008be9aba4",
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "changelog_details": [
          {
            "id": "11e95f8ec39de8fbdb0a4f1a",
            "changelog_id": "11e95f8ec39de8fbdb0a4f1a",
            "field": "next_run_ts",
            "old_value": "1643616000"
          }
        ],
        "user": {
          "id": "11e95f8ec39de8fbdb0a4f1a",
          "username": "email@domain.com",
          "first_name": "Bob",
          "last_name": "Fairview"
        }
      }
    ]
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |


# Create a Transaction ACH Retry

```php
function createATransactionACHRetry(
    V1TransactionAchRetriesRequest $body,
    ?array $expand = null
): ResponseTransactionAchRetry
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `body` | [`V1TransactionAchRetriesRequest`](../../doc/models/v1-transaction-ach-retries-request.md) | Body, Required | - |
| `expand` | [`?(string(Expand54Enum)[])`](../../doc/models/expand-54-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |

## Response Type

[`ResponseTransactionAchRetry`](../../doc/models/response-transaction-ach-retry.md)

## Example Usage

```php
$body = V1TransactionAchRetriesRequestBuilder::init(
    '11e95f8ec39de8fbdb0a4f1a'
)->build();

$result = $transactionACHRetriesController->createATransactionACHRetry($body);
```

## Example Response *(as JSON)*

```json
{
  "type": "TransactionAchRetry",
  "data": {
    "rejected_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
    "id": "11e95f8ec39de8fbdb0a4f1a",
    "retry_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
    "return_fee_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
    "created_ts": 1422040992,
    "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
    "rejected_transaction": {
      "additional_amounts": [
        {
          "type": "cashback",
          "amount": 10,
          "account_type": "credit",
          "currency": 840
        }
      ],
      "billing_address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "phone": "3339998822",
        "country": "USA"
      },
      "checkin_date": "2021-12-01",
      "checkout_date": "2021-12-01",
      "clerk_number": "AE1234",
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "custom_data": {},
      "customer_id": "customerid",
      "description": "some description",
      "identity_verification": {
        "dl_state": "MI",
        "dl_number": "1235567",
        "dob_year": "1980"
      },
      "iias_ind": 1,
      "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "installment": true,
      "installment_number": 1,
      "installment_count": 1,
      "location_api_id": "location-api-id-florida-2",
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "advance_deposit": false,
      "no_show": false,
      "notification_email_address": "johnsmith@smiths.com",
      "order_number": "433659378839",
      "po_number": "555555553123",
      "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
      "recurring": false,
      "recurring_number": 1,
      "room_num": "303",
      "room_rate": 95,
      "save_account": false,
      "save_account_title": "John Account",
      "subtotal_amount": 599,
      "surcharge_amount": 100,
      "tags": [
        "Walk-in Customer"
      ],
      "tax": 0,
      "tip_amount": 0,
      "transaction_amount": 0,
      "secondary_amount": 0,
      "transaction_api_id": "transaction-payment-abcd123",
      "transaction_c1": "custom-data-1",
      "transaction_c2": "custom-data-2",
      "transaction_c3": "custom-data-3",
      "bank_funded_only_override": false,
      "allow_partial_authorization_override": false,
      "auto_decline_cvv_override": false,
      "auto_decline_street_override": false,
      "auto_decline_zip_override": false,
      "ebt_type": "food_stamp",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_holder_name": "smith",
      "account_type": "checking",
      "token_id": "11e95f8ec39de8fbdb0a4f1a",
      "ach_identifier": "P",
      "ach_sec_code": "C21",
      "auth_amount": 1,
      "auth_code": "BR349K",
      "avs": "BAD",
      "avs_enhanced": "N",
      "cardholder_present": true,
      "card_present": true,
      "check_number": "8520748520963",
      "customer_ip": "192.168.0.10",
      "cvv_response": "N",
      "entry_mode_id": "C",
      "emv_receipt_data": {
        "AID": "a0000000042203",
        "APPLAB": "US Maestro",
        "APPN": "US Maestro",
        "CVM": "Pin Verified",
        "TSI": "e800",
        "TVR": "0800008000"
      },
      "first_six": "545454",
      "last_four": "5454",
      "payment_method": "cc",
      "terminal_serial_number": "1234567890",
      "transaction_settlement_status": null,
      "charge_back_date": "2021-12-01",
      "is_recurring": true,
      "notification_email_sent": true,
      "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
      "reason_code_id": 1000,
      "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
      "settle_date": "2021-12-01",
      "status_code": 101,
      "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
      "verbiage": "APPROVED",
      "voucher_number": "1234",
      "void_date": "2021-12-01",
      "batch": "2",
      "terms_agree": true,
      "response_message": null,
      "return_date": "2021-12-01",
      "trx_source_id": 8,
      "routing_number": "051904524",
      "trx_source_code": 8,
      "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
      "is_accountvault": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "effective_date": "2021-12-01",
      "is_token": true,
      "installment_total": 1,
      "installment_counter": 1,
      "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
      "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "retry_transaction": {
      "additional_amounts": [
        {
          "type": "cashback",
          "amount": 10,
          "account_type": "credit",
          "currency": 840
        }
      ],
      "billing_address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "phone": "3339998822",
        "country": "USA"
      },
      "checkin_date": "2021-12-01",
      "checkout_date": "2021-12-01",
      "clerk_number": "AE1234",
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "custom_data": {},
      "customer_id": "customerid",
      "description": "some description",
      "identity_verification": {
        "dl_state": "MI",
        "dl_number": "1235567",
        "dob_year": "1980"
      },
      "iias_ind": 1,
      "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "installment": true,
      "installment_number": 1,
      "installment_count": 1,
      "location_api_id": "location-api-id-florida-2",
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "advance_deposit": false,
      "no_show": false,
      "notification_email_address": "johnsmith@smiths.com",
      "order_number": "433659378839",
      "po_number": "555555553123",
      "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
      "recurring": false,
      "recurring_number": 1,
      "room_num": "303",
      "room_rate": 95,
      "save_account": false,
      "save_account_title": "John Account",
      "subtotal_amount": 599,
      "surcharge_amount": 100,
      "tags": [
        "Walk-in Customer"
      ],
      "tax": 0,
      "tip_amount": 0,
      "transaction_amount": 0,
      "secondary_amount": 0,
      "transaction_api_id": "transaction-payment-abcd123",
      "transaction_c1": "custom-data-1",
      "transaction_c2": "custom-data-2",
      "transaction_c3": "custom-data-3",
      "bank_funded_only_override": false,
      "allow_partial_authorization_override": false,
      "auto_decline_cvv_override": false,
      "auto_decline_street_override": false,
      "auto_decline_zip_override": false,
      "ebt_type": "food_stamp",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_holder_name": "smith",
      "account_type": "checking",
      "token_id": "11e95f8ec39de8fbdb0a4f1a",
      "ach_identifier": "P",
      "ach_sec_code": "C21",
      "auth_amount": 1,
      "auth_code": "BR349K",
      "avs": "BAD",
      "avs_enhanced": "N",
      "cardholder_present": true,
      "card_present": true,
      "check_number": "8520748520963",
      "customer_ip": "192.168.0.10",
      "cvv_response": "N",
      "entry_mode_id": "C",
      "emv_receipt_data": {
        "AID": "a0000000042203",
        "APPLAB": "US Maestro",
        "APPN": "US Maestro",
        "CVM": "Pin Verified",
        "TSI": "e800",
        "TVR": "0800008000"
      },
      "first_six": "545454",
      "last_four": "5454",
      "payment_method": "cc",
      "terminal_serial_number": "1234567890",
      "transaction_settlement_status": null,
      "charge_back_date": "2021-12-01",
      "is_recurring": true,
      "notification_email_sent": true,
      "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
      "reason_code_id": 1000,
      "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
      "settle_date": "2021-12-01",
      "status_code": 101,
      "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
      "verbiage": "APPROVED",
      "voucher_number": "1234",
      "void_date": "2021-12-01",
      "batch": "2",
      "terms_agree": true,
      "response_message": null,
      "return_date": "2021-12-01",
      "trx_source_id": 8,
      "routing_number": "051904524",
      "trx_source_code": 8,
      "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
      "is_accountvault": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "effective_date": "2021-12-01",
      "is_token": true,
      "installment_total": 1,
      "installment_counter": 1,
      "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
      "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "return_fee_transaction": {
      "additional_amounts": [
        {
          "type": "cashback",
          "amount": 10,
          "account_type": "credit",
          "currency": 840
        }
      ],
      "billing_address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "phone": "3339998822",
        "country": "USA"
      },
      "checkin_date": "2021-12-01",
      "checkout_date": "2021-12-01",
      "clerk_number": "AE1234",
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "custom_data": {},
      "customer_id": "customerid",
      "description": "some description",
      "identity_verification": {
        "dl_state": "MI",
        "dl_number": "1235567",
        "dob_year": "1980"
      },
      "iias_ind": 1,
      "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
      "installment": true,
      "installment_number": 1,
      "installment_count": 1,
      "location_api_id": "location-api-id-florida-2",
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "advance_deposit": false,
      "no_show": false,
      "notification_email_address": "johnsmith@smiths.com",
      "order_number": "433659378839",
      "po_number": "555555553123",
      "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
      "recurring": false,
      "recurring_number": 1,
      "room_num": "303",
      "room_rate": 95,
      "save_account": false,
      "save_account_title": "John Account",
      "subtotal_amount": 599,
      "surcharge_amount": 100,
      "tags": [
        "Walk-in Customer"
      ],
      "tax": 0,
      "tip_amount": 0,
      "transaction_amount": 0,
      "secondary_amount": 0,
      "transaction_api_id": "transaction-payment-abcd123",
      "transaction_c1": "custom-data-1",
      "transaction_c2": "custom-data-2",
      "transaction_c3": "custom-data-3",
      "bank_funded_only_override": false,
      "allow_partial_authorization_override": false,
      "auto_decline_cvv_override": false,
      "auto_decline_street_override": false,
      "auto_decline_zip_override": false,
      "ebt_type": "food_stamp",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_holder_name": "smith",
      "account_type": "checking",
      "token_id": "11e95f8ec39de8fbdb0a4f1a",
      "ach_identifier": "P",
      "ach_sec_code": "C21",
      "auth_amount": 1,
      "auth_code": "BR349K",
      "avs": "BAD",
      "avs_enhanced": "N",
      "cardholder_present": true,
      "card_present": true,
      "check_number": "8520748520963",
      "customer_ip": "192.168.0.10",
      "cvv_response": "N",
      "entry_mode_id": "C",
      "emv_receipt_data": {
        "AID": "a0000000042203",
        "APPLAB": "US Maestro",
        "APPN": "US Maestro",
        "CVM": "Pin Verified",
        "TSI": "e800",
        "TVR": "0800008000"
      },
      "first_six": "545454",
      "last_four": "5454",
      "payment_method": "cc",
      "terminal_serial_number": "1234567890",
      "transaction_settlement_status": null,
      "charge_back_date": "2021-12-01",
      "is_recurring": true,
      "notification_email_sent": true,
      "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
      "reason_code_id": 1000,
      "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
      "settle_date": "2021-12-01",
      "status_code": 101,
      "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
      "verbiage": "APPROVED",
      "voucher_number": "1234",
      "void_date": "2021-12-01",
      "batch": "2",
      "terms_agree": true,
      "response_message": null,
      "return_date": "2021-12-01",
      "trx_source_id": 8,
      "routing_number": "051904524",
      "trx_source_code": 8,
      "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
      "is_accountvault": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "effective_date": "2021-12-01",
      "is_token": true,
      "installment_total": 1,
      "installment_counter": 1,
      "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
      "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "created_user": {
      "account_number": "5454545454545454",
      "branding_domain_url": "{branding_domain_url}",
      "cell_phone": "3339998822",
      "company_name": "Fortis Payment Systems, LLC",
      "contact_id": "11e95f8ec39de8fbdb0a4f1a",
      "date_of_birth": "2021-12-01",
      "domain_id": "11e95f8ec39de8fbdb0a4f1a",
      "email": "email@domain.com",
      "email_trx_receipt": true,
      "home_phone": "3339998822",
      "first_name": "John",
      "last_name": "Smith",
      "locale": "en-US",
      "office_phone": "3339998822",
      "office_ext_phone": "5",
      "primary_location_id": "11e95f8ec39de8fbdb0a4f1a",
      "requires_new_password": null,
      "terms_condition_code": "20220308",
      "tz": "America/New_York",
      "ui_prefs": {
        "entry_page": "dashboard",
        "page_size": 2,
        "report_export_type": "csv",
        "process_method": "virtual_terminal",
        "default_terminal": "11e95f8ec39de8fbdb0a4f1a"
      },
      "username": "{user_name}",
      "user_api_key": "234bas8dfn8238f923w2",
      "user_hash_key": null,
      "user_type_code": 100,
      "password": null,
      "zip": "48375",
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "status_code": 1,
      "api_only": false,
      "is_invitation": false,
      "address": {
        "city": "Novi",
        "state": "MI",
        "postal_code": "48375",
        "country": "US"
      },
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "status": true,
      "login_attempts": 0,
      "last_login_ts": 1422040992,
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "terms_accepted_ts": 1422040992,
      "terms_agree_ip": "192.168.0.10",
      "current_date_time": "2019-03-11T10:38:26-0700",
      "current_login": 1422040992,
      "log_api_response_body_ts": 1422040992
    },
    "changelogs": [
      {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "action": "CREATE",
        "model": "TransactionRequest",
        "model_id": "11ec829598f0d4008be9aba4",
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "changelog_details": [
          {
            "id": "11e95f8ec39de8fbdb0a4f1a",
            "changelog_id": "11e95f8ec39de8fbdb0a4f1a",
            "field": "next_run_ts",
            "old_value": "1643616000"
          }
        ],
        "user": {
          "id": "11e95f8ec39de8fbdb0a4f1a",
          "username": "email@domain.com",
          "first_name": "Bob",
          "last_name": "Fairview"
        }
      }
    ]
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |
| 412 | Precondition Failed | [`Response412Exception`](../../doc/models/response-412-exception.md) |


# List All Transaction ACH Retries Related

```php
function listAllTransactionACHRetriesRelated(
    ?Page $page = null,
    ?array $order = null,
    ?array $filterBy = null,
    ?array $expand = null,
    ?string $format = null,
    ?string $typeahead = null,
    ?array $fields = null
): ResponseTransactionAchRetrysCollection
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `page` | [`?Page`](../../doc/models/page.md) | Query, Optional | Use this field to specify paginate your results, by using page size and number. You can use one of the following methods:<br><br>> /endpoint?page={ "number": 1, "size": 50 }<br>> <br>> /endpoint?page[number]=1&page[size]=50 |
| `order` | [`?(Order20[])`](../../doc/models/order-20.md) | Query, Optional | Criteria used in query string parameters to order results.  Most fields from the endpoint results can be used as a `key`.  Unsupported fields or operators will return a `412`.  Must be encoded, or use syntax that does not require encoding.<br><br>> /endpoint?order[0][key]=created_ts&order[0][operator]=asc<br>> <br>> /endpoint?order=[{ "key": "created_ts", "operator": "asc"}]<br>> <br>> /endpoint?order=[{ "key": "balance", "operator": "desc"},{ "key": "created_ts", "operator": "asc"}]<br><br>**Constraints**: *Minimum Items*: `1` |
| `filterBy` | [`?(FilterBy[])`](../../doc/models/filter-by.md) | Query, Optional | Filter criteria that can be used in query string parameters.  Most fields from the endpoint results can be used as a `key`.  Unsupported fields or operators will return a `412`. Must be encoded, or use syntax that does not require encoding.<br><br>> ?filter_by[0][key]=first_name&filter_by[0][operator]==&filter_by[0][value]=Steve<br>> <br>> /endpoint?filter_by=[{ "key": "first_name", "operator": "=", "value": "Fred" }]<br>> <br>> /endpoint?filter_by=[{ "key": "account_type", "operator": "=", "value": "VISA" }]<br>> <br>> /endpoint?filter_by=[{ "key": "created_ts", "operator": ">=", "value": "946702799" }, { "key": "created_ts", "operator": "<=", value: "1695061891" }]<br>> <br>> /endpoint?filter_by=[{ "key": "last_name", "operator": "IN", "value": "Williams,Brown,Allman" }]<br><br>**Constraints**: *Minimum Items*: `1` |
| `expand` | [`?(string(Expand54Enum)[])`](../../doc/models/expand-54-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |
| `format` | [`?string(Format1Enum)`](../../doc/models/format-1-enum.md) | Query, Optional | Reporting format, valid values: csv, tsv |
| `typeahead` | `?string` | Query, Optional | You can use any `field_name` from this endpoint results to order the list using the value provided as filter for the same `field_name`. It will be ordered using the following rules: 1) Exact match, 2) Starts with, 3) Contains.<br><br>> /endpoint?filter={ "field_name": "Value" }&_typeahead=field_name |
| `fields` | [`?(string(Field52Enum)[])`](../../doc/models/field-52-enum.md) | Query, Optional | You can use any `field_name` from this endpoint results to filter the list of fields returned on the response. |

## Response Type

[`ResponseTransactionAchRetrysCollection`](../../doc/models/response-transaction-ach-retrys-collection.md)

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

$result = $transactionACHRetriesController->listAllTransactionACHRetriesRelated(
    $page,
    $order,
    $filterBy
);
```

## Example Response *(as JSON)*

```json
{
  "type": "TransactionAchRetrysCollection",
  "list": [
    {
      "rejected_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "retry_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "return_fee_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "rejected_transaction": {
        "additional_amounts": [
          {
            "type": "cashback",
            "amount": 10,
            "account_type": "credit",
            "currency": 840
          }
        ],
        "billing_address": {
          "city": "Novi",
          "state": "Michigan",
          "postal_code": "48375",
          "phone": "3339998822",
          "country": "USA"
        },
        "checkin_date": "2021-12-01",
        "checkout_date": "2021-12-01",
        "clerk_number": "AE1234",
        "contact_id": "11e95f8ec39de8fbdb0a4f1a",
        "custom_data": {},
        "customer_id": "customerid",
        "description": "some description",
        "identity_verification": {
          "dl_state": "MI",
          "dl_number": "1235567",
          "dob_year": "1980"
        },
        "iias_ind": 1,
        "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
        "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
        "installment": true,
        "installment_number": 1,
        "installment_count": 1,
        "location_api_id": "location-api-id-florida-2",
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
        "advance_deposit": false,
        "no_show": false,
        "notification_email_address": "johnsmith@smiths.com",
        "order_number": "433659378839",
        "po_number": "555555553123",
        "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
        "recurring": false,
        "recurring_number": 1,
        "room_num": "303",
        "room_rate": 95,
        "save_account": false,
        "save_account_title": "John Account",
        "subtotal_amount": 599,
        "surcharge_amount": 100,
        "tags": [
          "Walk-in Customer"
        ],
        "tax": 0,
        "tip_amount": 0,
        "transaction_amount": 0,
        "secondary_amount": 0,
        "transaction_api_id": "transaction-payment-abcd123",
        "transaction_c1": "custom-data-1",
        "transaction_c2": "custom-data-2",
        "transaction_c3": "custom-data-3",
        "bank_funded_only_override": false,
        "allow_partial_authorization_override": false,
        "auto_decline_cvv_override": false,
        "auto_decline_street_override": false,
        "auto_decline_zip_override": false,
        "ebt_type": "food_stamp",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
        "account_holder_name": "smith",
        "account_type": "checking",
        "token_id": "11e95f8ec39de8fbdb0a4f1a",
        "ach_identifier": "P",
        "ach_sec_code": "C21",
        "auth_amount": 1,
        "auth_code": "BR349K",
        "avs": "BAD",
        "avs_enhanced": "N",
        "cardholder_present": true,
        "card_present": true,
        "check_number": "8520748520963",
        "customer_ip": "192.168.0.10",
        "cvv_response": "N",
        "entry_mode_id": "C",
        "emv_receipt_data": {
          "AID": "a0000000042203",
          "APPLAB": "US Maestro",
          "APPN": "US Maestro",
          "CVM": "Pin Verified",
          "TSI": "e800",
          "TVR": "0800008000"
        },
        "first_six": "545454",
        "last_four": "5454",
        "payment_method": "cc",
        "terminal_serial_number": "1234567890",
        "transaction_settlement_status": null,
        "charge_back_date": "2021-12-01",
        "is_recurring": true,
        "notification_email_sent": true,
        "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
        "reason_code_id": 1000,
        "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
        "settle_date": "2021-12-01",
        "status_code": 101,
        "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
        "verbiage": "APPROVED",
        "voucher_number": "1234",
        "void_date": "2021-12-01",
        "batch": "2",
        "terms_agree": true,
        "response_message": null,
        "return_date": "2021-12-01",
        "trx_source_id": 8,
        "routing_number": "051904524",
        "trx_source_code": 8,
        "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
        "is_accountvault": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "effective_date": "2021-12-01",
        "is_token": true,
        "installment_total": 1,
        "installment_counter": 1,
        "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
        "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
      },
      "retry_transaction": {
        "additional_amounts": [
          {
            "type": "cashback",
            "amount": 10,
            "account_type": "credit",
            "currency": 840
          }
        ],
        "billing_address": {
          "city": "Novi",
          "state": "Michigan",
          "postal_code": "48375",
          "phone": "3339998822",
          "country": "USA"
        },
        "checkin_date": "2021-12-01",
        "checkout_date": "2021-12-01",
        "clerk_number": "AE1234",
        "contact_id": "11e95f8ec39de8fbdb0a4f1a",
        "custom_data": {},
        "customer_id": "customerid",
        "description": "some description",
        "identity_verification": {
          "dl_state": "MI",
          "dl_number": "1235567",
          "dob_year": "1980"
        },
        "iias_ind": 1,
        "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
        "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
        "installment": true,
        "installment_number": 1,
        "installment_count": 1,
        "location_api_id": "location-api-id-florida-2",
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
        "advance_deposit": false,
        "no_show": false,
        "notification_email_address": "johnsmith@smiths.com",
        "order_number": "433659378839",
        "po_number": "555555553123",
        "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
        "recurring": false,
        "recurring_number": 1,
        "room_num": "303",
        "room_rate": 95,
        "save_account": false,
        "save_account_title": "John Account",
        "subtotal_amount": 599,
        "surcharge_amount": 100,
        "tags": [
          "Walk-in Customer"
        ],
        "tax": 0,
        "tip_amount": 0,
        "transaction_amount": 0,
        "secondary_amount": 0,
        "transaction_api_id": "transaction-payment-abcd123",
        "transaction_c1": "custom-data-1",
        "transaction_c2": "custom-data-2",
        "transaction_c3": "custom-data-3",
        "bank_funded_only_override": false,
        "allow_partial_authorization_override": false,
        "auto_decline_cvv_override": false,
        "auto_decline_street_override": false,
        "auto_decline_zip_override": false,
        "ebt_type": "food_stamp",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
        "account_holder_name": "smith",
        "account_type": "checking",
        "token_id": "11e95f8ec39de8fbdb0a4f1a",
        "ach_identifier": "P",
        "ach_sec_code": "C21",
        "auth_amount": 1,
        "auth_code": "BR349K",
        "avs": "BAD",
        "avs_enhanced": "N",
        "cardholder_present": true,
        "card_present": true,
        "check_number": "8520748520963",
        "customer_ip": "192.168.0.10",
        "cvv_response": "N",
        "entry_mode_id": "C",
        "emv_receipt_data": {
          "AID": "a0000000042203",
          "APPLAB": "US Maestro",
          "APPN": "US Maestro",
          "CVM": "Pin Verified",
          "TSI": "e800",
          "TVR": "0800008000"
        },
        "first_six": "545454",
        "last_four": "5454",
        "payment_method": "cc",
        "terminal_serial_number": "1234567890",
        "transaction_settlement_status": null,
        "charge_back_date": "2021-12-01",
        "is_recurring": true,
        "notification_email_sent": true,
        "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
        "reason_code_id": 1000,
        "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
        "settle_date": "2021-12-01",
        "status_code": 101,
        "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
        "verbiage": "APPROVED",
        "voucher_number": "1234",
        "void_date": "2021-12-01",
        "batch": "2",
        "terms_agree": true,
        "response_message": null,
        "return_date": "2021-12-01",
        "trx_source_id": 8,
        "routing_number": "051904524",
        "trx_source_code": 8,
        "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
        "is_accountvault": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "effective_date": "2021-12-01",
        "is_token": true,
        "installment_total": 1,
        "installment_counter": 1,
        "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
        "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
      },
      "return_fee_transaction": {
        "additional_amounts": [
          {
            "type": "cashback",
            "amount": 10,
            "account_type": "credit",
            "currency": 840
          }
        ],
        "billing_address": {
          "city": "Novi",
          "state": "Michigan",
          "postal_code": "48375",
          "phone": "3339998822",
          "country": "USA"
        },
        "checkin_date": "2021-12-01",
        "checkout_date": "2021-12-01",
        "clerk_number": "AE1234",
        "contact_id": "11e95f8ec39de8fbdb0a4f1a",
        "custom_data": {},
        "customer_id": "customerid",
        "description": "some description",
        "identity_verification": {
          "dl_state": "MI",
          "dl_number": "1235567",
          "dob_year": "1980"
        },
        "iias_ind": 1,
        "image_front": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
        "image_back": "U29tZVN0cmluZ09idmlvdXNseU5vdEJhc2U2NEVuY29kZWQ=",
        "installment": true,
        "installment_number": 1,
        "installment_count": 1,
        "location_api_id": "location-api-id-florida-2",
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
        "advance_deposit": false,
        "no_show": false,
        "notification_email_address": "johnsmith@smiths.com",
        "order_number": "433659378839",
        "po_number": "555555553123",
        "quick_invoice_id": "11e95f8ec39de8fbdb0a4f1a",
        "recurring": false,
        "recurring_number": 1,
        "room_num": "303",
        "room_rate": 95,
        "save_account": false,
        "save_account_title": "John Account",
        "subtotal_amount": 599,
        "surcharge_amount": 100,
        "tags": [
          "Walk-in Customer"
        ],
        "tax": 0,
        "tip_amount": 0,
        "transaction_amount": 0,
        "secondary_amount": 0,
        "transaction_api_id": "transaction-payment-abcd123",
        "transaction_c1": "custom-data-1",
        "transaction_c2": "custom-data-2",
        "transaction_c3": "custom-data-3",
        "bank_funded_only_override": false,
        "allow_partial_authorization_override": false,
        "auto_decline_cvv_override": false,
        "auto_decline_street_override": false,
        "auto_decline_zip_override": false,
        "ebt_type": "food_stamp",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "terminal_id": "11e95f8ec39de8fbdb0a4f1a",
        "account_holder_name": "smith",
        "account_type": "checking",
        "token_id": "11e95f8ec39de8fbdb0a4f1a",
        "ach_identifier": "P",
        "ach_sec_code": "C21",
        "auth_amount": 1,
        "auth_code": "BR349K",
        "avs": "BAD",
        "avs_enhanced": "N",
        "cardholder_present": true,
        "card_present": true,
        "check_number": "8520748520963",
        "customer_ip": "192.168.0.10",
        "cvv_response": "N",
        "entry_mode_id": "C",
        "emv_receipt_data": {
          "AID": "a0000000042203",
          "APPLAB": "US Maestro",
          "APPN": "US Maestro",
          "CVM": "Pin Verified",
          "TSI": "e800",
          "TVR": "0800008000"
        },
        "first_six": "545454",
        "last_four": "5454",
        "payment_method": "cc",
        "terminal_serial_number": "1234567890",
        "transaction_settlement_status": null,
        "charge_back_date": "2021-12-01",
        "is_recurring": true,
        "notification_email_sent": true,
        "par": "Q1J4Z28RKA1EBL470G9XYG90R5D3E",
        "reason_code_id": 1000,
        "recurring_id": "11e95f8ec39de8fbdb0a4f1a",
        "settle_date": "2021-12-01",
        "status_code": 101,
        "transaction_batch_id": "11e95f8ec39de8fbdb0a4f1a",
        "verbiage": "APPROVED",
        "voucher_number": "1234",
        "void_date": "2021-12-01",
        "batch": "2",
        "terms_agree": true,
        "response_message": null,
        "return_date": "2021-12-01",
        "trx_source_id": 8,
        "routing_number": "051904524",
        "trx_source_code": 8,
        "paylink_id": "11e95f8ec39de8fbdb0a4f1a",
        "is_accountvault": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "effective_date": "2021-12-01",
        "is_token": true,
        "installment_total": 1,
        "installment_counter": 1,
        "account_vault_id": "11e95f8ec39de8fbdb0a4f1a",
        "hosted_payment_page_id": "11e95f8ec39de8fbdb0a4f1a"
      },
      "created_user": {
        "account_number": "5454545454545454",
        "branding_domain_url": "{branding_domain_url}",
        "cell_phone": "3339998822",
        "company_name": "Fortis Payment Systems, LLC",
        "contact_id": "11e95f8ec39de8fbdb0a4f1a",
        "date_of_birth": "2021-12-01",
        "domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "email": "email@domain.com",
        "email_trx_receipt": true,
        "home_phone": "3339998822",
        "first_name": "John",
        "last_name": "Smith",
        "locale": "en-US",
        "office_phone": "3339998822",
        "office_ext_phone": "5",
        "primary_location_id": "11e95f8ec39de8fbdb0a4f1a",
        "requires_new_password": null,
        "terms_condition_code": "20220308",
        "tz": "America/New_York",
        "ui_prefs": {
          "entry_page": "dashboard",
          "page_size": 2,
          "report_export_type": "csv",
          "process_method": "virtual_terminal",
          "default_terminal": "11e95f8ec39de8fbdb0a4f1a"
        },
        "username": "{user_name}",
        "user_api_key": "234bas8dfn8238f923w2",
        "user_hash_key": null,
        "user_type_code": 100,
        "password": null,
        "zip": "48375",
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "status_code": 1,
        "api_only": false,
        "is_invitation": false,
        "address": {
          "city": "Novi",
          "state": "MI",
          "postal_code": "48375",
          "country": "US"
        },
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "status": true,
        "login_attempts": 0,
        "last_login_ts": 1422040992,
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "terms_accepted_ts": 1422040992,
        "terms_agree_ip": "192.168.0.10",
        "current_date_time": "2019-03-11T10:38:26-0700",
        "current_login": 1422040992,
        "log_api_response_body_ts": 1422040992
      },
      "changelogs": [
        {
          "id": "11e95f8ec39de8fbdb0a4f1a",
          "created_ts": 1422040992,
          "action": "CREATE",
          "model": "TransactionRequest",
          "model_id": "11ec829598f0d4008be9aba4",
          "user_id": "11e95f8ec39de8fbdb0a4f1a",
          "changelog_details": [
            {
              "id": "11e95f8ec39de8fbdb0a4f1a",
              "changelog_id": "11e95f8ec39de8fbdb0a4f1a",
              "field": "next_run_ts",
              "old_value": "1643616000"
            }
          ],
          "user": {
            "id": "11e95f8ec39de8fbdb0a4f1a",
            "username": "email@domain.com",
            "first_name": "Bob",
            "last_name": "Fairview"
          }
        }
      ]
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

