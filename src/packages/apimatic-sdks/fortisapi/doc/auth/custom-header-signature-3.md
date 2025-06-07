
# Custom Header Signature



Documentation for accessing and setting credentials for access-token.

## Auth Credentials

| Name | Type | Description | Setter | Getter |
|  --- | --- | --- | --- | --- |
| access-token | `string` | Access Token | `accessToken` | `getAccessToken()` |



**Note:** Auth credentials can be set using `AccessTokenCredentialsBuilder::init()` in `accessTokenCredentials` method in the client builder and accessed through `getAccessTokenCredentials` method in the client instance.

## Usage Example

### Client Initialization

You must provide credentials in the client as shown in the following code snippet.

```php
$client = FortisAPIClientBuilder::init()
    ->accessTokenCredentials(
        AccessTokenCredentialsBuilder::init(
            'access-token'
        )
    )
    ->build();
```


