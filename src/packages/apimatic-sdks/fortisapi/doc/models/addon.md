
# Addon

## Structure

`Addon`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `title` | `?string` | Optional | Title<br><br>**Constraints**: *Maximum Length*: `24` | getTitle(): ?string | setTitle(?string title): void |
| `secret` | `?string` | Optional | Secret<br><br>**Constraints**: *Maximum Length*: `36` | getSecret(): ?string | setSecret(?string secret): void |
| `iframeUrl` | `?string` | Optional | Iframe URL<br><br>**Constraints**: *Maximum Length*: `512` | getIframeUrl(): ?string | setIframeUrl(?string iframeUrl): void |
| `locationSetupUrl` | `?string` | Optional | Location Setup URL<br><br>**Constraints**: *Maximum Length*: `512` | getLocationSetupUrl(): ?string | setLocationSetupUrl(?string locationSetupUrl): void |
| `userSetupUrl` | `?string` | Optional | User Setup URL<br><br>**Constraints**: *Maximum Length*: `512` | getUserSetupUrl(): ?string | setUserSetupUrl(?string userSetupUrl): void |
| `encryptUrlParams` | `?bool` | Optional | Encrypt URL Params | getEncryptUrlParams(): ?bool | setEncryptUrlParams(?bool encryptUrlParams): void |

## Example (as JSON)

```json
{
  "encrypt_url_params": true,
  "title": "title4",
  "secret": "secret4",
  "iframe_url": "iframe_url4",
  "location_setup_url": "location_setup_url0",
  "user_setup_url": "user_setup_url6"
}
```

