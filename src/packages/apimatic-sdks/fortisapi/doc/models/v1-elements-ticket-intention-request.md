
# V1 Elements Ticket Intention Request

## Structure

`V1ElementsTicketIntentionRequest`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `contactId` | `?string` | Optional | Used to associate the Ticket with a Contact.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getContactId(): ?string | setContactId(?string contactId): void |
| `contactApiId` | `?string` | Optional | Used to associate the Ticket with a Contact. | getContactApiId(): ?string | setContactApiId(?string contactApiId): void |
| `locationId` | `string` | Required | A valid Location Id associated with the Contact for this Ticket<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationId(): string | setLocationId(string locationId): void |
| `productTransactionId` | `?string` | Optional | Include a product_transaction_id to respect it's cvv and address field settings when creating a ticket.  These settings are enforced at the ticket creation level only.<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getProductTransactionId(): ?string | setProductTransactionId(?string productTransactionId): void |
| `message` | `?string` | Optional | A custom text message that displays after the ticket is created.<br><br>**Constraints**: *Maximum Length*: `120` | getMessage(): ?string | setMessage(?string message): void |

## Example (as JSON)

```json
{
  "contact_id": "11e95f8ec39de8fbdb0a4f1a",
  "location_id": "11e95f8ec39de8fbdb0a4f1a",
  "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "contact_api_id": "contact_api_id4",
  "message": "message0"
}
```

