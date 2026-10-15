<?php

namespace App\Enums;

enum SettingGroup: string
{
    case Signal = 'signal';
    case Score = 'score';
    case Wallet = 'wallet';
    case Subscription = 'subscription';
    case Marketplace = 'marketplace';
    case Content = 'content';
    case Trader = 'trader';
    case Fraud = 'fraud';
    case Payment = 'payment';

    public function label(): string
    {
        return match ($this) {
            self::Signal => 'Aturan Sinyal',
            self::Score => 'Skor & Leaderboard',
            self::Wallet => 'Wallet',
            self::Subscription => 'Subscription',
            self::Marketplace => 'Marketplace',
            self::Content => 'Konten',
            self::Trader => 'Trader & KYC',
            self::Fraud => 'Anti-Fraud',
            self::Payment => 'Rekening Pembayaran',
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
