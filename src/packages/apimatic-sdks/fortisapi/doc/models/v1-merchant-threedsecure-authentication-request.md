
# V1 Merchant Threedsecure Authentication Request

## Structure

`V1MerchantThreedsecureAuthenticationRequest`

## Fields

| Name | Type | Tags | Description | Getter | Setter |
|  --- | --- | --- | --- | --- | --- |
| `productTransactionId` | `string` | Required | Product Transaction ID to associate with this 3DS request<br><br>**Constraints**: *Pattern*: `^(([0-9a-fA-F\-]{24,36})\|(([0-9a-fA-F]{8})-(([0-9a-fA-F]{4}\-){3})([0-9a-fA-F]{12})))$` | getProductTransactionId(): string | setProductTransactionId(string productTransactionId): void |
| `preferredProtocolVersion` | [`?string(PreferredProtocolVersionEnum)`](../../doc/models/preferred-protocol-version-enum.md) | Optional | Specifies the preferred version of 3D Secure protocol to be utilized while executing 3D Secure authentication. 3DS Server initiates an authentication request with the preferred version and if this version is not supported by other 3D Secure components, it falls back to the next supported version(s) and continues authentication.<br>If the preferred version is enforced by setting  #enforcePreferredProtocolVersion flag, but this version is not supported by one of the 3D Secure components, 3DS Server does not initiate an authentication and provides corresponding error message to the customer.<br>If no value is provided, EMV 3DS 2.1.0 version will be used by default.<br><br>> 2.1.0 - prefer authentication with 2.1.0 version<br>> <br>> 2.2.0 - prefer authentication with 2.2.0 version<br>> <br>> 2.3.1 - prefer authentication with 2.3.1 version | getPreferredProtocolVersion(): ?string | setPreferredProtocolVersion(?string preferredProtocolVersion): void |
| `enforcePreferredProtocolVersion` | `?bool` | Optional | Boolean flag that enforces preferred 3D Secure protocol version to be used in 3D Secure authentication. The value should be set true to enforce preferred version. If value is false or not provided, 3DS Server can fall back to next supported 3DS protocol version while initiating 3D Secure authentication. | getEnforcePreferredProtocolVersion(): ?bool | setEnforcePreferredProtocolVersion(?bool enforcePreferredProtocolVersion): void |
| `deviceChannel` | [`string(DeviceChannelEnum)`](../../doc/models/device-channel-enum.md) | Required | Indicates the type of channel interface being used to initiate the transaction.<br><br>> 02 - Browser (BRW)<br>> <br>> 03 - 3DS Requestor Initiated (3RI) | getDeviceChannel(): string | setDeviceChannel(string deviceChannel): void |
| `messageCategory` | [`string(MessageCategoryEnum)`](../../doc/models/message-category-enum.md) | Required | Identifies the category of the message for a specific use case.<br><br>> 01 - Payment Authentication (PA)<br>> <br>> 02 - Non-Payment Authentication (NPA)<br>> <br>> 80 through 99 - PS Specific Values<br>> <br>> 80 - MasterCard Identity Check Insights<br>> <br>> 85 - MasterCard Identity Check, Production Validation PA<br>> <br>> 86 - MasterCard Identity Check, Production Validation NPA | getMessageCategory(): string | setMessageCategory(string messageCategory): void |
| `threeDsCompInd` | `?string` | Optional | Indicates whether the 3DS Method successfully completed. The value is used only when device_channel = 02 (Browser).<br><br>From protocol version 2.3.1 this field is required.<br><br>> Y - Successfully completed<br>> <br>> N - Did not successfully complete<br>> <br>> U - Unavailable - 3DS Method URL was not present in the PRes message data for the card range associated with the Cardholder Account Number. | getThreeDsCompInd(): ?string | setThreeDsCompInd(?string threeDsCompInd): void |
| `threeDsMethodId` | `?string` | Optional | Contains the 3DS Server Transaction ID used during the previous execution of the 3DS method. Accepted value length is 36 characters. Accepted value is a Canonical format as defined in IETF RFC 4122. May utilise any of the specified versions if the output meets specified requirements.<br>This field is required if the 3DS Requestor reuses previous 3DS Method execution with device_channel = 02 (BRW). Available in EMV 3DS 2.3.1 and later versions. | getThreeDsMethodId(): ?string | setThreeDsMethodId(?string threeDsMethodId): void |
| `threeDsRequestor` | [`ThreeDsRequestor`](../../doc/models/three-ds-requestor.md) | Required | Contains information for the 3DS Requestor. | getThreeDsRequestor(): ThreeDsRequestor | setThreeDsRequestor(ThreeDsRequestor threeDsRequestor): void |
| `threeDsServerTransId` | `?string` | Optional | Universally unique transaction identifier assigned by the 3DS Server to identify a single transaction. This value has 36 characters in a format defined in IETF RFC 4122. In case 3DS Method is previously invoked, the threeDSServerTransID should be sent. If not, 3DS Server will generate a new transaction identifier.<br><br>If threeDSServerTransID is generated in versioning flow same transaction identifier should be used in Authentication flow which combines them as one transaction. | getThreeDsServerTransId(): ?string | setThreeDsServerTransId(?string threeDsServerTransId): void |
| `threeDsRequestorUrl` | `?string` | Optional | Fully qualified URL of 3DS Requestor website or customer care site. | getThreeDsRequestorUrl(): ?string | setThreeDsRequestorUrl(?string threeDsRequestorUrl): void |
| `cardholderAccount` | [`CardholderAccount`](../../doc/models/cardholder-account.md) | Required | Contains information for the Cardholder Account. | getCardholderAccount(): CardholderAccount | setCardholderAccount(CardholderAccount cardholderAccount): void |
| `cardholder` | [`?Cardholder`](../../doc/models/cardholder.md) | Optional | Contains information for the Cardholder. This field is required unless market or regional mandate restricts sending this information. | getCardholder(): ?Cardholder | setCardholder(?Cardholder cardholder): void |
| `purchase` | [`?Purchase`](../../doc/models/purchase.md) | Optional | Contains purchase information | getPurchase(): ?Purchase | setPurchase(?Purchase purchase): void |
| `broadInfo` | [`?BroadInfo`](../../doc/models/broad-info.md) | Optional | Until EMV 3DS 2.2.0:<br><br>Unstructured information sent between the 3DS Server, the DS and the ACS.<br><br>This field is not required to be filled by the Requestor and the requirements for the presence of this field are DS specific.<br><br>Starting from EMV 3DS 2.3.1:<br><br>Structured information sent between the 3DS Server, the DS and the ACS. 2.3.1 structure is defined below. Accepted value length is maximum 4096 characters. This field is optional. | getBroadInfo(): ?BroadInfo | setBroadInfo(?BroadInfo broadInfo): void |
| `messageExtension` | [`?(MessageExtension[])`](../../doc/models/message-extension.md) | Optional | Data necessary to support requirements not otherwise defined in the 3D Secure message are carried in a Message Extension. This field is limited to 81,920 characters and it is used in the Authentication Request.<br><br>Requirements of this field are set by each Directory Server. | getMessageExtension(): ?array | setMessageExtension(?array messageExtension): void |
| `challengeMessageExtension` | [`?(ChallengeMessageExtension[])`](../../doc/models/challenge-message-extension.md) | Optional | Data necessary to support requirements not otherwise defined in the 3D Secure message are carried in a Message Extension. This field is limited to 81,920 characters and it is used in the generating of the ChallengeRequest, if challenge is needed.<br><br>Requirements of this field are set by each Directory Server. | getChallengeMessageExtension(): ?array | setChallengeMessageExtension(?array challengeMessageExtension): void |
| `browserInformation` | [`?BrowserInformation`](../../doc/models/browser-information.md) | Optional | Contains browser information.<br><br>This field is required when device_channel=02 (BRW). | getBrowserInformation(): ?BrowserInformation | setBrowserInformation(?BrowserInformation browserInformation): void |
| `threeRiInd` | [`?string(ThreeRiIndEnum)`](../../doc/models/three-ri-ind-enum.md) | Optional | Indicates the type of 3RI request. This data element provides additional information to the ACS to determine the best approach for handling a 3RI request.<br><br>Values 06 through 12 are accepted as well if 3DS Server initiates authentication with EMV 3DS 2.2.0 version or greater (required protocol version can be set in the preferred_protocol_version field).<br><br>Starting from EMV 3DS 2.3.1:<br>Required if device_channel = 03 and message_category = 01 or 02.<br>Values 13 and 14 can be used.<br><br>> 01 - Recurring transaction<br>> <br>> 02 - Installment transaction<br>> <br>> 03 - Add card<br>> <br>> 04 - Maintain card information<br>> <br>> 05 - Account verification<br>> <br>> 06 - Split/delayed shipment (EMV 3DS 2.2.0 version or greater)<br>> <br>> 07 - Top-up (EMV 3DS 2.2.0 version or greater)<br>> <br>> 08 - Mail order (EMV 3DS 2.2.0 version or greater)<br>> <br>> 09 - Telephone order (EMV 3DS 2.2.0 version or greater)<br>> <br>> 10 - Whitelist status check (EMV 3DS 2.2.0 version or greater)<br>> <br>> 11 - Other payment (EMV 3DS 2.2.0 version or greater)<br>> <br>> 12 - Billing agreement (EMV 3DS 2.2.0 version or greater)<br>> <br>> 13 - Device Binding status check (EMV 3DS 2.3.1 version or greater)<br>> <br>> 14 - Card Security Code status check (EMV 3DS 2.3.1 version or greater)<br>> <br>> 80 through 99 - PS-specific values, regardless of protocol version | getThreeRiInd(): ?string | setThreeRiInd(?string threeRiInd): void |
| `device` | [`?Device`](../../doc/models/device.md) | Optional | Contains device information.<br><br>Available for supporting EMV 3DS 2.3.1 and later versions. | getDevice(): ?Device | setDevice(?Device device): void |
| `multiTransaction` | [`?MultiTransaction`](../../doc/models/multi-transaction.md) | Optional | Additional transaction information in case of multiple transactions or merchants.<br><br>Available for supporting EMV 3DS 2.3.1 and later versions. | getMultiTransaction(): ?MultiTransaction | setMultiTransaction(?MultiTransaction multiTransaction): void |
| `deviceId` | `?string` | Optional | Unique and immutable identifier linked to a device that is consistent across 3DS transactions for the specific user device.<br><br>Available for supporting EMV 3DS 2.3.1 and later versions. | getDeviceId(): ?string | setDeviceId(?string deviceId): void |
| `userId` | `?string` | Optional | Identifier of the transacting user’s Browser Account ID.<br><br>Available for supporting EMV 3DS 2.3.1 and later versions. | getUserId(): ?string | setUserId(?string userId): void |
| `payeeOrigin` | `?string` | Optional | The origin of the payee that will be provided in the SPC Transaction Data as a fully qualified URL.<br><br>**Constraints**: *Maximum Length*: `2048` | getPayeeOrigin(): ?string | setPayeeOrigin(?string payeeOrigin): void |

