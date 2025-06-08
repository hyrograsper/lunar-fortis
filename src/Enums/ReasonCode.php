<?php

namespace Hyrograsper\LunarFortis\Enums;

enum ReasonCode: string
{
    public const string ENUM_1000 = 'CC - Approved';
    public const string ENUM_1001 = 'AuthCompleted';
    public const string ENUM_1002 = 'Forced';
    public const string ENUM_1003 = 'AuthOnly Declined';
    public const string ENUM_1004 = 'Validation Failure (System Run Trx)';
    public const string ENUM_1005 = 'Processor Response Invalid';
    public const string ENUM_1200 = 'Voided';
    public const string ENUM_1201 = 'Partial Approval';
    public const string ENUM_1240 = 'Approved, optional fields are missing (Paya ACH only)';
    public const string ENUM_1301 = 'Account Deactivated for Fraud';
    // ENUM_1302-1399 - Reserved for Future Fraud Reason Codes
    public const string ENUM_1500 = 'Generic Decline';
    public const string ENUM_1510 = 'Call';
    public const string ENUM_1518 = 'Transaction Not Permitted - Terminal';
    public const string ENUM_1520 = 'Pickup Card';
    public const string ENUM_1530 = 'Retry Trx';
    public const string ENUM_1531 = 'Communication Error';
    public const string ENUM_1540 = 'Setup Issue, contact Support';
    public const string ENUM_1541 = 'Device is not signature capable';
    public const string ENUM_1588 = 'Data could not be de-tokenized';
    public const string ENUM_1599 = 'Other Reason';
    public const string ENUM_1601 = 'Generic Decline';
    public const string ENUM_1602 = 'Call';
    public const string ENUM_1603 = 'No Reply';
    public const string ENUM_1604 = 'Pickup Card - No Fraud';
    public const string ENUM_1605 = 'Pickup Card - Fraud';
    public const string ENUM_1606 = 'Pickup Card - Lost';
    public const string ENUM_1607 = 'Pickup Card - Stolen';
    public const string ENUM_1608 = 'Account Error';
    public const string ENUM_1609 = 'Already Reversed';
    public const string ENUM_1610 = 'Bad PIN';
    public const string ENUM_1611 = 'Cashback Exceeded';
    public const string ENUM_1612 = 'Cashback Not Available';
    public const string ENUM_1613 = 'CID Error';
    public const string ENUM_1614 = 'Date Error';
    public const string ENUM_1615 = 'Do Not Honor';
    public const string ENUM_1616 = 'NSF';
    public const string ENUM_1618 = 'Invalid Service Code';
    public const string ENUM_1619 = 'Exceeded activity limit';
    public const string ENUM_1620 = 'Violation';
    public const string ENUM_1621 = 'Encryption Error';
    public const string ENUM_1622 = 'Card Expired';
    public const string ENUM_1623 = 'Renter';
    public const string ENUM_1624 = 'Security Violation';
    public const string ENUM_1625 = 'Card Not Permitted';
    public const string ENUM_1626 = 'Trans Not Permitted';
    public const string ENUM_1627 = 'System Error';
    public const string ENUM_1628 = 'Bad Merchant ID';
    public const string ENUM_1629 = 'Duplicate Batch (Already Closed)';
    public const string ENUM_1630 = 'Batch Rejected';
    public const string ENUM_1631 = 'Account Closed';
    public const string ENUM_1632 = 'PIN tries exceeded';
    public const string ENUM_1640 = 'Required fields are missing (ACH only)';
    public const string ENUM_1641 = 'Previously declined transaction (1640)';
    public const string ENUM_1650 = 'Contact Support';
    public const string ENUM_1651 = 'Max Sending - Throttle Limit Hit (ACH only)';
    public const string ENUM_1652 = 'Max Attempts Exceeded';
    public const string ENUM_1653 = 'Contact Support';
    public const string ENUM_1654 = 'Voided - Online Reversal Failed';
    public const string ENUM_1655 = 'Decline (AVS Auto Reversal)';
    public const string ENUM_1656 = 'Decline (CVV Auto Reversal)';
    public const string ENUM_1657 = 'Decline (Partial Auth Auto Reversal)';
    public const string ENUM_1658 = 'Expired Authorization';
    public const string ENUM_1659 = 'Declined - Partial Approval not Supported';
    public const string ENUM_1660 = 'Bank Account Error, please delete and re-add Token';
    public const string ENUM_1661 = 'Declined AuthIncrement';
    public const string ENUM_1662 = 'Auto Reversal - Processor can\'t settle';
    public const string ENUM_1663 = 'Manager Needed (Needs override transaction)';
    public const string ENUM_1664 = 'Token Not Found: Sharing Group Unavailable';
    public const string ENUM_1665 = 'Contact Not Found: Sharing Group Unavailable';
    public const string ENUM_1666 = 'Amount Error';
    public const string ENUM_1667 = 'Action Not Allowed in Current State';
    public const string ENUM_1668 = 'Original Authorization Not Valid';
    public const string ENUM_1701 = 'Chip Reject';
    public const string ENUM_1800 = 'Incorrect CVV';
    public const string ENUM_1801 = 'Duplicate Transaction';
    public const string ENUM_1802 = 'MID/TID Not Registered';
    public const string ENUM_1803 = 'Stop Recurring';
    public const string ENUM_1804 = 'No Transactions in Batch';
    public const string ENUM_1805 = 'Batch Does Not Exist';

    public static function fromCode(int $code){

        return constant("self::ENUM_{$code}");
    }
}
