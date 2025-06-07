
# Result

## Structure

`Result`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `clientAppId` | `?string` | Optional | Client Issues Id to track that can be used to track each submitted merchant application. This id should be generated and sent in the request payload, and will be returned in the response payload. If no id is submitted in the payload request, this field will be null in the response.<br><br>**Constraints**: *Maximum Length*: `50` | getClientAppId(): ?string | setClientAppId(?string clientAppId): void |
| `dbaName` | `?string` | Optional | Merchant 'Doing Business As' name.<br><br>**Constraints**: *Maximum Length*: `100` | getDbaName(): ?string | setDbaName(?string dbaName): void |
| `email` | `?string` | Optional | Merchant email address.<br><br>**Constraints**: *Maximum Length*: `100` | getEmail(): ?string | setEmail(?string email): void |

## Example (as JSON)

```json
{
  "client_app_id": "ABC123",
  "dba_name": "Discount Home Goods",
  "email": "jtodd@example.com"
}
```

