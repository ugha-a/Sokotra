<?php

namespace App\Http\Requests\Admin;

use App\Enums\SettingGroup;
use App\Models\PlatformSetting;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class UpdatePlatformSettingsRequest extends FormRequest
{
    /** @var Collection<int, PlatformSetting>|null */
    private ?Collection $definitions = null;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = ['settings' => ['required', 'array']];

        foreach ($this->definitions() as $setting) {
            foreach ($setting->type->rules(array_keys($setting->options ?? [])) as $suffix => $typeRules) {
                $rules["settings.{$setting->key}{$suffix}"] = $suffix === ''
                    ? ['sometimes', ...$typeRules]
                    : $typeRules;
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->definitions()
            ->mapWithKeys(fn (PlatformSetting $setting) => ["settings.{$setting->key}" => $setting->label])
            ->all();
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->rejectUnknownKeys($validator),
            fn (Validator $validator) => $this->requireScoreWeightsToSumToOne($validator),
        ];
    }

    /**
     * Submitted values keyed by setting key, e.g. ['wallet.min_withdraw' => 150000].
     *
     * @return array<string, mixed>
     */
    public function settingValues(): array
    {
        $validated = $this->validated();

        return $this->definitions()
            ->filter(fn (PlatformSetting $setting) => Arr::has($validated, "settings.{$setting->key}"))
            ->mapWithKeys(fn (PlatformSetting $setting) => [
                $setting->key => data_get($validated, "settings.{$setting->key}"),
            ])
            ->all();
    }

    private function rejectUnknownKeys(Validator $validator): void
    {
        $known = $this->definitions()->pluck('key')->all();

        foreach ((array) $this->input('settings', []) as $group => $values) {
            if (SettingGroup::tryFrom((string) $group) === null || ! is_array($values)) {
                $validator->errors()->add('settings', "Grup pengaturan [{$group}] tidak dikenal.");

                continue;
            }

            foreach (array_keys($values) as $name) {
                if (! in_array("{$group}.{$name}", $known, true)) {
                    $validator->errors()->add('settings', "Pengaturan [{$group}.{$name}] tidak dikenal.");
                }
            }
        }
    }

    private function requireScoreWeightsToSumToOne(Validator $validator): void
    {
        $submitted = Arr::where(
            Arr::dot(['score' => (array) $this->input('settings.score', [])]),
            fn ($value, string $key) => str_starts_with($key, 'score.weight_'),
        );

        if ($submitted === []) {
            return;
        }

        $weights = Arr::where(Settings::all(), fn ($value, string $key) => str_starts_with($key, 'score.weight_'));
        $total = array_sum(array_map('floatval', array_merge($weights, $submitted)));

        if (abs($total - 1) > 0.0001) {
            foreach (array_keys($submitted) as $key) {
                $validator->errors()->add("settings.{$key}", 'Total semua bobot skor harus 1,00 (saat ini '.number_format($total, 2, ',', '.').').');
            }
        }
    }

    /**
     * @return Collection<int, PlatformSetting>
     */
    private function definitions(): Collection
    {
        return $this->definitions ??= PlatformSetting::query()->orderBy('sort_order')->get();
    }
}
