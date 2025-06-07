
# Product Invoice

Product Invoice Information on `expand`

## Structure

`ProductInvoice`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `title` | `?string` | Optional | Title<br><br>**Constraints**: *Maximum Length*: `36` | getTitle(): ?string | setTitle(?string title): void |
| `quoteNumberFormat` | `?string` | Optional | Quote Number Format<br><br>**Constraints**: *Maximum Length*: `36` | getQuoteNumberFormat(): ?string | setQuoteNumberFormat(?string quoteNumberFormat): void |
| `quoteNumberStart` | `?float` | Optional | Quote Number Start<br><br>**Constraints**: `>= 1`, `<= 999999999` | getQuoteNumberStart(): ?float | setQuoteNumberStart(?float quoteNumberStart): void |
| `quoteNumberIncrement` | `?float` | Optional | Quote Number Increment<br><br>**Constraints**: `>= 1`, `<= 999999999` | getQuoteNumberIncrement(): ?float | setQuoteNumberIncrement(?float quoteNumberIncrement): void |
| `quoteNumberCurrent` | `?float` | Optional | Quote Number Current | getQuoteNumberCurrent(): ?float | setQuoteNumberCurrent(?float quoteNumberCurrent): void |
| `invoiceNumberFormat` | `?string` | Optional | Invoice Number Format<br><br>**Constraints**: *Maximum Length*: `36` | getInvoiceNumberFormat(): ?string | setInvoiceNumberFormat(?string invoiceNumberFormat): void |
| `invoiceNumberStart` | `?float` | Optional | Invoice Number Start<br><br>**Constraints**: `>= 1`, `<= 999999999` | getInvoiceNumberStart(): ?float | setInvoiceNumberStart(?float invoiceNumberStart): void |
| `invoiceNumberIncrement` | `?float` | Optional | Invoice Number Increment<br><br>**Constraints**: `>= 1`, `<= 999999999` | getInvoiceNumberIncrement(): ?float | setInvoiceNumberIncrement(?float invoiceNumberIncrement): void |
| `invoiceNumberCurrent` | `?float` | Optional | Invoice Number Current | getInvoiceNumberCurrent(): ?float | setInvoiceNumberCurrent(?float invoiceNumberCurrent): void |
| `taxRate` | `?float` | Optional | Tax Rate<br><br>**Constraints**: `>= 0`, `<= 99.99` | getTaxRate(): ?float | setTaxRate(?float taxRate): void |
| `taxFee` | `?int` | Optional | Tax Fee<br><br>**Constraints**: `<= 999999999999` | getTaxFee(): ?int | setTaxFee(?int taxFee): void |
| `monthlyFee` | `?int` | Optional | Monthly Fees<br><br>**Constraints**: `>= 0`, `<= 99999` | getMonthlyFee(): ?int | setMonthlyFee(?int monthlyFee): void |
| `perInvoiceFee` | `?int` | Optional | Per Invoice Fee<br><br>**Constraints**: `>= 0`, `<= 99999` | getPerInvoiceFee(): ?int | setPerInvoiceFee(?int perInvoiceFee): void |
| `perQuoteFee` | `?int` | Optional | Per Quote fee<br><br>**Constraints**: `>= 0`, `<= 99999` | getPerQuoteFee(): ?int | setPerQuoteFee(?int perQuoteFee): void |
| `requirePayInFull` | `?bool` | Optional | Require Pay In Full | getRequirePayInFull(): ?bool | setRequirePayInFull(?bool requirePayInFull): void |
| `selectable` | `?float` | Optional | Selectable | getSelectable(): ?float | setSelectable(?float selectable): void |
| `reportable` | `?float` | Optional | Reportable | getReportable(): ?float | setReportable(?float reportable): void |
| `portfolioId` | `?string` | Optional | Portfolio Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getPortfolioId(): ?string | setPortfolioId(?string portfolioId): void |
| `locationId` | `?string` | Optional | Location ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getLocationId(): ?string | setLocationId(?string locationId): void |
| `id` | `?string` | Optional | Product Invoice Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `modifiedTs` | `?int` | Optional | Modified Time Stamp | getModifiedTs(): ?int | setModifiedTs(?int modifiedTs): void |
| `createdUserId` | `?string` | Optional | User ID Created the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getCreatedUserId(): ?string | setCreatedUserId(?string createdUserId): void |
| `modifiedUserId` | `?string` | Optional | Last User ID that updated the register<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getModifiedUserId(): ?string | setModifiedUserId(?string modifiedUserId): void |

## Example (as JSON)

```json
{
  "title": "Sample title",
  "quote_number_start": 1.0,
  "quote_number_increment": 1.0,
  "quote_number_current": 1.0,
  "invoice_number_start": 1,
  "invoice_number_increment": 1,
  "invoice_number_current": 1,
  "tax_rate": 0,
  "tax_fee": 0,
  "monthly_fee": 0,
  "per_invoice_fee": 0,
  "per_quote_fee": 0,
  "require_pay_in_full": true,
  "selectable": 1,
  "reportable": 1,
  "portfolio_id": "11e95f8ec39de8fbdb0a4f1a",
  "location_id": "11e95f8ec39de8fbdb0a4f1a",
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "modified_ts": 1422040992,
  "created_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "modified_user_id": "11e95f8ec39de8fbdb0a4f1a",
  "quote_number_format": "quote_number_format6"
}
```

