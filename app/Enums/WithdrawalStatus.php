<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case Requested = 'requested';
    case Processing = 'processing';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Diajukan',
            self::Processing => 'Diproses',
            self::Paid => 'Dibayar',
            self::Rejected => 'Ditolak',
            self::Failed => 'Gagal',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Requested, self::Processing], true);
    }
}
