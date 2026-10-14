<?php

namespace App\Enums;

enum Role: string
{
    case Member = 'member';
    case Trader = 'trader';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Member',
            self::Trader => 'Trader',
            self::Admin => 'Admin',
        };
    }
}
