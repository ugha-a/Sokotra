<?php

namespace App\Actions\MasterData;

use App\Actions\Audit\RecordAdminAudit;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;

class UpdatePlatformSettings
{
    public function __construct(private RecordAdminAudit $audit) {}

    /**
     * @param  array<string, mixed>  $values  Validated values keyed by setting key.
     */
    public function handle(User $admin, array $values): void
    {
        DB::transaction(function () use ($admin, $values) {
            $old = [];
            $new = [];

            $settings = PlatformSetting::whereIn('key', array_keys($values))->get()->keyBy('key');

            foreach ($values as $key => $value) {
                $setting = $settings[$key];
                $current = $setting->type->cast($setting->value);
                $next = $setting->type->cast($value);

                if ($current === $next) {
                    continue;
                }

                $setting->update(['value' => $next]);
                $old[$key] = $current;
                $new[$key] = $next;
            }

            if ($new !== []) {
                $this->audit->handle($admin, 'settings.updated', null, $old, $new);
            }
        });

        Settings::flush();
    }
}
