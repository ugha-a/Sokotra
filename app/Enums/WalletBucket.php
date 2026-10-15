<?php

namespace App\Enums;

enum WalletBucket: string
{
    case Available = 'available';
    case Pending = 'pending';
    case Locked = 'locked';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Pending => 'Tertahan',
            self::Locked => 'Diproses',
        };
    }

    /** Column on the wallets table holding this bucket's cached balance. */
    public function column(): string
    {
        return "{$this->value}_balance";
    }
}
