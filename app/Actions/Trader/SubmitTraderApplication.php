<?php

namespace App\Actions\Trader;

use App\Enums\ConsentType;
use App\Enums\KycStatus;
use App\Enums\TraderStatus;
use App\Models\ContentPage;
use App\Models\KycVerification;
use App\Models\TraderProfile;
use App\Models\User;
use App\Support\Nik;
use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Member applies to become a trader, together with KYC (docs/logic/trader-onboarding.md).
 */
class SubmitTraderApplication
{
    public const DISK = 'local';

    /**
     * Why this user cannot submit right now, or null when they can.
     */
    public function blockReason(User $user): ?string
    {
        $profile = $this->profileOf($user);

        if ($profile === null) {
            return null;
        }

        return match ($profile->status) {
            TraderStatus::Approved => 'Kamu sudah menjadi trader.',
            TraderStatus::Suspended => 'Akun trader kamu sedang ditangguhkan.',
            TraderStatus::Applied => $this->kycOf($user)?->status === KycStatus::Rejected
                ? null
                : 'Pengajuan kamu sedang direview admin.',
            TraderStatus::Rejected => $this->cooldownReason($profile),
        };
    }

    /**
     * @param  array<string, mixed>  $data  Validated by SubmitTraderApplicationRequest.
     */
    public function handle(User $user, array $data, UploadedFile $idCard, UploadedFile $selfie, ?string $ip): TraderProfile
    {
        if ($reason = $this->blockReason($user)) {
            throw ValidationException::withMessages(['application' => $reason]);
        }

        $previous = $this->kycOf($user);
        $idCardPath = $idCard->store("kyc/{$user->id}", self::DISK);
        $selfiePath = $selfie->store("kyc/{$user->id}", self::DISK);

        try {
            $profile = DB::transaction(function () use ($user, $data, $idCardPath, $selfiePath, $ip) {
                $profile = $this->saveProfile($user, $data);
                $this->saveKyc($user, $data, (string) $idCardPath, (string) $selfiePath);
                $this->recordConsents($user, $ip);

                return $profile;
            });
        } catch (Throwable $exception) {
            Storage::disk(self::DISK)->delete(array_filter([$idCardPath, $selfiePath]));

            throw $exception;
        }

        if ($previous !== null) {
            Storage::disk(self::DISK)->delete([$previous->id_card_path, $previous->selfie_path]);
        }

        return $profile;
    }

    /** Always read current state: the user's cached relations may be stale. */
    private function profileOf(User $user): ?TraderProfile
    {
        return TraderProfile::where('user_id', $user->id)->first();
    }

    private function kycOf(User $user): ?KycVerification
    {
        return KycVerification::where('user_id', $user->id)->first();
    }

    private function cooldownReason(TraderProfile $profile): ?string
    {
        $reapplyAt = ($profile->reviewed_at ?? now())->copy()->addDays(Settings::get('trader.reapply_cooldown_days'));

        return now()->lt($reapplyAt)
            ? 'Kamu bisa mengajukan ulang mulai '.$reapplyAt->translatedFormat('j F Y').'.'
            : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveProfile(User $user, array $data): TraderProfile
    {
        $attributes = [
            'display_name' => $data['display_name'],
            'bio' => $data['bio'],
            'trading_style' => $data['trading_style'],
            'experience_years' => $data['experience_years'],
            'main_instruments' => array_map('intval', $data['main_instruments']),
            'status' => TraderStatus::Applied,
            'applied_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'rejection_reason' => null,
        ];

        $profile = $this->profileOf($user);

        if ($profile !== null) {
            $profile->update($attributes);

            return $profile;
        }

        return TraderProfile::create([
            ...$attributes,
            'user_id' => $user->id,
            'slug' => $this->uniqueSlug($data['display_name']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function saveKyc(User $user, array $data, string $idCardPath, string $selfiePath): void
    {
        KycVerification::updateOrCreate(['user_id' => $user->id], [
            'full_name' => $data['full_name'],
            'nik' => $data['nik'],
            'nik_hash' => Nik::hash($data['nik']),
            'birth_date' => $data['birth_date'],
            'id_card_path' => $idCardPath,
            'selfie_path' => $selfiePath,
            'status' => KycStatus::Submitted,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'rejection_reason' => null,
        ]);
    }

    private function recordConsents(User $user, ?string $ip): void
    {
        $pages = [ConsentType::Terms->value => 'syarat-ketentuan', ConsentType::RiskDisclaimer->value => 'disclaimer-risiko'];

        foreach ($pages as $type => $slug) {
            $user->consents()->create([
                'type' => $type,
                'version' => ContentPage::where('slug', $slug)->first()?->updated_at?->format('YmdHis') ?? 'unversioned',
                'accepted_at' => now(),
                'ip' => $ip,
            ]);
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'trader';
        $slug = $base;

        for ($i = 2; TraderProfile::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
