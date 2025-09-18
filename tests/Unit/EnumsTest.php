<?php

use Hyrograsper\LunarFortis\Enums\AvsResponseCode;
use Hyrograsper\LunarFortis\Enums\CvvResponseCode;
use Hyrograsper\LunarFortis\Enums\ReasonCode;
use Hyrograsper\LunarFortis\Enums\StatusCode;

describe('StatusCode Enum', function () {
    it('has correct status code values', function () {
        expect(StatusCode::Approved->value)->toBe(101);
        expect(StatusCode::AuthOnly->value)->toBe(102);
        expect(StatusCode::Refunded->value)->toBe(111);
        expect(StatusCode::Settled->value)->toBe(191);
        expect(StatusCode::Voided->value)->toBe(201);
        expect(StatusCode::Declined->value)->toBe(301);
        expect(StatusCode::ChargeBack->value)->toBe(331);
    });

    it('correctly identifies captured transactions', function () {
        expect(StatusCode::isCaptured(101))->toBeTrue(); // Approved
        expect(StatusCode::isCaptured(102))->toBeFalse(); // Auth Only
        expect(StatusCode::isCaptured(111))->toBeFalse(); // Refunded
        expect(StatusCode::isCaptured(301))->toBeFalse(); // Declined
        expect(StatusCode::isCaptured(999))->toBeFalse(); // Invalid code
    });

    it('correctly identifies refunded transactions', function () {
        expect(StatusCode::isRefunded(111))->toBeTrue(); // Refunded
        expect(StatusCode::isRefunded(101))->toBeFalse(); // Approved
        expect(StatusCode::isRefunded(102))->toBeFalse(); // Auth Only
        expect(StatusCode::isRefunded(301))->toBeFalse(); // Declined
        expect(StatusCode::isRefunded(999))->toBeFalse(); // Invalid code
    });

    it('correctly identifies successful transactions', function () {
        expect(StatusCode::isSuccessful(101))->toBeTrue(); // Approved
        expect(StatusCode::isSuccessful(102))->toBeTrue(); // Auth Only
        expect(StatusCode::isSuccessful(111))->toBeFalse(); // Refunded
        expect(StatusCode::isSuccessful(301))->toBeFalse(); // Declined
        expect(StatusCode::isSuccessful(999))->toBeFalse(); // Invalid code
    });

    it('correctly identifies unsuccessful transactions', function () {
        expect(StatusCode::isUnsuccessful(301))->toBeTrue(); // Declined
        expect(StatusCode::isUnsuccessful(201))->toBeTrue(); // Voided
        expect(StatusCode::isUnsuccessful(331))->toBeTrue(); // ChargeBack
        expect(StatusCode::isUnsuccessful(101))->toBeFalse(); // Approved
        expect(StatusCode::isUnsuccessful(102))->toBeFalse(); // Auth Only
    });
});

