
# Terminal Timeouts 12

The following options outlines some configurable timeout values that can be used to customize the experience at the terminal for the cardholder.

> These timeouts are specific to Ingenico devices only.

## Structure

`TerminalTimeouts12`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `cardEntryTimeout` | `?int` | Optional | How long to wait for input from cardholder.<br><br>> Message on timeout:<br>> Card Entry Timeout<br><br>**Constraints**: `>= 20`, `<= 120` | getCardEntryTimeout(): ?int | setCardEntryTimeout(?int cardEntryTimeout): void |
| `deviceTermsPromptTimeout` | `?int` | Optional | How long the terms will be displayed on the device.<br><br>> Message on timeout:<br>> Timeout waiting for Customer<br><br>**Constraints**: `>= 5`, `<= 300` | getDeviceTermsPromptTimeout(): ?int | setDeviceTermsPromptTimeout(?int deviceTermsPromptTimeout): void |
| `overallTimeout` | `?int` | Optional | How long to wait for response from /v2/routertransactions endpoint.<br><br>> Message on timeout:<br>> Overall Request Timeout<br><br>**Constraints**: `>= 30`, `<= 300` | getOverallTimeout(): ?int | setOverallTimeout(?int overallTimeout): void |
| `pinEntryTimeout` | `?int` | Optional | How long to wait for pin entry by cardholder.<br><br>> Message on timeout:<br>> Pin Entry Timeout<br><br>**Constraints**: `>= 20`, `<= 50` | getPinEntryTimeout(): ?int | setPinEntryTimeout(?int pinEntryTimeout): void |
| `signatureInputTimeout` | `?int` | Optional | How long to wait for first "touch" to signature.<br><br>> Message on timeout:<br>> Signature Input Timeout<br><br>**Constraints**: `>= 10`, `<= 50` | getSignatureInputTimeout(): ?int | setSignatureInputTimeout(?int signatureInputTimeout): void |
| `signatureSubmitTimeout` | `?int` | Optional | How long to wait for signature to be submitted.<br><br>> Message on timeout:<br>> Signature Storage Timeout<br><br>**Constraints**: `>= 20`, `<= 50` | getSignatureSubmitTimeout(): ?int | setSignatureSubmitTimeout(?int signatureSubmitTimeout): void |
| `statusDisplayTime` | `?int` | Optional | How long the approve/decline status message stays on screen.<br><br>> Message on timeout:<br>> N/A - Not actually a "timeout".  This is a time to display the status on the screen.<br><br>**Constraints**: `>= 1`, `<= 30` | getStatusDisplayTime(): ?int | setStatusDisplayTime(?int statusDisplayTime): void |
| `tipCashbackTimeout` | `?int` | Optional | How long to wait for input on a tip or cashback screen.<br><br>> Message on timeout:<br>> Tip/Cashback Timeout<br><br>**Constraints**: `>= 20`, `<= 50` | getTipCashbackTimeout(): ?int | setTipCashbackTimeout(?int tipCashbackTimeout): void |
| `transactionTimeout` | `?int` | Optional | How long to wait for response from the processor.<br><br>> Message on timeout:<br>> Transaction Timeout<br><br>**Constraints**: `>= 10`, `<= 20` | getTransactionTimeout(): ?int | setTransactionTimeout(?int transactionTimeout): void |

## Example (as JSON)

```json
{
  "card_entry_timeout": 47,
  "device_terms_prompt_timeout": 30,
  "overall_timeout": 125,
  "pin_entry_timeout": 40,
  "signature_input_timeout": 35,
  "signature_submit_timeout": 38,
  "status_display_time": 12,
  "tip_cashback_timeout": 25,
  "transaction_timeout": 17
}
```

