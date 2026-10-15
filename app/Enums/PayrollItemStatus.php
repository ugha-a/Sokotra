<?php

namespace App\Enums;

enum PayrollItemStatus: string
{
    case Pending = 'pending';
    case Held = 'held';
    case Paid = 'paid';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Held => 'Ditahan',
            self::Paid => 'Dibayar',
            self::Skipped => 'Tidak dibayar',
        };
    }
}
