<?php

namespace Hyrograsper\LunarFortis\Enums;

enum StatusCode: int
{
    case Approved = 101;
    case AuthOnly = 102;
    case Refunded = 111;
    case Settled = 191;
    case Voided = 201;
    case Declined = 301;
    case ChargeBack = 331;

    public static function isCaptured(?int $statusCode): bool
    {
        return $statusCode === self::Approved->value;
    }

    public static function isRefunded(?int $statusCode): bool
    {
        return $statusCode === self::Refunded->value;
    }

    public static function isSuccessful(?int $statusCode): bool
    {
        return in_array($statusCode, [self::Approved->value, self::AuthOnly->value], true);
    }

    public static function isUnsuccessful(?int $statusCode): bool
    {
        return ! self::isSuccessful($statusCode);
    }
}
