<?php

namespace App\Enums;

enum SignalStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case TpHit = 'tp_hit';
    case SlHit = 'sl_hit';
    case ClosedTimeout = 'closed_timeout';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Entry',
            self::Active => 'Berjalan',
            self::TpHit => 'Kena TP',
            self::SlHit => 'Kena SL',
            self::ClosedTimeout => 'Ditutup (Waktu Habis)',
            self::Expired => 'Kedaluwarsa',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, self::open(), true);
    }

    /** Final states that count towards statistics (docs/logic/scoring-ranking.md). */
    public function isScored(): bool
    {
        return in_array($this, [self::TpHit, self::SlHit, self::ClosedTimeout], true);
    }

    /**
     * @return list<self>
     */
    public static function open(): array
    {
        return [self::Pending, self::Active];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases());
    }
}
