<?php

namespace App\Enums;

enum InstrumentCategory: string
{
    case Forex = 'forex';
    case Metal = 'metal';
    case Index = 'index';
    case Crypto = 'crypto';
    case Commodity = 'commodity';

    public function label(): string
    {
        return match ($this) {
            self::Forex => 'Forex',
            self::Metal => 'Logam',
            self::Index => 'Indeks',
            self::Crypto => 'Kripto',
            self::Commodity => 'Komoditas',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
