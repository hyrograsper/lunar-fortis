<?php

namespace Hyrograsper\LunarFortis\Concerns;

enum ReasonCode: string
{
    public const ENUM_1000 = 'CC - Approved';
    public const ENUM_1001 = 'AuthCompleted';
    public const ENUM_1002 = 'Forced';
    public const ENUM_1003 = 'AuthOnly Declined';
    public const ENUM_1004 = 'Validation Failure (System Run Trx)';
    public const ENUM_1005 = 'Processor Response Invalid';
    public const ENUM_1200 = 'Voided';
    public const ENUM_1201 = 'Partial Approval';
    public const ENUM_1240 = 'Approved, optional fields are missing (Paya ACH only)';
    public const ENUM_1301 = 'Account Deactivated for Fraud';
    // ENUM_1302-1399 - Reserved for Future Fraud Reason Codes
    public const ENUM_1500 = 'Generic Decline';
    public const ENUM_1510 = 'Call';
    public const ENUM_1518 = 'Transaction Not Permitted - Terminal';
    public const ENUM_1520 = 'Pickup Card';
    public const ENUM_1530 = 'Retry Trx';
    public const ENUM_1531 = 'Communication Error';
    public const ENUM_1540 = 'Setup Issue, contact Support';
    public const ENUM_1541 = 'Device is not signature capable';
    public const ENUM_1588 = 'Data could not be de-tokenized';
    public const ENUM_1599 = 'Other Reason';
    public const ENUM_1601 = 'Generic Decline';
    public const ENUM_1602 = 'Call';
    public const ENUM_1603 = 'No Reply';
    public const ENUM_1604 = 'Pickup Card - No Fraud';
    public const ENUM_1605 = 'Pickup Card - Fraud';
    public const ENUM_1606 = 'Pickup Card - Lost';
    public const ENUM_1607 = 'Pickup Card - Stolen';
    public const ENUM_1608 = 'Account Error';
    public const ENUM_1609 = 'Already Reversed';
    public const ENUM_1610 = 'Bad PIN';
    public const ENUM_1611 = 'Cashback Exceeded';
    public const ENUM_1612 = 'Cashback Not Available';
    public const ENUM_1613 = 'CID Error';
    public const ENUM_1614 = 'Date Error';
    public const ENUM_1615 = 'Do Not Honor';
    public const ENUM_1616 = 'NSF';
    public const ENUM_1618 = 'Invalid Service Code';
    public const ENUM_1619 = 'Exceeded activity limit';
    public const ENUM_1620 = 'Violation';
    public const ENUM_1621 = 'Encryption Error';
    public const ENUM_1622 = 'Card Expired';
    public const ENUM_1623 = 'Renter';
    public const ENUM_1624 = 'Security Violation';
    public const ENUM_1625 = 'Card Not Permitted';
    public const ENUM_1626 = 'Trans Not Permitted';
    public const ENUM_1627 = 'System Error';
    public const ENUM_1628 = 'Bad Merchant ID';
    public const ENUM_1629 = 'Duplicate Batch (Already Closed)';
    public const ENUM_1630 = 'Batch Rejected';
    public const ENUM_1631 = 'Account Closed';
    public const ENUM_1632 = 'PIN tries exceeded';
    public const ENUM_1640 = 'Required fields are missing (ACH only)';
    public const ENUM_1641 = 'Previously declined transaction (1640)';
    public const ENUM_1650 = 'Contact Support';
    public const ENUM_1651 = 'Max Sending - Throttle Limit Hit (ACH only)';
    public const ENUM_1652 = 'Max Attempts Exceeded';
    public const ENUM_1653 = 'Contact Support';
    public const ENUM_1654 = 'Voided - Online Reversal Failed';
    public const ENUM_1655 = 'Decline (AVS Auto Reversal)';
    public const ENUM_1656 = 'Decline (CVV Auto Reversal)';
    public const ENUM_1657 = 'Decline (Partial Auth Auto Reversal)';
    public const ENUM_1658 = 'Expired Authorization';
    public const ENUM_1659 = 'Declined - Partial Approval not Supported';
    public const ENUM_1660 = 'Bank Account Error, please delete and re-add Token';
    public const ENUM_1661 = 'Declined AuthIncrement';
    public const ENUM_1662 = 'Auto Reversal - Processor can\'t settle';
    public const ENUM_1663 = 'Manager Needed (Needs override transaction)';
    public const ENUM_1664 = 'Token Not Found: Sharing Group Unavailable';
    public const ENUM_1665 = 'Contact Not Found: Sharing Group Unavailable';
    public const ENUM_1666 = 'Amount Error';
    public const ENUM_1667 = 'Action Not Allowed in Current State';
    public const ENUM_1668 = 'Original Authorization Not Valid';
    public const ENUM_1701 = 'Chip Reject';
    public const ENUM_1800 = 'Incorrect CVV';
    public const ENUM_1801 = 'Duplicate Transaction';
    public const ENUM_1802 = 'MID/TID Not Registered';
    public const ENUM_1803 = 'Stop Recurring';
    public const ENUM_1804 = 'No Transactions in Batch';
    public const ENUM_1805 = 'Batch Does Not Exist';

    public static function fromCode(int $code){

        return constant("self::ENUM_{$code}");
    }
}
