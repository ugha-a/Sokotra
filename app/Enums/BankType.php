<?php

namespace App\Enums;

enum BankType: string
{
    case Bank = 'bank';
    case Ewallet = 'ewallet';

    public function label(): string
    {
        return match ($this) {
            self::Bank => 'Bank',
            self::Ewallet => 'E-Wallet',
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
