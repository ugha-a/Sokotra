<?php

namespace App\Enums;

enum SignalDirection: string
{
    case Buy = 'buy';
    case Sell = 'sell';

    public function label(): string
    {
        return match ($this) {
            self::Buy => 'Buy',
            self::Sell => 'Sell',
        };
    }

    /** +1 for buy, −1 for sell: multiply a price move to get the trade result. */
    public function sign(): int
    {
        return $this === self::Buy ? 1 : -1;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases());
    }
}
