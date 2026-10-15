<?php

namespace App\Http\Requests\Trader;

use App\Enums\OrderType;
use App\Enums\SignalDirection;
use App\Enums\SignalVisibility;
use App\Support\ProhibitedClaims;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Shape validation only; price levels are checked against the live quote in PublishSignal.
 */
class PublishSignalRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'instrument_id' => ['required', 'integer', Rule::exists('instruments', 'id')->where('is_active', true)],
            'direction' => ['required', Rule::enum(SignalDirection::class)],
            'order_type' => ['required', Rule::enum(OrderType::class)],
            'entry_price' => ['nullable', 'required_unless:order_type,market', 'numeric', 'gt:0'],
            'stop_loss' => ['required', 'numeric', 'gt:0'],
            'take_profit' => ['required', 'numeric', 'gt:0'],
            'visibility' => ['required', Rule::enum(SignalVisibility::class)],
            'analysis' => ['required', 'string', 'min:'.Settings::get('signal.min_analysis_chars'), 'max:5000'],
            'chart_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.(Settings::get('signal.chart_max_mb') * 1024)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'instrument_id' => 'instrumen',
            'direction' => 'arah',
            'order_type' => 'tipe order',
            'entry_price' => 'entry',
            'stop_loss' => 'Stop Loss',
            'take_profit' => 'Take Profit',
            'visibility' => 'visibilitas',
            'analysis' => 'analisa',
            'chart_image' => 'gambar chart',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['entry_price.required_unless' => 'Entry wajib diisi untuk order limit atau stop.'];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $found = ProhibitedClaims::find((string) $this->input('analysis', ''));

                if ($found !== []) {
                    $validator->errors()->add('analysis', 'Hapus klaim yang dilarang: '.implode(', ', $found).'.');
                }
            },
        ];
    }
}
