
# Device

Contains device information.

Available for supporting EMV 3DS 2.3.1 and later versions.

## Structure

`Device`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `deviceBindingStatus` | [`?string(DeviceBindingStatusEnum)`](../../doc/models/device-binding-status-enum.md) | Optional | Enables the communication of Device Binding Status between the ACS, the DS and the 3DS Requestor. For bound devices (value = 11–14), Device Binding Status also conveys the type of binding that was performed.<br><br>> 01 - Device is not bound by Cardholder<br>> <br>> 02 - Not eligible as determined by Issuer<br>> <br>> 03 - Pending confirmation by Cardholder<br>> <br>> 04 - Cardholder rejected<br>> <br>> 05 - Device Binding Status unknown, unavailable, or does not apply<br>> <br>> 06 through 10 - Reserved for EMVCo future use (values invalid until defined by EMVCo)<br>> <br>> 11 - Device is bound by Cardholder (device is bound using hardware / SIM internal to the Consumer Device. For instance, keys stored in a secure element on the device)<br>> <br>> 12 - Device is bound by Cardholder (device is bound using hardware external to the Consumer Device. For example, an external FIDO Authenticator<br>> <br>> 13 - Device is bound by Cardholder (device is bound using data that includes dynamically generated data and could include a unique device ID)<br>> <br>> 14 - Device is bound by Cardholder (device is bound using static device data that has been obtained from the Consumer Device<br>> <br>> 15 - Device is bound by Cardholder (Other method)<br>> <br>> 16 through 79 - Reserved for EMVCo future use (values invalid until defined by EMVCo)<br>> <br>> 80 through 99 - Reserved for DS use | getDeviceBindingStatus(): ?string | setDeviceBindingStatus(?string deviceBindingStatus): void |

## Example (as JSON)

```json
{
  "device_binding_status": "38"
}
```

