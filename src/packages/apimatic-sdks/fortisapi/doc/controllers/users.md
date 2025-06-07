# Users

```php
$usersController = $client->getUsersController();
```

## Class Name

`UsersController`

## Methods

* [Delete a User Record](../../doc/controllers/users.md#delete-a-user-record)
* [View Single User Record](../../doc/controllers/users.md#view-single-user-record)
* [Create a New User](../../doc/controllers/users.md#create-a-new-user)
* [Update a User Record](../../doc/controllers/users.md#update-a-user-record)
* [Create a New API Key](../../doc/controllers/users.md#create-a-new-api-key)
* [List All User](../../doc/controllers/users.md#list-all-user)
* [Send Verification](../../doc/controllers/users.md#send-verification)
* [View Self Record](../../doc/controllers/users.md#view-self-record)
* [Remove Verification](../../doc/controllers/users.md#remove-verification)


# Delete a User Record

```php
function deleteAUserRecord(string $userId): ResponseUser
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `userId` | `string` | Template, Required | User ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` |

## Response Type

[`ResponseUser`](../../doc/models/response-user.md)

## Example Usage

```php
$userId = '11e95f8ec39de8fbdb0a4f1a';

$result = $usersController->deleteAUserRecord($userId);
```

## Example Response *(as JSON)*

```json
{
  "type": "User",
  "data": {
    "account_number": "5454545454545454",
    "branding_domain_url": "{branding_domain_url}",
    "cell_phone": "3339998822",
    "company_name": "Fortis Payment Systems, LLC",
    "contact_id": "Sample contact ID",
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
    "log_api_response_body_ts": 1422040992,
    "locations": [
      {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "account_number": "5454545454545454",
        "address": {
          "city": "Novi",
          "state": "MI",
          "postal_code": "48375",
          "country": "US"
        },
        "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "contact_email_trx_receipt_default": true,
        "default_ach": "11e608a7d515f1e093242bb2",
        "default_cc": "11e608a442a5f1e092242dda",
        "email_reply_to": "email@domain.com",
        "fax": "3339998822",
        "location_api_id": "location-111111",
        "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
        "location_c1": "custom 1",
        "location_c2": "custom 2",
        "location_c3": "custom data 3",
        "name": "Sample Company Headquarters",
        "office_phone": "2481234567",
        "office_ext_phone": "1021021209",
        "tz": "America/New_York",
        "parent_id": "11e95f8ec39de8fbdb0a4f1a",
        "show_contact_notes": true,
        "show_contact_files": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_type": "merchant",
        "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
      }
    ],
    "primary_location": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "account_number": "5454545454545454",
      "address": {
        "city": "Novi",
        "state": "MI",
        "postal_code": "48375",
        "country": "US"
      },
      "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
      "contact_email_trx_receipt_default": true,
      "default_ach": "11e608a7d515f1e093242bb2",
      "default_cc": "11e608a442a5f1e092242dda",
      "email_reply_to": "email@domain.com",
      "fax": "3339998822",
      "location_api_id": "location-111111",
      "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
      "location_c1": "custom 1",
      "location_c2": "custom 2",
      "location_c3": "custom data 3",
      "name": "Sample Company Headquarters",
      "office_phone": "2481234567",
      "office_ext_phone": "1021021209",
      "tz": "America/New_York",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "show_contact_notes": true,
      "show_contact_files": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "location_type": "merchant",
      "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
    },
    "received_emails": [
      {
        "subject": "Payment Receipt - 12skiestech",
        "body": "This email is being sent from a server.",
        "source_address": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "return_path": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "provider_id": "0100017e67bcc530-e1dd23b4-8a39-4a5b-8d5d-68d51c4c942f-000000",
        "domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "reason_sent": "Contact Email",
        "reason_model": "Transaction",
        "reason_model_id": "11e95f8ec39de8fbdb0a4f1a",
        "reply_to": "\"Zeamster\" <emma.p@zeamster.com>",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992
      }
    ],
    "contact": {
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_number": "54545433332",
      "contact_api_id": "137",
      "first_name": "John",
      "last_name": "Smith",
      "cell_phone": "3339998822",
      "balance": 245.36,
      "address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "country": "USA"
      },
      "company_name": "Fortis Payment Systems, LLC",
      "header_message": "This is a sample message for you",
      "date_of_birth": "2021-12-01",
      "email_trx_receipt": true,
      "home_phone": "3339998822",
      "office_phone": "3339998822",
      "office_phone_ext": "5",
      "home_phone_country_code": "+1",
      "office_phone_country_code": "+1",
      "cell_phone_country_code": "+1",
      "header_message_type": 0,
      "update_if_exists": 1,
      "contact_c1": "any",
      "contact_c2": "anything",
      "contact_c3": "something",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "email": "email@domain.com",
      "token_import_id": "11e95f8ec39de8fbdb0a4f1a",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "active": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "isDeletable": true,
    "active_notification_alerts": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "date_start": "2021-12-01 10:10:00",
        "date_end": "2021-12-01 10:10:00",
        "user_location": true,
        "user_contact": true,
        "include_children": true,
        "alert_type": 1,
        "alert_type_id": 1,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "location_users": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "auth_roles": [
      {
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "auth_role_code": 110,
        "code": 83931,
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
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
    ],
    "resources": {
      "title": "My terminal",
      "priv": null,
      "resource_name": "v2.addons.iframe.get",
      "id": 5693,
      "last_used_date": 1422040992,
      "created_ts": 1422040992,
      "modified_ts": 1422040992
    },
    "domain": {
      "url": "fortispayrbyn9y.sandbox.zeamster.com",
      "title": "Test Brand Domain Title 2",
      "logo": "",
      "support_email": "email@domain.com",
      "allow_contact_signup": true,
      "allow_contact_registration": true,
      "allow_contact_login": true,
      "registration_fields": [
        "account_number"
      ],
      "company_name": null,
      "nav_color": null,
      "button_primary_color": null,
      "logo_background_color": null,
      "icon_background_color": null,
      "menu_text_background_color": null,
      "menu_text_color": null,
      "right_menu_background_color": null,
      "right_menu_text_color": null,
      "button_primary_text_color": null,
      "nav_logo": null,
      "fav_icon": null,
      "aes_key": null,
      "help_text": null,
      "email_reply_to": "email@domain.com",
      "email": "email@domain.com",
      "custom_javascript": null,
      "custom_theme": null,
      "custom_css": null,
      "contact_user_default_entry_page": "dashboard",
      "contact_user_default_auth_roles": [
        null
      ],
      "custom_stylesheet_url": "https://127.0.0.1/receiver",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992
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
    "location_marketplaces": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "marketplace_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "email_blacklist": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "isBlacklisted": true,
      "detail": true,
      "created_ts": 1422040992
    },
    "helppage": {
      "user_type_code": 100,
      "body": "Sample Body",
      "title": "Sample Title",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
    }
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |


# View Single User Record

```php
function viewSingleUserRecord(string $userId, ?array $expand = null, ?array $fields = null): ResponseUser
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `userId` | `string` | Template, Required | User ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` |
| `expand` | [`?(string(Expand108Enum)[])`](../../doc/models/expand-108-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |
| `fields` | [`?(string(Field57Enum)[])`](../../doc/models/field-57-enum.md) | Query, Optional | You can use any `field_name` from this endpoint results to filter the list of fields returned on the response. |

## Response Type

[`ResponseUser`](../../doc/models/response-user.md)

## Example Usage

```php
$userId = '11e95f8ec39de8fbdb0a4f1a';

$result = $usersController->viewSingleUserRecord($userId);
```

## Example Response *(as JSON)*

```json
{
  "type": "User",
  "data": {
    "account_number": "5454545454545454",
    "branding_domain_url": "{branding_domain_url}",
    "cell_phone": "3339998822",
    "company_name": "Fortis Payment Systems, LLC",
    "contact_id": "Sample contact ID",
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
    "log_api_response_body_ts": 1422040992,
    "locations": [
      {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "account_number": "5454545454545454",
        "address": {
          "city": "Novi",
          "state": "MI",
          "postal_code": "48375",
          "country": "US"
        },
        "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "contact_email_trx_receipt_default": true,
        "default_ach": "11e608a7d515f1e093242bb2",
        "default_cc": "11e608a442a5f1e092242dda",
        "email_reply_to": "email@domain.com",
        "fax": "3339998822",
        "location_api_id": "location-111111",
        "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
        "location_c1": "custom 1",
        "location_c2": "custom 2",
        "location_c3": "custom data 3",
        "name": "Sample Company Headquarters",
        "office_phone": "2481234567",
        "office_ext_phone": "1021021209",
        "tz": "America/New_York",
        "parent_id": "11e95f8ec39de8fbdb0a4f1a",
        "show_contact_notes": true,
        "show_contact_files": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_type": "merchant",
        "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
      }
    ],
    "primary_location": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "account_number": "5454545454545454",
      "address": {
        "city": "Novi",
        "state": "MI",
        "postal_code": "48375",
        "country": "US"
      },
      "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
      "contact_email_trx_receipt_default": true,
      "default_ach": "11e608a7d515f1e093242bb2",
      "default_cc": "11e608a442a5f1e092242dda",
      "email_reply_to": "email@domain.com",
      "fax": "3339998822",
      "location_api_id": "location-111111",
      "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
      "location_c1": "custom 1",
      "location_c2": "custom 2",
      "location_c3": "custom data 3",
      "name": "Sample Company Headquarters",
      "office_phone": "2481234567",
      "office_ext_phone": "1021021209",
      "tz": "America/New_York",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "show_contact_notes": true,
      "show_contact_files": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "location_type": "merchant",
      "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
    },
    "received_emails": [
      {
        "subject": "Payment Receipt - 12skiestech",
        "body": "This email is being sent from a server.",
        "source_address": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "return_path": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "provider_id": "0100017e67bcc530-e1dd23b4-8a39-4a5b-8d5d-68d51c4c942f-000000",
        "domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "reason_sent": "Contact Email",
        "reason_model": "Transaction",
        "reason_model_id": "11e95f8ec39de8fbdb0a4f1a",
        "reply_to": "\"Zeamster\" <emma.p@zeamster.com>",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992
      }
    ],
    "contact": {
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_number": "54545433332",
      "contact_api_id": "137",
      "first_name": "John",
      "last_name": "Smith",
      "cell_phone": "3339998822",
      "balance": 245.36,
      "address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "country": "USA"
      },
      "company_name": "Fortis Payment Systems, LLC",
      "header_message": "This is a sample message for you",
      "date_of_birth": "2021-12-01",
      "email_trx_receipt": true,
      "home_phone": "3339998822",
      "office_phone": "3339998822",
      "office_phone_ext": "5",
      "home_phone_country_code": "+1",
      "office_phone_country_code": "+1",
      "cell_phone_country_code": "+1",
      "header_message_type": 0,
      "update_if_exists": 1,
      "contact_c1": "any",
      "contact_c2": "anything",
      "contact_c3": "something",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "email": "email@domain.com",
      "token_import_id": "11e95f8ec39de8fbdb0a4f1a",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "active": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "isDeletable": true,
    "active_notification_alerts": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "date_start": "2021-12-01 10:10:00",
        "date_end": "2021-12-01 10:10:00",
        "user_location": true,
        "user_contact": true,
        "include_children": true,
        "alert_type": 1,
        "alert_type_id": 1,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "location_users": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "auth_roles": [
      {
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "auth_role_code": 110,
        "code": 83931,
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
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
    ],
    "resources": {
      "title": "My terminal",
      "priv": null,
      "resource_name": "v2.addons.iframe.get",
      "id": 5693,
      "last_used_date": 1422040992,
      "created_ts": 1422040992,
      "modified_ts": 1422040992
    },
    "domain": {
      "url": "fortispayrbyn9y.sandbox.zeamster.com",
      "title": "Test Brand Domain Title 2",
      "logo": "",
      "support_email": "email@domain.com",
      "allow_contact_signup": true,
      "allow_contact_registration": true,
      "allow_contact_login": true,
      "registration_fields": [
        "account_number"
      ],
      "company_name": null,
      "nav_color": null,
      "button_primary_color": null,
      "logo_background_color": null,
      "icon_background_color": null,
      "menu_text_background_color": null,
      "menu_text_color": null,
      "right_menu_background_color": null,
      "right_menu_text_color": null,
      "button_primary_text_color": null,
      "nav_logo": null,
      "fav_icon": null,
      "aes_key": null,
      "help_text": null,
      "email_reply_to": "email@domain.com",
      "email": "email@domain.com",
      "custom_javascript": null,
      "custom_theme": null,
      "custom_css": null,
      "contact_user_default_entry_page": "dashboard",
      "contact_user_default_auth_roles": [
        null
      ],
      "custom_stylesheet_url": "https://127.0.0.1/receiver",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992
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
    "location_marketplaces": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "marketplace_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "email_blacklist": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "isBlacklisted": true,
      "detail": true,
      "created_ts": 1422040992
    },
    "helppage": {
      "user_type_code": 100,
      "body": "Sample Body",
      "title": "Sample Title",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
    }
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |


# Create a New User

```php
function createANewUser(V1UsersRequest $body, ?array $expand = null): ResponseUser
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `body` | [`V1UsersRequest`](../../doc/models/v1-users-request.md) | Body, Required | - |
| `expand` | [`?(string(Expand108Enum)[])`](../../doc/models/expand-108-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |

## Response Type

[`ResponseUser`](../../doc/models/response-user.md)

## Example Usage

```php
$body = V1UsersRequestBuilder::init(
    'email@domain.com',
    'Smith',
    '11e95f8ec39de8fbdb0a4f1a',
    '{user_name}',
    UserTypeCodeEnum::ENUM_100
)
    ->accountNumber('5454545454545454')
    ->brandingDomainUrl('{branding_domain_url}')
    ->cellPhone('3339998822')
    ->companyName('Fortis Payment Systems, LLC')
    ->contactId('11e95f8ec39de8fbdb0a4f1a')
    ->dateOfBirth('2021-12-01')
    ->domainId('11e95f8ec39de8fbdb0a4f1a')
    ->emailTrxReceipt(true)
    ->homePhone('3339998822')
    ->firstName('John')
    ->locale('en-US')
    ->officePhone('3339998822')
    ->officeExtPhone('5')
    ->termsConditionCode('20220308')
    ->tz('America/New_York')
    ->userApiKey('234bas8dfn8238f923w2')
    ->zip('48375')
    ->locationId('11e95f8ec39de8fbdb0a4f1a')
    ->statusCode(StatusCodeEnum::ENUM_1)
    ->apiOnly(false)
    ->isInvitation(false)
    ->build();

$result = $usersController->createANewUser($body);
```

## Example Response *(as JSON)*

```json
{
  "type": "User",
  "data": {
    "account_number": "5454545454545454",
    "branding_domain_url": "{branding_domain_url}",
    "cell_phone": "3339998822",
    "company_name": "Fortis Payment Systems, LLC",
    "contact_id": "Sample contact ID",
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
    "log_api_response_body_ts": 1422040992,
    "locations": [
      {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "account_number": "5454545454545454",
        "address": {
          "city": "Novi",
          "state": "MI",
          "postal_code": "48375",
          "country": "US"
        },
        "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "contact_email_trx_receipt_default": true,
        "default_ach": "11e608a7d515f1e093242bb2",
        "default_cc": "11e608a442a5f1e092242dda",
        "email_reply_to": "email@domain.com",
        "fax": "3339998822",
        "location_api_id": "location-111111",
        "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
        "location_c1": "custom 1",
        "location_c2": "custom 2",
        "location_c3": "custom data 3",
        "name": "Sample Company Headquarters",
        "office_phone": "2481234567",
        "office_ext_phone": "1021021209",
        "tz": "America/New_York",
        "parent_id": "11e95f8ec39de8fbdb0a4f1a",
        "show_contact_notes": true,
        "show_contact_files": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_type": "merchant",
        "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
      }
    ],
    "primary_location": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "account_number": "5454545454545454",
      "address": {
        "city": "Novi",
        "state": "MI",
        "postal_code": "48375",
        "country": "US"
      },
      "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
      "contact_email_trx_receipt_default": true,
      "default_ach": "11e608a7d515f1e093242bb2",
      "default_cc": "11e608a442a5f1e092242dda",
      "email_reply_to": "email@domain.com",
      "fax": "3339998822",
      "location_api_id": "location-111111",
      "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
      "location_c1": "custom 1",
      "location_c2": "custom 2",
      "location_c3": "custom data 3",
      "name": "Sample Company Headquarters",
      "office_phone": "2481234567",
      "office_ext_phone": "1021021209",
      "tz": "America/New_York",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "show_contact_notes": true,
      "show_contact_files": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "location_type": "merchant",
      "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
    },
    "received_emails": [
      {
        "subject": "Payment Receipt - 12skiestech",
        "body": "This email is being sent from a server.",
        "source_address": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "return_path": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "provider_id": "0100017e67bcc530-e1dd23b4-8a39-4a5b-8d5d-68d51c4c942f-000000",
        "domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "reason_sent": "Contact Email",
        "reason_model": "Transaction",
        "reason_model_id": "11e95f8ec39de8fbdb0a4f1a",
        "reply_to": "\"Zeamster\" <emma.p@zeamster.com>",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992
      }
    ],
    "contact": {
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_number": "54545433332",
      "contact_api_id": "137",
      "first_name": "John",
      "last_name": "Smith",
      "cell_phone": "3339998822",
      "balance": 245.36,
      "address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "country": "USA"
      },
      "company_name": "Fortis Payment Systems, LLC",
      "header_message": "This is a sample message for you",
      "date_of_birth": "2021-12-01",
      "email_trx_receipt": true,
      "home_phone": "3339998822",
      "office_phone": "3339998822",
      "office_phone_ext": "5",
      "home_phone_country_code": "+1",
      "office_phone_country_code": "+1",
      "cell_phone_country_code": "+1",
      "header_message_type": 0,
      "update_if_exists": 1,
      "contact_c1": "any",
      "contact_c2": "anything",
      "contact_c3": "something",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "email": "email@domain.com",
      "token_import_id": "11e95f8ec39de8fbdb0a4f1a",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "active": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "isDeletable": true,
    "active_notification_alerts": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "date_start": "2021-12-01 10:10:00",
        "date_end": "2021-12-01 10:10:00",
        "user_location": true,
        "user_contact": true,
        "include_children": true,
        "alert_type": 1,
        "alert_type_id": 1,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "location_users": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "auth_roles": [
      {
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "auth_role_code": 110,
        "code": 83931,
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
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
    ],
    "resources": {
      "title": "My terminal",
      "priv": null,
      "resource_name": "v2.addons.iframe.get",
      "id": 5693,
      "last_used_date": 1422040992,
      "created_ts": 1422040992,
      "modified_ts": 1422040992
    },
    "domain": {
      "url": "fortispayrbyn9y.sandbox.zeamster.com",
      "title": "Test Brand Domain Title 2",
      "logo": "",
      "support_email": "email@domain.com",
      "allow_contact_signup": true,
      "allow_contact_registration": true,
      "allow_contact_login": true,
      "registration_fields": [
        "account_number"
      ],
      "company_name": null,
      "nav_color": null,
      "button_primary_color": null,
      "logo_background_color": null,
      "icon_background_color": null,
      "menu_text_background_color": null,
      "menu_text_color": null,
      "right_menu_background_color": null,
      "right_menu_text_color": null,
      "button_primary_text_color": null,
      "nav_logo": null,
      "fav_icon": null,
      "aes_key": null,
      "help_text": null,
      "email_reply_to": "email@domain.com",
      "email": "email@domain.com",
      "custom_javascript": null,
      "custom_theme": null,
      "custom_css": null,
      "contact_user_default_entry_page": "dashboard",
      "contact_user_default_auth_roles": [
        null
      ],
      "custom_stylesheet_url": "https://127.0.0.1/receiver",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992
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
    "location_marketplaces": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "marketplace_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "email_blacklist": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "isBlacklisted": true,
      "detail": true,
      "created_ts": 1422040992
    },
    "helppage": {
      "user_type_code": 100,
      "body": "Sample Body",
      "title": "Sample Title",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
    }
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |
| 412 | Precondition Failed | [`Response412Exception`](../../doc/models/response-412-exception.md) |


# Update a User Record

```php
function updateAUserRecord(string $userId, V1UsersRequest1 $body, ?array $expand = null): ResponseUser
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `userId` | `string` | Template, Required | User ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` |
| `body` | [`V1UsersRequest1`](../../doc/models/v1-users-request-1.md) | Body, Required | - |
| `expand` | [`?(string(Expand108Enum)[])`](../../doc/models/expand-108-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |

## Response Type

[`ResponseUser`](../../doc/models/response-user.md)

## Example Usage

```php
$userId = '11e95f8ec39de8fbdb0a4f1a';

$body = V1UsersRequest1Builder::init()
    ->accountNumber('5454545454545454')
    ->brandingDomainUrl('{branding_domain_url}')
    ->cellPhone('3339998822')
    ->companyName('Fortis Payment Systems, LLC')
    ->contactId('11e95f8ec39de8fbdb0a4f1a')
    ->dateOfBirth('2021-12-01')
    ->domainId('11e95f8ec39de8fbdb0a4f1a')
    ->email('email@domain.com')
    ->emailTrxReceipt(true)
    ->homePhone('3339998822')
    ->firstName('John')
    ->lastName('Smith')
    ->locale('en-US')
    ->officePhone('3339998822')
    ->officeExtPhone('5')
    ->primaryLocationId('11e95f8ec39de8fbdb0a4f1a')
    ->termsConditionCode('20220308')
    ->tz('America/New_York')
    ->username('{user_name}')
    ->userApiKey('234bas8dfn8238f923w2')
    ->userTypeCode(UserTypeCodeEnum::ENUM_100)
    ->zip('48375')
    ->locationId('11e95f8ec39de8fbdb0a4f1a')
    ->statusCode(StatusCodeEnum::ENUM_1)
    ->apiOnly(false)
    ->isInvitation(false)
    ->build();

$result = $usersController->updateAUserRecord(
    $userId,
    $body
);
```

## Example Response *(as JSON)*

```json
{
  "type": "User",
  "data": {
    "account_number": "5454545454545454",
    "branding_domain_url": "{branding_domain_url}",
    "cell_phone": "3339998822",
    "company_name": "Fortis Payment Systems, LLC",
    "contact_id": "Sample contact ID",
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
    "log_api_response_body_ts": 1422040992,
    "locations": [
      {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "account_number": "5454545454545454",
        "address": {
          "city": "Novi",
          "state": "MI",
          "postal_code": "48375",
          "country": "US"
        },
        "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "contact_email_trx_receipt_default": true,
        "default_ach": "11e608a7d515f1e093242bb2",
        "default_cc": "11e608a442a5f1e092242dda",
        "email_reply_to": "email@domain.com",
        "fax": "3339998822",
        "location_api_id": "location-111111",
        "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
        "location_c1": "custom 1",
        "location_c2": "custom 2",
        "location_c3": "custom data 3",
        "name": "Sample Company Headquarters",
        "office_phone": "2481234567",
        "office_ext_phone": "1021021209",
        "tz": "America/New_York",
        "parent_id": "11e95f8ec39de8fbdb0a4f1a",
        "show_contact_notes": true,
        "show_contact_files": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_type": "merchant",
        "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
      }
    ],
    "primary_location": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "account_number": "5454545454545454",
      "address": {
        "city": "Novi",
        "state": "MI",
        "postal_code": "48375",
        "country": "US"
      },
      "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
      "contact_email_trx_receipt_default": true,
      "default_ach": "11e608a7d515f1e093242bb2",
      "default_cc": "11e608a442a5f1e092242dda",
      "email_reply_to": "email@domain.com",
      "fax": "3339998822",
      "location_api_id": "location-111111",
      "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
      "location_c1": "custom 1",
      "location_c2": "custom 2",
      "location_c3": "custom data 3",
      "name": "Sample Company Headquarters",
      "office_phone": "2481234567",
      "office_ext_phone": "1021021209",
      "tz": "America/New_York",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "show_contact_notes": true,
      "show_contact_files": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "location_type": "merchant",
      "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
    },
    "received_emails": [
      {
        "subject": "Payment Receipt - 12skiestech",
        "body": "This email is being sent from a server.",
        "source_address": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "return_path": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "provider_id": "0100017e67bcc530-e1dd23b4-8a39-4a5b-8d5d-68d51c4c942f-000000",
        "domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "reason_sent": "Contact Email",
        "reason_model": "Transaction",
        "reason_model_id": "11e95f8ec39de8fbdb0a4f1a",
        "reply_to": "\"Zeamster\" <emma.p@zeamster.com>",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992
      }
    ],
    "contact": {
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_number": "54545433332",
      "contact_api_id": "137",
      "first_name": "John",
      "last_name": "Smith",
      "cell_phone": "3339998822",
      "balance": 245.36,
      "address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "country": "USA"
      },
      "company_name": "Fortis Payment Systems, LLC",
      "header_message": "This is a sample message for you",
      "date_of_birth": "2021-12-01",
      "email_trx_receipt": true,
      "home_phone": "3339998822",
      "office_phone": "3339998822",
      "office_phone_ext": "5",
      "home_phone_country_code": "+1",
      "office_phone_country_code": "+1",
      "cell_phone_country_code": "+1",
      "header_message_type": 0,
      "update_if_exists": 1,
      "contact_c1": "any",
      "contact_c2": "anything",
      "contact_c3": "something",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "email": "email@domain.com",
      "token_import_id": "11e95f8ec39de8fbdb0a4f1a",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "active": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "isDeletable": true,
    "active_notification_alerts": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "date_start": "2021-12-01 10:10:00",
        "date_end": "2021-12-01 10:10:00",
        "user_location": true,
        "user_contact": true,
        "include_children": true,
        "alert_type": 1,
        "alert_type_id": 1,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "location_users": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "auth_roles": [
      {
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "auth_role_code": 110,
        "code": 83931,
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
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
    ],
    "resources": {
      "title": "My terminal",
      "priv": null,
      "resource_name": "v2.addons.iframe.get",
      "id": 5693,
      "last_used_date": 1422040992,
      "created_ts": 1422040992,
      "modified_ts": 1422040992
    },
    "domain": {
      "url": "fortispayrbyn9y.sandbox.zeamster.com",
      "title": "Test Brand Domain Title 2",
      "logo": "",
      "support_email": "email@domain.com",
      "allow_contact_signup": true,
      "allow_contact_registration": true,
      "allow_contact_login": true,
      "registration_fields": [
        "account_number"
      ],
      "company_name": null,
      "nav_color": null,
      "button_primary_color": null,
      "logo_background_color": null,
      "icon_background_color": null,
      "menu_text_background_color": null,
      "menu_text_color": null,
      "right_menu_background_color": null,
      "right_menu_text_color": null,
      "button_primary_text_color": null,
      "nav_logo": null,
      "fav_icon": null,
      "aes_key": null,
      "help_text": null,
      "email_reply_to": "email@domain.com",
      "email": "email@domain.com",
      "custom_javascript": null,
      "custom_theme": null,
      "custom_css": null,
      "contact_user_default_entry_page": "dashboard",
      "contact_user_default_auth_roles": [
        null
      ],
      "custom_stylesheet_url": "https://127.0.0.1/receiver",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992
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
    "location_marketplaces": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "marketplace_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "email_blacklist": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "isBlacklisted": true,
      "detail": true,
      "created_ts": 1422040992
    },
    "helppage": {
      "user_type_code": 100,
      "body": "Sample Body",
      "title": "Sample Title",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
    }
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |
| 412 | Precondition Failed | [`Response412Exception`](../../doc/models/response-412-exception.md) |


# Create a New API Key

```php
function createANewAPIKey(string $userId, ?array $expand = null): ResponseUserApiKey
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `userId` | `string` | Template, Required | User ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` |
| `expand` | [`?(string(Expand108Enum)[])`](../../doc/models/expand-108-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |

## Response Type

[`ResponseUserApiKey`](../../doc/models/response-user-api-key.md)

## Example Usage

```php
$userId = '11e95f8ec39de8fbdb0a4f1a';

$result = $usersController->createANewAPIKey($userId);
```

## Example Response *(as JSON)*

```json
{
  "type": "UserApiKey",
  "data": {
    "user_api_key": "234bas8dfn8238f923w2"
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |


# List All User

```php
function listAllUser(
    ?Page $page = null,
    ?array $order = null,
    ?array $filterBy = null,
    ?array $expand = null,
    ?string $format = null,
    ?string $typeahead = null,
    ?array $fields = null
): ResponseUsersCollection
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `page` | [`?Page`](../../doc/models/page.md) | Query, Optional | Use this field to specify paginate your results, by using page size and number. You can use one of the following methods:<br><br>> /endpoint?page={ "number": 1, "size": 50 }<br>> <br>> /endpoint?page[number]=1&page[size]=50 |
| `order` | [`?(Order20[])`](../../doc/models/order-20.md) | Query, Optional | Criteria used in query string parameters to order results.  Most fields from the endpoint results can be used as a `key`.  Unsupported fields or operators will return a `412`.  Must be encoded, or use syntax that does not require encoding.<br><br>> /endpoint?order[0][key]=created_ts&order[0][operator]=asc<br>> <br>> /endpoint?order=[{ "key": "created_ts", "operator": "asc"}]<br>> <br>> /endpoint?order=[{ "key": "balance", "operator": "desc"},{ "key": "created_ts", "operator": "asc"}]<br><br>**Constraints**: *Minimum Items*: `1` |
| `filterBy` | [`?(FilterBy[])`](../../doc/models/filter-by.md) | Query, Optional | Filter criteria that can be used in query string parameters.  Most fields from the endpoint results can be used as a `key`.  Unsupported fields or operators will return a `412`. Must be encoded, or use syntax that does not require encoding.<br><br>> ?filter_by[0][key]=first_name&filter_by[0][operator]==&filter_by[0][value]=Steve<br>> <br>> /endpoint?filter_by=[{ "key": "first_name", "operator": "=", "value": "Fred" }]<br>> <br>> /endpoint?filter_by=[{ "key": "account_type", "operator": "=", "value": "VISA" }]<br>> <br>> /endpoint?filter_by=[{ "key": "created_ts", "operator": ">=", "value": "946702799" }, { "key": "created_ts", "operator": "<=", value: "1695061891" }]<br>> <br>> /endpoint?filter_by=[{ "key": "last_name", "operator": "IN", "value": "Williams,Brown,Allman" }]<br><br>**Constraints**: *Minimum Items*: `1` |
| `expand` | [`?(string(Expand108Enum)[])`](../../doc/models/expand-108-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |
| `format` | [`?string(Format1Enum)`](../../doc/models/format-1-enum.md) | Query, Optional | Reporting format, valid values: csv, tsv |
| `typeahead` | `?string` | Query, Optional | You can use any `field_name` from this endpoint results to order the list using the value provided as filter for the same `field_name`. It will be ordered using the following rules: 1) Exact match, 2) Starts with, 3) Contains.<br><br>> /endpoint?filter={ "field_name": "Value" }&_typeahead=field_name |
| `fields` | [`?(string(Field57Enum)[])`](../../doc/models/field-57-enum.md) | Query, Optional | You can use any `field_name` from this endpoint results to filter the list of fields returned on the response. |

## Response Type

[`ResponseUsersCollection`](../../doc/models/response-users-collection.md)

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

$result = $usersController->listAllUser(
    $page,
    $order,
    $filterBy
);
```

## Example Response *(as JSON)*

```json
{
  "type": "UsersCollection",
  "list": [
    {
      "account_number": "5454545454545454",
      "branding_domain_url": "{branding_domain_url}",
      "cell_phone": "3339998822",
      "company_name": "Fortis Payment Systems, LLC",
      "contact_id": "Sample contact ID",
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
      "log_api_response_body_ts": 1422040992,
      "locations": [
        {
          "id": "11e95f8ec39de8fbdb0a4f1a",
          "created_ts": 1422040992,
          "modified_ts": 1422040992,
          "account_number": "5454545454545454",
          "address": {
            "city": "Novi",
            "state": "MI",
            "postal_code": "48375",
            "country": "US"
          },
          "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
          "contact_email_trx_receipt_default": true,
          "default_ach": "11e608a7d515f1e093242bb2",
          "default_cc": "11e608a442a5f1e092242dda",
          "email_reply_to": "email@domain.com",
          "fax": "3339998822",
          "location_api_id": "location-111111",
          "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
          "location_c1": "custom 1",
          "location_c2": "custom 2",
          "location_c3": "custom data 3",
          "name": "Sample Company Headquarters",
          "office_phone": "2481234567",
          "office_ext_phone": "1021021209",
          "tz": "America/New_York",
          "parent_id": "11e95f8ec39de8fbdb0a4f1a",
          "show_contact_notes": true,
          "show_contact_files": true,
          "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
          "location_type": "merchant",
          "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
        }
      ],
      "primary_location": {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "account_number": "5454545454545454",
        "address": {
          "city": "Novi",
          "state": "MI",
          "postal_code": "48375",
          "country": "US"
        },
        "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "contact_email_trx_receipt_default": true,
        "default_ach": "11e608a7d515f1e093242bb2",
        "default_cc": "11e608a442a5f1e092242dda",
        "email_reply_to": "email@domain.com",
        "fax": "3339998822",
        "location_api_id": "location-111111",
        "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
        "location_c1": "custom 1",
        "location_c2": "custom 2",
        "location_c3": "custom data 3",
        "name": "Sample Company Headquarters",
        "office_phone": "2481234567",
        "office_ext_phone": "1021021209",
        "tz": "America/New_York",
        "parent_id": "11e95f8ec39de8fbdb0a4f1a",
        "show_contact_notes": true,
        "show_contact_files": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_type": "merchant",
        "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
      },
      "received_emails": [
        {
          "subject": "Payment Receipt - 12skiestech",
          "body": "This email is being sent from a server.",
          "source_address": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
          "return_path": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
          "provider_id": "0100017e67bcc530-e1dd23b4-8a39-4a5b-8d5d-68d51c4c942f-000000",
          "domain_id": "11e95f8ec39de8fbdb0a4f1a",
          "reason_sent": "Contact Email",
          "reason_model": "Transaction",
          "reason_model_id": "11e95f8ec39de8fbdb0a4f1a",
          "reply_to": "\"Zeamster\" <emma.p@zeamster.com>",
          "id": "11e95f8ec39de8fbdb0a4f1a",
          "created_ts": 1422040992
        }
      ],
      "contact": {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "account_number": "54545433332",
        "contact_api_id": "137",
        "first_name": "John",
        "last_name": "Smith",
        "cell_phone": "3339998822",
        "balance": 245.36,
        "address": {
          "city": "Novi",
          "state": "Michigan",
          "postal_code": "48375",
          "country": "USA"
        },
        "company_name": "Fortis Payment Systems, LLC",
        "header_message": "This is a sample message for you",
        "date_of_birth": "2021-12-01",
        "email_trx_receipt": true,
        "home_phone": "3339998822",
        "office_phone": "3339998822",
        "office_phone_ext": "5",
        "home_phone_country_code": "+1",
        "office_phone_country_code": "+1",
        "cell_phone_country_code": "+1",
        "header_message_type": 0,
        "update_if_exists": 1,
        "contact_c1": "any",
        "contact_c2": "anything",
        "contact_c3": "something",
        "parent_id": "11e95f8ec39de8fbdb0a4f1a",
        "email": "email@domain.com",
        "token_import_id": "11e95f8ec39de8fbdb0a4f1a",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "active": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
      },
      "isDeletable": true,
      "active_notification_alerts": [
        {
          "location_id": "11e95f8ec39de8fbdb0a4f1a",
          "date_start": "2021-12-01 10:10:00",
          "date_end": "2021-12-01 10:10:00",
          "user_location": true,
          "user_contact": true,
          "include_children": true,
          "alert_type": 1,
          "alert_type_id": 1,
          "id": "11e95f8ec39de8fbdb0a4f1a",
          "created_ts": 1422040992,
          "modified_ts": 1422040992,
          "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
          "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
        }
      ],
      "location_users": [
        {
          "location_id": "11e95f8ec39de8fbdb0a4f1a",
          "user_id": "11e95f8ec39de8fbdb0a4f1a",
          "location_api_id": null,
          "id": "11e95f8ec39de8fbdb0a4f1a",
          "created_ts": 1422040992,
          "modified_ts": 1422040992,
          "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
          "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
        }
      ],
      "auth_roles": [
        {
          "user_id": "11e95f8ec39de8fbdb0a4f1a",
          "auth_role_code": 110,
          "code": 83931,
          "created_ts": 1422040992,
          "modified_ts": 1422040992,
          "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
          "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
        }
      ],
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
      ],
      "resources": {
        "title": "My terminal",
        "priv": null,
        "resource_name": "v2.addons.iframe.get",
        "id": 5693,
        "last_used_date": 1422040992,
        "created_ts": 1422040992,
        "modified_ts": 1422040992
      },
      "domain": {
        "url": "fortispayrbyn9y.sandbox.zeamster.com",
        "title": "Test Brand Domain Title 2",
        "logo": "",
        "support_email": "email@domain.com",
        "allow_contact_signup": true,
        "allow_contact_registration": true,
        "allow_contact_login": true,
        "registration_fields": [
          "account_number"
        ],
        "company_name": null,
        "nav_color": null,
        "button_primary_color": null,
        "logo_background_color": null,
        "icon_background_color": null,
        "menu_text_background_color": null,
        "menu_text_color": null,
        "right_menu_background_color": null,
        "right_menu_text_color": null,
        "button_primary_text_color": null,
        "nav_logo": null,
        "fav_icon": null,
        "aes_key": null,
        "help_text": null,
        "email_reply_to": "email@domain.com",
        "email": "email@domain.com",
        "custom_javascript": null,
        "custom_theme": null,
        "custom_css": null,
        "contact_user_default_entry_page": "dashboard",
        "contact_user_default_auth_roles": [
          null
        ],
        "custom_stylesheet_url": "https://127.0.0.1/receiver",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992
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
      "location_marketplaces": [
        {
          "location_id": "11e95f8ec39de8fbdb0a4f1a",
          "marketplace_id": "11e95f8ec39de8fbdb0a4f1a",
          "location_api_id": null,
          "user_id": "11e95f8ec39de8fbdb0a4f1a",
          "id": "11e95f8ec39de8fbdb0a4f1a",
          "created_ts": 1422040992,
          "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
        }
      ],
      "email_blacklist": {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "isBlacklisted": true,
        "detail": true,
        "created_ts": 1422040992
      },
      "helppage": {
        "user_type_code": 100,
        "body": "Sample Body",
        "title": "Sample Title",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
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


# Send Verification

Send an verification email to the pending user

```php
function sendVerification(string $userId): ResponseSendVerification
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `userId` | `string` | Template, Required | - |

## Response Type

[`ResponseSendVerification`](../../doc/models/response-send-verification.md)

## Example Usage

```php
$userId = 'user_id8';

$result = $usersController->sendVerification($userId);
```

## Example Response *(as JSON)*

```json
{
  "type": "SendVerification",
  "data": {
    "id": "11e95f8ec39de8fbdb0a4f1a",
    "user_id": "11e95f8ec39de8fbdb0a4f1a",
    "hash": "123456781234567812345678",
    "created_ts": 1422040992
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |


# View Self Record

```php
function viewSelfRecord(?array $expand = null, ?array $fields = null): ResponseUser
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `expand` | [`?(string(Expand108Enum)[])`](../../doc/models/expand-108-enum.md) | Query, Optional | Most endpoints in the API have a way to retrieve extra data related to the current record being retrieved. For example, if the API request is for the accountvaults endpoint, and the end user also needs to know which contact the token belongs to, this data can be returned in the accountvaults endpoint request.<br><br>**Constraints**: *Unique Items Required*, *Pattern*: `^[\w]+$` |
| `fields` | [`?(string(Field57Enum)[])`](../../doc/models/field-57-enum.md) | Query, Optional | You can use any `field_name` from this endpoint results to filter the list of fields returned on the response. |

## Response Type

[`ResponseUser`](../../doc/models/response-user.md)

## Example Usage

```php
$result = $usersController->viewSelfRecord();
```

## Example Response *(as JSON)*

```json
{
  "type": "User",
  "data": {
    "account_number": "5454545454545454",
    "branding_domain_url": "{branding_domain_url}",
    "cell_phone": "3339998822",
    "company_name": "Fortis Payment Systems, LLC",
    "contact_id": "Sample contact ID",
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
    "log_api_response_body_ts": 1422040992,
    "locations": [
      {
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "account_number": "5454545454545454",
        "address": {
          "city": "Novi",
          "state": "MI",
          "postal_code": "48375",
          "country": "US"
        },
        "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "contact_email_trx_receipt_default": true,
        "default_ach": "11e608a7d515f1e093242bb2",
        "default_cc": "11e608a442a5f1e092242dda",
        "email_reply_to": "email@domain.com",
        "fax": "3339998822",
        "location_api_id": "location-111111",
        "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
        "location_c1": "custom 1",
        "location_c2": "custom 2",
        "location_c3": "custom data 3",
        "name": "Sample Company Headquarters",
        "office_phone": "2481234567",
        "office_ext_phone": "1021021209",
        "tz": "America/New_York",
        "parent_id": "11e95f8ec39de8fbdb0a4f1a",
        "show_contact_notes": true,
        "show_contact_files": true,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_type": "merchant",
        "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
      }
    ],
    "primary_location": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "account_number": "5454545454545454",
      "address": {
        "city": "Novi",
        "state": "MI",
        "postal_code": "48375",
        "country": "US"
      },
      "branding_domain_id": "11e95f8ec39de8fbdb0a4f1a",
      "contact_email_trx_receipt_default": true,
      "default_ach": "11e608a7d515f1e093242bb2",
      "default_cc": "11e608a442a5f1e092242dda",
      "email_reply_to": "email@domain.com",
      "fax": "3339998822",
      "location_api_id": "location-111111",
      "location_api_key": "AE34BBCAADF4AE34BBCAADF4",
      "location_c1": "custom 1",
      "location_c2": "custom 2",
      "location_c3": "custom data 3",
      "name": "Sample Company Headquarters",
      "office_phone": "2481234567",
      "office_ext_phone": "1021021209",
      "tz": "America/New_York",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "show_contact_notes": true,
      "show_contact_files": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "location_type": "merchant",
      "ticket_hash_key": "A5F443CADF4AE34BBCAADF4"
    },
    "received_emails": [
      {
        "subject": "Payment Receipt - 12skiestech",
        "body": "This email is being sent from a server.",
        "source_address": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "return_path": "\"12skiestech A7t3qi\" <noreply@zeamster.email>",
        "provider_id": "0100017e67bcc530-e1dd23b4-8a39-4a5b-8d5d-68d51c4c942f-000000",
        "domain_id": "11e95f8ec39de8fbdb0a4f1a",
        "reason_sent": "Contact Email",
        "reason_model": "Transaction",
        "reason_model_id": "11e95f8ec39de8fbdb0a4f1a",
        "reply_to": "\"Zeamster\" <emma.p@zeamster.com>",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992
      }
    ],
    "contact": {
      "location_id": "11e95f8ec39de8fbdb0a4f1a",
      "account_number": "54545433332",
      "contact_api_id": "137",
      "first_name": "John",
      "last_name": "Smith",
      "cell_phone": "3339998822",
      "balance": 245.36,
      "address": {
        "city": "Novi",
        "state": "Michigan",
        "postal_code": "48375",
        "country": "USA"
      },
      "company_name": "Fortis Payment Systems, LLC",
      "header_message": "This is a sample message for you",
      "date_of_birth": "2021-12-01",
      "email_trx_receipt": true,
      "home_phone": "3339998822",
      "office_phone": "3339998822",
      "office_phone_ext": "5",
      "home_phone_country_code": "+1",
      "office_phone_country_code": "+1",
      "cell_phone_country_code": "+1",
      "header_message_type": 0,
      "update_if_exists": 1,
      "contact_c1": "any",
      "contact_c2": "anything",
      "contact_c3": "something",
      "parent_id": "11e95f8ec39de8fbdb0a4f1a",
      "email": "email@domain.com",
      "token_import_id": "11e95f8ec39de8fbdb0a4f1a",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "active": true,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
    },
    "isDeletable": true,
    "active_notification_alerts": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "date_start": "2021-12-01 10:10:00",
        "date_end": "2021-12-01 10:10:00",
        "user_location": true,
        "user_contact": true,
        "include_children": true,
        "alert_type": 1,
        "alert_type_id": 1,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "location_users": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "auth_roles": [
      {
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "auth_role_code": 110,
        "code": 83931,
        "created_ts": 1422040992,
        "modified_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
        "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
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
    ],
    "resources": {
      "title": "My terminal",
      "priv": null,
      "resource_name": "v2.addons.iframe.get",
      "id": 5693,
      "last_used_date": 1422040992,
      "created_ts": 1422040992,
      "modified_ts": 1422040992
    },
    "domain": {
      "url": "fortispayrbyn9y.sandbox.zeamster.com",
      "title": "Test Brand Domain Title 2",
      "logo": "",
      "support_email": "email@domain.com",
      "allow_contact_signup": true,
      "allow_contact_registration": true,
      "allow_contact_login": true,
      "registration_fields": [
        "account_number"
      ],
      "company_name": null,
      "nav_color": null,
      "button_primary_color": null,
      "logo_background_color": null,
      "icon_background_color": null,
      "menu_text_background_color": null,
      "menu_text_color": null,
      "right_menu_background_color": null,
      "right_menu_text_color": null,
      "button_primary_text_color": null,
      "nav_logo": null,
      "fav_icon": null,
      "aes_key": null,
      "help_text": null,
      "email_reply_to": "email@domain.com",
      "email": "email@domain.com",
      "custom_javascript": null,
      "custom_theme": null,
      "custom_css": null,
      "contact_user_default_entry_page": "dashboard",
      "contact_user_default_auth_roles": [
        null
      ],
      "custom_stylesheet_url": "https://127.0.0.1/receiver",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992
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
    "location_marketplaces": [
      {
        "location_id": "11e95f8ec39de8fbdb0a4f1a",
        "marketplace_id": "11e95f8ec39de8fbdb0a4f1a",
        "location_api_id": null,
        "user_id": "11e95f8ec39de8fbdb0a4f1a",
        "id": "11e95f8ec39de8fbdb0a4f1a",
        "created_ts": 1422040992,
        "created_user_id": "11e95f8ec39de8fbdb0a4f1a"
      }
    ],
    "email_blacklist": {
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "isBlacklisted": true,
      "detail": true,
      "created_ts": 1422040992
    },
    "helppage": {
      "user_type_code": 100,
      "body": "Sample Body",
      "title": "Sample Title",
      "id": "11e95f8ec39de8fbdb0a4f1a",
      "created_ts": 1422040992,
      "modified_ts": 1422040992,
      "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
      "modified_user_id": "11e95f8ec39de8fbdb0a4f1a"
    }
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |


# Remove Verification

Remove the pending user

```php
function removeVerification(string $userId): ResponseRemoveVerification
```

## Parameters

| Parameter | Type | Tags | Description |
|  --- | --- | --- | --- |
| `userId` | `string` | Template, Required | - |

## Response Type

[`ResponseRemoveVerification`](../../doc/models/response-remove-verification.md)

## Example Usage

```php
$userId = 'user_id8';

$result = $usersController->removeVerification($userId);
```

## Example Response *(as JSON)*

```json
{
  "type": "RemoveVerification",
  "data": {
    "id": "11e95f8ec39de8fbdb0a4f1a",
    "user_id": "11e95f8ec39de8fbdb0a4f1a",
    "hash": "123456781234567812345678",
    "created_ts": 1422040992
  }
}
```

## Errors

| HTTP Status Code | Error Description | Exception Class |
|  --- | --- | --- |
| 401 | Unauthorized | [`Response401tokenException`](../../doc/models/response-401-token-exception.md) |

