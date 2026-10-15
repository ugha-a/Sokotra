<?php

namespace App\Enums;

enum ProductPlatform: string
{
    case Mt4 = 'mt4';
    case Mt5 = 'mt5';
    case TradingView = 'tradingview';

    public function label(): string
    {
        return match ($this) {
            self::Mt4 => 'MetaTrader 4',
            self::Mt5 => 'MetaTrader 5',
            self::TradingView => 'TradingView',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases());
    }
}
