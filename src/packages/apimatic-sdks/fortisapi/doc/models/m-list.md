
# M List

## Structure

`MList`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `id` | `?string` | Optional | Batch ID<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getId(): ?string | setId(?string id): void |
| `createdTs` | `?int` | Optional | Created Time Stamp | getCreatedTs(): ?int | setCreatedTs(?int createdTs): void |
| `productTransactionId` | `?string` | Optional | Product Transaction Id<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getProductTransactionId(): ?string | setProductTransactionId(?string productTransactionId): void |
| `processingStatusId` | `?int` | Optional | Processing Status Id<br><br>**Constraints**: `>= 1`, `<= 5` | getProcessingStatusId(): ?int | setProcessingStatusId(?int processingStatusId): void |
| `batchNum` | `?int` | Optional | Batch Number | getBatchNum(): ?int | setBatchNum(?int batchNum): void |
| `isOpen` | `?float` | Optional | Is Open<br><br>**Constraints**: `>= 0`, `<= 1` | getIsOpen(): ?float | setIsOpen(?float isOpen): void |
| `settlementFileName` | `?string` | Optional | Settlement File Name | getSettlementFileName(): ?string | setSettlementFileName(?string settlementFileName): void |
| `batchCloseTs` | `?float` | Optional | Batch Close Ts | getBatchCloseTs(): ?float | setBatchCloseTs(?float batchCloseTs): void |
| `batchCloseDetail` | `?string` | Optional | Batch Close Detail | getBatchCloseDetail(): ?string | setBatchCloseDetail(?string batchCloseDetail): void |
| `totalSaleAmount` | `?int` | Optional | Total Sale Amount | getTotalSaleAmount(): ?int | setTotalSaleAmount(?int totalSaleAmount): void |
| `totalSaleCount` | `?int` | Optional | Total Sale Count | getTotalSaleCount(): ?int | setTotalSaleCount(?int totalSaleCount): void |
| `totalRefundAmount` | `?int` | Optional | Total Refund Amount | getTotalRefundAmount(): ?int | setTotalRefundAmount(?int totalRefundAmount): void |
| `totalRefundCount` | `?int` | Optional | Total Refund Count | getTotalRefundCount(): ?int | setTotalRefundCount(?int totalRefundCount): void |
| `totalVoidAmount` | `?int` | Optional | Total Void Amount | getTotalVoidAmount(): ?int | setTotalVoidAmount(?int totalVoidAmount): void |
| `totalVoidCount` | `?int` | Optional | Total Void Count | getTotalVoidCount(): ?int | setTotalVoidCount(?int totalVoidCount): void |
| `totalBlindRefundAmount` | `?int` | Optional | Total Blind Refund Amount | getTotalBlindRefundAmount(): ?int | setTotalBlindRefundAmount(?int totalBlindRefundAmount): void |
| `totalBlindRefundCount` | `?int` | Optional | Total Blind Refund Count | getTotalBlindRefundCount(): ?int | setTotalBlindRefundCount(?int totalBlindRefundCount): void |
| `changelogs` | [`?(Changelog[])`](../../doc/models/changelog.md) | Optional | Changelog Information on `expand` | getChangelogs(): ?array | setChangelogs(?array changelogs): void |
| `postbackLogs` | [`?(PostbackLog[])`](../../doc/models/postback-log.md) | Optional | Postback Log Information on `expand` | getPostbackLogs(): ?array | setPostbackLogs(?array postbackLogs): void |
| `productTransaction` | [`?ProductTransaction`](../../doc/models/product-transaction.md) | Optional | Product Transaction Information on `expand` | getProductTransaction(): ?ProductTransaction | setProductTransaction(?ProductTransaction productTransaction): void |

## Example (as JSON)

```json
{
  "id": "11e95f8ec39de8fbdb0a4f1a",
  "created_ts": 1422040992,
  "product_transaction_id": "11e95f8ec39de8fbdb0a4f1a",
  "processing_status_id": 2,
  "batch_num": 4,
  "is_open": 0,
  "settlement_file_name": "settement_file.txt",
  "batch_close_ts": 1531423693,
  "batch_close_detail": "BATCH_MISMATCH",
  "total_sale_amount": 2342,
  "total_sale_count": 21,
  "total_refund_amount": 2342,
  "total_refund_count": 18,
  "total_void_amount": 2342,
  "total_void_count": 17,
  "total_blind_refund_amount": 2342,
  "total_blind_refund_count": 16
}
```

