<?php

namespace App\Enums;

enum BrokerAccountStatus: string
{
    case Submitted = 'submitted';
    case Linked = 'linked';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Menunggu Verifikasi',
            self::Linked => 'Terhubung',
            self::Rejected => 'Ditolak',
        };
    }
}