describe('ReasonCode Enum', function () {
    it('has correct reason code constants', function () {
        expect(ReasonCode::ENUM_1000)->toBe('CC - Approved');
        expect(ReasonCode::ENUM_1001)->toBe('AuthCompleted');
        expect(ReasonCode::ENUM_1003)->toBe('AuthOnly Declined');
        expect(ReasonCode::ENUM_1500)->toBe('Generic Decline');
        expect(ReasonCode::ENUM_1540)->toBe('Setup Issue, contact Support');
        expect(ReasonCode::ENUM_1622)->toBe('Card Expired');
        expect(ReasonCode::ENUM_1800)->toBe('Incorrect CVV');
    });

    it('retrieves reason code by numeric code', function () {
        expect(ReasonCode::fromCode(1000))->toBe('CC - Approved');
        expect(ReasonCode::fromCode(1001))->toBe('AuthCompleted');
        expect(ReasonCode::fromCode(1500))->toBe('Generic Decline');
        expect(ReasonCode::fromCode(1622))->toBe('Card Expired');
        expect(ReasonCode::fromCode(1800))->toBe('Incorrect CVV');
        expect(ReasonCode::fromCode(1801))->toBe('Duplicate Transaction');
    });

    it('returns null for unknown reason codes', function () {
        expect(ReasonCode::fromCode(9999))->toBeNull();
        expect(ReasonCode::fromCode(0))->toBeNull();
        expect(ReasonCode::fromCode(-1))->toBeNull();
        expect(ReasonCode::fromCode(500))->toBeNull();
    });

    it('correctly identifies approved transactions', function () {
        expect(ReasonCode::isApproved(1000))->toBeTrue(); // CC - Approved
        expect(ReasonCode::isApproved(1001))->toBeFalse(); // AuthCompleted (different)
        expect(ReasonCode::isApproved(1500))->toBeFalse(); // Generic Decline
        expect(ReasonCode::isApproved(9999))->toBeFalse(); // Unknown code
    });

    it('correctly identifies authorized transactions', function () {
        expect(ReasonCode::isAuthorized(1001))->toBeTrue(); // AuthCompleted
        expect(ReasonCode::isAuthorized(1000))->toBeFalse(); // CC - Approved (different)
        expect(ReasonCode::isAuthorized(1500))->toBeFalse(); // Generic Decline
        expect(ReasonCode::isAuthorized(9999))->toBeFalse(); // Unknown code
    });

    it('handles decline codes correctly', function () {
        expect(ReasonCode::fromCode(1500))->toBe('Generic Decline');
        expect(ReasonCode::fromCode(1615))->toBe('Do Not Honor');
        expect(ReasonCode::fromCode(1616))->toBe('NSF');
        expect(ReasonCode::fromCode(1622))->toBe('Card Expired');
        expect(ReasonCode::fromCode(1625))->toBe('Card Not Permitted');
    });

    it('handles fraud-related codes correctly', function () {
        expect(ReasonCode::fromCode(1301))->toBe('Account Deactivated for Fraud');
        expect(ReasonCode::fromCode(1605))->toBe('Pickup Card - Fraud');
        expect(ReasonCode::fromCode(1624))->toBe('Security Violation');
    });

    it('handles system error codes correctly', function () {
        expect(ReasonCode::fromCode(1531))->toBe('Communication Error');
        expect(ReasonCode::fromCode(1540))->toBe('Setup Issue, contact Support');
        expect(ReasonCode::fromCode(1627))->toBe('System Error');
        expect(ReasonCode::fromCode(1650))->toBe('Contact Support');
    });

    it('handles ACH-specific codes correctly', function () {
        expect(ReasonCode::fromCode(1240))->toBe('Approved, optional fields are missing (Paya ACH only)');
        expect(ReasonCode::fromCode(1640))->toBe('Required fields are missing (ACH only)');
        expect(ReasonCode::fromCode(1651))->toBe('Max Sending - Throttle Limit Hit (ACH only)');
    });
});

describe('AVS Response Code Enum', function () {
    it('exists and can be instantiated', function () {
        // Basic test to ensure the enum exists
        expect(enum_exists(AvsResponseCode::class))->toBeTrue();
    });
});

describe('CVV Response Code Enum', function () {
    it('exists and can be instantiated', function () {
        // Basic test to ensure the enum exists
        expect(enum_exists(CvvResponseCode::class))->toBeTrue();
    });
});

describe('ReasonCode Edge Cases', function () {
    it('handles boundary conditions', function () {
        // Test some boundary values
        expect(ReasonCode::fromCode(1000))->not->toBeNull(); // First defined constant
        expect(ReasonCode::fromCode(999))->toBeNull(); // Just below first
        expect(ReasonCode::fromCode(1805))->not->toBeNull(); // Last defined constant
        expect(ReasonCode::fromCode(1806))->toBeNull(); // Just above last
    });

    it('handles special character codes correctly', function () {
        expect(ReasonCode::fromCode(1662))->toBe('Auto Reversal - Processor can\'t settle');
        expect(ReasonCode::fromCode(1663))->toBe('Manager Needed (Needs override transaction)');
    });

    it('validates reserved code ranges', function () {
        // Test that the documented reserved range (1302-1399) returns null
        expect(ReasonCode::fromCode(1302))->toBeNull();
        expect(ReasonCode::fromCode(1350))->toBeNull();
        expect(ReasonCode::fromCode(1399))->toBeNull();
    });
});

describe('StatusCode Edge Cases', function () {
    it('handles null and invalid inputs gracefully', function () {
        // These methods should handle invalid inputs without throwing errors
        expect(StatusCode::isCaptured(0))->toBeFalse();
        expect(StatusCode::isRefunded(-1))->toBeFalse();
        expect(StatusCode::isSuccessful(99999))->toBeFalse();
        expect(StatusCode::isUnsuccessful(99999))->toBeTrue(); // Inverse of isSuccessful
    });

    it('validates status code logic consistency', function () {
        // Test that isSuccessful and isUnsuccessful are logical opposites
        $testCodes = [101, 102, 111, 191, 201, 301, 331, 999];

        foreach ($testCodes as $code) {
            expect(StatusCode::isSuccessful($code))->toBe(! StatusCode::isUnsuccessful($code));
        }
    });

    it('validates captured vs successful logic', function () {
        // All captured transactions should be successful, but not all successful are captured
        expect(StatusCode::isCaptured(101))->toBeTrue();
        expect(StatusCode::isSuccessful(101))->toBeTrue();

        expect(StatusCode::isCaptured(102))->toBeFalse(); // Auth only - successful but not captured
        expect(StatusCode::isSuccessful(102))->toBeTrue();
    });
});