## Example (as JSON)

```json
{
  "product_transaction_id": "11ee3860e2fc7f5ea67d36b3",
  "preferred_protocol_version": "2.2.0",
  "enforce_preferred_protocol_version": true,
  "device_channel": "02",
  "message_category": "01",
  "three_ds_comp_ind": "Y",
  "three_ds_requestor": {
    "three_ds_requestor_authentication_ind": "01",
    "three_ds_requestor_challenge_ind": [
      "03"
    ],
    "three_ds_requestor_authentication_info": [
      {
        "three_ds_req_auth_method": "98",
        "three_ds_req_auth_timestamp": "three_ds_req_auth_timestamp2",
        "three_ds_req_auth_data": "three_ds_req_auth_data2"
      },
      {
        "three_ds_req_auth_method": "98",
        "three_ds_req_auth_timestamp": "three_ds_req_auth_timestamp2",
        "three_ds_req_auth_data": "three_ds_req_auth_data2"
      }
    ],
    "three_ds_requestor_prior_authentication_info": [
      {
        "three_ds_req_prior_ref": "three_ds_req_prior_ref6",
        "three_ds_req_prior_auth_method": "90",
        "three_ds_req_prior_auth_timestamp": "three_ds_req_prior_auth_timestamp6",
        "three_ds_req_prior_auth_data": "three_ds_req_prior_auth_data0"
      }
    ],
    "three_ds_requestor_dec_req_ind": "F",
    "three_ds_requestor_dec_max_time": 246
  },
  "cardholder_account": {
    "expire_date": "2508",
    "account_number": "5454545454545454",
    "scheme_id": "Visa",
    "account_info": {
      "ch_acc_age_ind": "02",
      "ch_acc_date": "ch_acc_date4",
      "ch_acc_change_ind": "03",
      "ch_acc_change": "ch_acc_change2",
      "ch_acc_pw_change_ind": "02"
    },
    "account_id": "account_id0",
    "cvv": "cvv6"
  },
  "three_ds_method_id": "three_ds_method_id4",
  "three_ds_server_trans_id": "00001b58-0000-0000-0000-000000000000"
}
```

