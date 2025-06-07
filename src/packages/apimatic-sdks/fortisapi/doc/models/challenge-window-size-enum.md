
# Challenge Window Size Enum

Dimensions of the challenge window that has been displayed to the Cardholder. The ACS shall reply with content that is formatted to appropriately render in this window to provide the best possible user experience.

Preconfigured sizes are width X height in pixels of the window displayed in the Cardholder browser window. This is used only to prepare the CReq request and it is not part of the AReq flow. If not present it will be omitted.

However, when sending the Challenge Request, this field is required when device_channel = 02 (BRW).

> 01 - 250 x 400
> 
> 02 - 390 x 400
> 
> 03 - 500 x 600
> 
> 04 - 600 x 400
> 
> 05 - Full screen

## Enumeration

`ChallengeWindowSizeEnum`

## Fields

| Name |
|  --- |
| `ENUM_01` |
| `ENUM_02` |
| `ENUM_03` |
| `ENUM_04` |
| `ENUM_05` |

## Example

```
01
```

