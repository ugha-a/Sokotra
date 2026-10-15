<?php

namespace App\Http\Controllers\Member;

use App\Actions\Trader\SubmitTraderApplication;
use App\Enums\TraderStatus;
use App\Enums\TradingStyle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Member\SubmitTraderApplicationRequest;
use App\Models\Instrument;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BecomeTraderController extends Controller
{
    public function show(Request $request, SubmitTraderApplication $submit): Response|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $profile = $user->traderProfile;
        $kyc = $user->kycVerification;

        if ($profile?->status === TraderStatus::Approved) {
            return to_route('trader.dashboard');
        }

        $blockReason = $submit->blockReason($user);

        return Inertia::render('member/become-trader', [
            'application' => $profile ? [
                'display_name' => $profile->display_name,
                'bio' => $profile->bio,
                'trading_style' => $profile->trading_style->value,
                'experience_years' => $profile->experience_years,
                'main_instruments' => $profile->main_instruments,
                'status' => $profile->status->value,
                'status_label' => $profile->status->label(),
                'rejection_reason' => $profile->rejection_reason,
                'suspension_reason' => $profile->suspension_reason,
                'applied_at' => $profile->applied_at?->toIso8601String(),
            ] : null,
            'kyc' => $kyc ? [
                'full_name' => $kyc->full_name,
                'birth_date' => $kyc->birth_date->toDateString(),
                'status' => $kyc->status->value,
                'status_label' => $kyc->status->label(),
                'rejection_reason' => $kyc->rejection_reason,
            ] : null,
            'canSubmit' => $blockReason === null,
            'blockReason' => $blockReason,
            'instruments' => Instrument::query()->where('is_active', true)->orderBy('category')->orderBy('symbol')->get()
                ->map(fn (Instrument $instrument) => ['value' => $instrument->id, 'label' => $instrument->symbol])
                ->values()->all(),
            'tradingStyles' => TradingStyle::options(),
            'maxFileMb' => Settings::get('kyc.max_file_mb'),
            'maxInstruments' => Settings::get('trader.max_main_instruments'),
            'riskDisclaimer' => Settings::get('content.risk_disclaimer'),
        ]);
    }

    public function store(SubmitTraderApplicationRequest $request, SubmitTraderApplication $submit): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $submit->handle(
            $user,
            $request->safe()->except(['id_card', 'selfie', 'accept_terms']),
            $request->file('id_card'),
            $request->file('selfie'),
            $request->ip(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pengajuan terkirim. Admin akan mereview data kamu.']);

        return to_route('become-trader.show');
    }
}
