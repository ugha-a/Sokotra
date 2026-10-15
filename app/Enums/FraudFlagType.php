<?php

namespace App\Enums;

enum FraudFlagType: string
{
    case Hedging = 'hedging';
    case FollowerFarm = 'follower_farm';

    public function label(): string
    {
        return match ($this) {
            self::Hedging => 'Hedging antar akun',
            self::FollowerFarm => 'Follower palsu',
        };
    }
}
