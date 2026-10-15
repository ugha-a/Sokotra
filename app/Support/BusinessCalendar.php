<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Week/month boundaries in WIB (config app.business_timezone), returned in the storage timezone.
 */
class BusinessCalendar
{
    public static function timezone(): string
    {
        return (string) config('app.business_timezone', 'Asia/Jakarta');
    }

    public static function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::timezone());
    }

    public static function startOfWeek(): CarbonImmutable
    {
        return self::now()->startOfWeek();
    }

    public static function startOfMonth(): CarbonImmutable
    {
        return self::now()->startOfMonth();
    }
}
