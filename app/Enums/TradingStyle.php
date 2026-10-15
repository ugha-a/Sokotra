<?php

namespace App\Enums;

enum TradingStyle: string
{
    case Scalping = 'scalping';
    case Intraday = 'intraday';
    case Swing = 'swing';
    case Position = 'position';

    public function label(): string
    {
        return match ($this) {
            self::Scalping => 'Scalping',
            self::Intraday => 'Intraday',
            self::Swing => 'Swing',
            self::Position => 'Position',
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
