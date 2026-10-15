<?php

namespace App\Enums;

enum FraudFlagStatus: string
{
    case Open = 'open';
    case Confirmed = 'confirmed';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Perlu Review',
            self::Confirmed => 'Terbukti',
            self::Dismissed => 'Diabaikan',
        };
    }
}
