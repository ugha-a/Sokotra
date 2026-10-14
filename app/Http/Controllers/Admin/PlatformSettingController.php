<?php

namespace App\Http\Controllers\Admin;

use App\Actions\MasterData\UpdatePlatformSettings;
use App\Enums\SettingGroup;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePlatformSettingsRequest;
use App\Models\PlatformSetting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PlatformSettingController extends Controller
{
    public function edit(): Response
    {
        $settings = PlatformSetting::query()->orderBy('sort_order')->get()->groupBy(fn (PlatformSetting $setting) => $setting->group->value);

        return Inertia::render('admin/settings', [
            'groups' => collect(SettingGroup::cases())->map(fn (SettingGroup $group) => [
                'key' => $group->value,
                'label' => $group->label(),
                'settings' => $settings->get($group->value, collect())->map(fn (PlatformSetting $setting) => [
                    'key' => $setting->key,
                    'name' => str($setting->key)->after('.')->toString(),
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'type' => $setting->type->value,
                    'options' => $setting->options,
                    'value' => $setting->type->cast($setting->value),
                ])->values()->all(),
            ])->all(),
        ]);
    }

    public function update(UpdatePlatformSettingsRequest $request, UpdatePlatformSettings $update): RedirectResponse
    {
        $update->handle($request->user(), $request->settingValues());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pengaturan berhasil disimpan.']);

        return to_route('admin.settings.edit');
    }
}
