
# Batch Risk Config

Batch Risk Config

## Structure

`BatchRiskConfig`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `blindRefundTotalCount` | `?int` | Optional | Blind Refund Total Count<br><br>**Constraints**: `>= 0`, `<= 999999999` | getBlindRefundTotalCount(): ?int | setBlindRefundTotalCount(?int blindRefundTotalCount): void |
| `blindRefundMaxAmount` | `?int` | Optional | Blind Refund Max Amount<br><br>**Constraints**: `>= 0`, `<= 999999999` | getBlindRefundMaxAmount(): ?int | setBlindRefundMaxAmount(?int blindRefundMaxAmount): void |

## Example (as JSON)

```json
{
  "blind_refund_total_count": 110,
  "blind_refund_max_amount": 172
}
```

