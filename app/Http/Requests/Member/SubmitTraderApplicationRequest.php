<?php

namespace App\Http\Requests\Member;

use App\Enums\TradingStyle;
use App\Models\KycVerification;
use App\Support\Nik;
use App\Support\ProhibitedClaims;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubmitTraderApplicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('nik'))) {
            $this->merge(['nik' => Nik::normalize($this->input('nik'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxKilobytes = Settings::get('kyc.max_file_mb') * 1024;
        $document = ['required', 'image', 'mimes:jpg,jpeg,png,webp', "max:{$maxKilobytes}"];

        return [
            'display_name' => ['required', 'string', 'min:3', 'max:50'],
            'bio' => ['required', 'string', 'min:30', 'max:1000'],
            'trading_style' => ['required', Rule::enum(TradingStyle::class)],
            'experience_years' => ['required', 'integer', 'between:0,60'],
            'main_instruments' => ['required', 'array', 'min:1', 'max:'.Settings::get('trader.max_main_instruments')],
            'main_instruments.*' => ['integer', 'distinct', Rule::exists('instruments', 'id')->where('is_active', true)],
            'full_name' => ['required', 'string', 'max:100'],
            'nik' => ['required', 'digits:16'],
            'birth_date' => ['required', 'date', 'before_or_equal:'.now()->subYears(Settings::get('kyc.min_age'))->toDateString()],
            'id_card' => $document,
            'selfie' => $document,
            'accept_terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'birth_date.before_or_equal' => 'Usia minimum '.Settings::get('kyc.min_age').' tahun.',
            'nik.digits' => 'NIK harus 16 digit angka.',
            'accept_terms.accepted' => 'Kamu perlu menyetujui syarat & ketentuan dan disclaimer risiko.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'display_name' => 'nama tampilan',
            'bio' => 'bio',
            'trading_style' => 'gaya trading',
            'experience_years' => 'pengalaman',
            'main_instruments' => 'instrumen utama',
            'main_instruments.*' => 'instrumen',
            'full_name' => 'nama sesuai KTP',
            'nik' => 'NIK',
            'birth_date' => 'tanggal lahir',
            'id_card' => 'foto KTP',
            'selfie' => 'selfie dengan KTP',
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $found = ProhibitedClaims::find((string) $this->input('bio', ''));

                if ($found !== []) {
                    $validator->errors()->add('bio', 'Hapus klaim yang dilarang: '.implode(', ', $found).'.');
                }
            },
            function (Validator $validator) {
                $nik = $this->input('nik');

                if (! is_string($nik) || $validator->errors()->has('nik')) {
                    return;
                }

                $takenElsewhere = KycVerification::where('nik_hash', Nik::hash($nik))
                    ->where('user_id', '!=', $this->user()?->id)
                    ->exists();

                if ($takenElsewhere) {
                    $validator->errors()->add('nik', 'NIK ini sudah terdaftar di akun lain.');
                }
            },
        ];
    }
}
