<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * Typed, cached access to platform_settings (docs/logic/master-data.md §7).
 */
class Settings
{
    public const CACHE_KEY = 'platform_settings';

    public static function get(string $key): mixed
    {
        $settings = self::all();

        if (! array_key_exists($key, $settings)) {
            throw new InvalidArgumentException("Unknown platform setting [{$key}].");
        }

        return $settings[$key];
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => PlatformSetting::query()
            ->get()
            ->mapWithKeys(fn (PlatformSetting $setting) => [$setting->key => $setting->type->cast($setting->value)])
            ->all());
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
