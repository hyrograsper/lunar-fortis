
# Custom Header Signature



Documentation for accessing and setting credentials for user-id.

## Auth Credentials

| Name | Type | Description | Setter | Getter |
|  --- | --- | --- | --- | --- |
| user-id | `string` | User ID | `userId` | `getUserId()` |



**Note:** Auth credentials can be set using `UserIdCredentialsBuilder::init()` in `userIdCredentials` method in the client builder and accessed through `getUserIdCredentials` method in the client instance.

## Usage Example

### Client Initialization

You must provide credentials in the client as shown in the following code snippet.

```php
$client = FortisAPIClientBuilder::init()
    ->userIdCredentials(
        UserIdCredentialsBuilder::init(
            'user-id'
        )
    )
    ->build();
```


