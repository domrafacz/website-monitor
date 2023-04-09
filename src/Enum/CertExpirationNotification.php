<?php

declare(strict_types=1);

namespace App\Enum;

use DateTimeImmutable;
use DateTimeInterface;

enum CertExpirationNotification: int
{
    case JUST_EXPIRED = 1;
    case EXPIRES_IN_1_DAY = 2;
    case EXPIRES_IN_7_DAYS = 3;

    public static function getReadyToSend(?DateTimeInterface $certExpireTime): ?CertExpirationNotification
    {
        $currentTime = new DateTimeImmutable();

        if ($certExpireTime < $currentTime) {
            return self::JUST_EXPIRED;
        }

        // [days, hours]
        return match ([
            $currentTime->diff($certExpireTime)->d,
            $currentTime->diff($certExpireTime)->h
        ]) {
            [1, 0] => self::EXPIRES_IN_1_DAY,
            [7, 0] => self::EXPIRES_IN_7_DAYS,
            default => null,
        };
    }
}
