
# Custom Header Signature



Documentation for accessing and setting credentials for developer-id.

## Auth Credentials

| Name | Type | Description | Setter | Getter |
|  --- | --- | --- | --- | --- |
| developer-id | `string` | Developer ID | `developerId` | `getDeveloperId()` |



**Note:** Auth credentials can be set using `DeveloperIdCredentialsBuilder::init()` in `developerIdCredentials` method in the client builder and accessed through `getDeveloperIdCredentials` method in the client instance.

## Usage Example

### Client Initialization

You must provide credentials in the client as shown in the following code snippet.

```php
$client = FortisAPIClientBuilder::init()
    ->developerIdCredentials(
        DeveloperIdCredentialsBuilder::init(
            'developer-id'
        )
    )
    ->build();
```


