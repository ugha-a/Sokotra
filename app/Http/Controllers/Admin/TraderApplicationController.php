<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Kyc\ReviewKyc;
use App\Actions\Trader\ApproveTraderApplication;
use App\Actions\Trader\ReinstateTrader;
use App\Actions\Trader\RejectTraderApplication;
use App\Actions\Trader\SuspendTrader;
use App\Enums\KycStatus;
use App\Enums\TraderStatus;
use App\Http\Controllers\Controller;
use App\Models\Instrument;
use App\Models\KycVerification;
use App\Models\TraderProfile;
use App\Models\User;
use App\Support\Nik;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TraderApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate(['status' => ['nullable', Rule::enum(TraderStatus::class)]]);
        $status = TraderStatus::from($validated['status'] ?? TraderStatus::Applied->value);

        $profiles = TraderProfile::query()
            ->with(['user:id,name,email', 'user.kycVerification:id,user_id,status', 'rank:id,name,color'])
            ->where('status', $status)
            ->latest('applied_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (TraderProfile $profile) => [
                'id' => $profile->id,
                'display_name' => $profile->display_name,
                'user' => ['name' => $profile->user->name, 'email' => $profile->user->email],
                'trading_style' => $profile->trading_style->label(),
                'kyc_status' => $profile->user->kycVerification?->status->value,
                'kyc_status_label' => $profile->user->kycVerification?->status->label(),
                'rank' => $profile->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
                'applied_at' => $profile->applied_at?->toIso8601String(),
            ]);

        $counts = TraderProfile::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('admin/traders/index', [
            'status' => $status->value,
            'statuses' => array_map(fn (TraderStatus $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'count' => (int) ($counts[$case->value] ?? 0),
            ], TraderStatus::cases()),
            'counts' => $counts,
            'profiles' => $profiles,
        ]);
    }

    public function show(TraderProfile $profile): Response
    {
        $profile->load(['user', 'user.kycVerification.reviewer:id,name', 'rank', 'reviewer:id,name']);
        $kyc = $profile->user->kycVerification;
        $expiresAt = now()->addMinutes(Settings::get('kyc.document_link_minutes'));

        return Inertia::render('admin/traders/show', [
            'profile' => [
                'id' => $profile->id,
                'display_name' => $profile->display_name,
                'slug' => $profile->slug,
                'bio' => $profile->bio,
                'trading_style' => $profile->trading_style->label(),
                'experience_years' => $profile->experience_years,
                'main_instruments' => Instrument::whereIn('id', $profile->main_instruments)->orderBy('symbol')->pluck('symbol')->all(),
                'status' => $profile->status->value,
                'status_label' => $profile->status->label(),
                'rank' => $profile->rank ? ['name' => $profile->rank->name, 'color' => $profile->rank->color] : null,
                'applied_at' => $profile->applied_at?->toIso8601String(),
                'approved_at' => $profile->approved_at?->toIso8601String(),
                'reviewed_at' => $profile->reviewed_at?->toIso8601String(),
                'reviewer' => $profile->reviewer?->name,
                'rejection_reason' => $profile->rejection_reason,
                'suspension_reason' => $profile->suspension_reason,
                'user' => ['name' => $profile->user->name, 'email' => $profile->user->email],
            ],
            'kyc' => $kyc ? [
                'id' => $kyc->id,
                'full_name' => $kyc->full_name,
                'nik_masked' => Nik::mask($kyc->nik),
                'birth_date' => $kyc->birth_date->toDateString(),
                'status' => $kyc->status->value,
                'status_label' => $kyc->status->label(),
                'submitted_at' => $kyc->submitted_at?->toIso8601String(),
                'reviewed_at' => $kyc->reviewed_at?->toIso8601String(),
                'reviewer' => $kyc->reviewer?->name,
                'rejection_reason' => $kyc->rejection_reason,
                'documents' => collect(KycVerification::DOCUMENTS)->mapWithKeys(fn (string $column, string $document) => [
                    $document => URL::temporarySignedRoute('admin.kyc.document', $expiresAt, [$kyc, $document]),
                ])->all(),
                'documentLinkMinutes' => Settings::get('kyc.document_link_minutes'),
            ] : null,
            'canApprove' => $profile->status === TraderStatus::Applied && $kyc?->status === KycStatus::Verified,
        ]);
    }

    public function approve(Request $request, TraderProfile $profile, ApproveTraderApplication $approve): RedirectResponse
    {
        $approve->handle($this->admin($request), $profile);

        return $this->backTo($profile, 'Aplikasi trader disetujui.');
    }

    public function reject(Request $request, TraderProfile $profile, RejectTraderApplication $reject): RedirectResponse
    {
        $reject->handle($this->admin($request), $profile, $this->reason($request));

        return $this->backTo($profile, 'Aplikasi trader ditolak.');
    }

    public function suspend(Request $request, TraderProfile $profile, SuspendTrader $suspend): RedirectResponse
    {
        $suspend->handle($this->admin($request), $profile, $this->reason($request));

        return $this->backTo($profile, 'Trader ditangguhkan.');
    }

    public function reinstate(Request $request, TraderProfile $profile, ReinstateTrader $reinstate): RedirectResponse
    {
        $reinstate->handle($this->admin($request), $profile);

        return $this->backTo($profile, 'Trader diaktifkan kembali.');
    }

    public function verifyKyc(Request $request, TraderProfile $profile, ReviewKyc $review): RedirectResponse
    {
        $review->verify($this->admin($request), $this->kycOf($profile));

        return $this->backTo($profile, 'KYC terverifikasi.');
    }

    public function rejectKyc(Request $request, TraderProfile $profile, ReviewKyc $review): RedirectResponse
    {
        $review->reject($this->admin($request), $this->kycOf($profile), $this->reason($request));

        return $this->backTo($profile, 'KYC ditolak.');
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }

    private function reason(Request $request): string
    {
        return $request->validate(
            ['reason' => ['required', 'string', 'max:500']],
            [],
            ['reason' => 'alasan'],
        )['reason'];
    }

    private function kycOf(TraderProfile $profile): KycVerification
    {
        return $profile->user->kycVerification
            ?? throw ValidationException::withMessages(['kyc' => 'Pengguna ini belum mengirim KYC.']);
    }

    private function backTo(TraderProfile $profile, string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route('admin.traders.show', $profile);
    }
}
