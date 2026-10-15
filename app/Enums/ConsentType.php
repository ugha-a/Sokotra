<?php

namespace App\Enums;

enum ConsentType: string
{
    case Terms = 'terms';
    case RiskDisclaimer = 'risk_disclaimer';

    public function label(): string
    {
        return match ($this) {
            self::Terms => 'Syarat & Ketentuan',
            self::RiskDisclaimer => 'Disclaimer Risiko',
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
