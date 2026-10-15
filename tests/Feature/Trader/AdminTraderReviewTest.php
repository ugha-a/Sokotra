<?php

use App\Enums\KycStatus;
use App\Enums\Role;
use App\Enums\TraderStatus;
use App\Models\AdminAuditLog;
use App\Models\KycAccessLog;
use App\Models\KycVerification;
use App\Models\Rank;
use App\Models\TraderProfile;
use App\Notifications\KycReviewed;
use App\Notifications\TraderApplicationReviewed;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    Notification::fake();
});

function pendingApplication(KycStatus $kycStatus = KycStatus::Submitted): TraderProfile
{
    $profile = TraderProfile::factory()->create();
    KycVerification::factory()->for($profile->user)->create(['status' => $kycStatus]);

    return $profile;
}

it('forbids non-admins from the review queue', function () {
    $this->actingAs(userWithRoles(Role::Member, Role::Trader))
        ->get(route('admin.traders.index'))
        ->assertForbidden();
});

it('lists applications filtered by status', function () {
    pendingApplication();
    pendingApplication();
    TraderProfile::factory()->approved()->create();

    $this->actingAs(adminUser())
        ->get(route('admin.traders.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/traders/index')
            ->where('status', 'applied')
            ->has('profiles.data', 2)
            ->has('counts'));

    $this->actingAs(adminUser())
        ->get(route('admin.traders.index', ['status' => 'approved']))
        ->assertInertia(fn (Assert $page) => $page->has('profiles.data', 1));
});

it('shows an application with masked NIK and signed document links', function () {
    $profile = pendingApplication();

    $this->actingAs(adminUser())
        ->get(route('admin.traders.show', $profile))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/traders/show')
            ->where('profile.id', $profile->id)
            ->where('kyc.nik_masked', fn (string $masked) => str_starts_with($masked, '************'))
            ->missing('kyc.nik')
            ->where('kyc.documents.id_card', fn (string $url) => str_contains($url, 'signature='))
            ->where('kyc.documents.selfie', fn (string $url) => str_contains($url, 'signature=')));
});

it('verifies KYC with an audit trail', function () {
    $profile = pendingApplication();
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('admin.traders.kyc.verify', $profile))
        ->assertRedirect(route('admin.traders.show', $profile));

    $kyc = $profile->user->kycVerification;

    expect($kyc->status)->toBe(KycStatus::Verified)
        ->and($kyc->reviewed_by)->toBe($admin->id)
        ->and($kyc->reviewed_at)->not->toBeNull()
        ->and(AdminAuditLog::latest('id')->first()->action)->toBe('kyc.verified');

    Notification::assertSentTo($profile->user, KycReviewed::class);
});

it('requires a reason to reject KYC', function () {
    $profile = pendingApplication();

    $this->actingAs(adminUser())
        ->post(route('admin.traders.kyc.reject', $profile), ['reason' => ''])
        ->assertSessionHasErrors('reason');

    $this->actingAs(adminUser())
        ->post(route('admin.traders.kyc.reject', $profile), ['reason' => 'Foto KTP terpotong.'])
        ->assertSessionHasNoErrors();

    $kyc = $profile->user->kycVerification;

    expect($kyc->status)->toBe(KycStatus::Rejected)
        ->and($kyc->rejection_reason)->toBe('Foto KTP terpotong.');

    Notification::assertSentTo($profile->user, KycReviewed::class);
});

it('cannot approve a trader before KYC is verified', function () {
    $profile = pendingApplication();

    $this->actingAs(adminUser())
        ->post(route('admin.traders.approve', $profile))
        ->assertSessionHasErrors('application');

    expect($profile->fresh()->status)->toBe(TraderStatus::Applied)
        ->and($profile->user->fresh()->hasRole(Role::Trader->value))->toBeFalse();
});

it('approves a trader with the lowest rank and the trader role', function () {
    $profile = pendingApplication(KycStatus::Verified);
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('admin.traders.approve', $profile))
        ->assertRedirect(route('admin.traders.show', $profile));

    $profile->refresh();

    expect($profile->status)->toBe(TraderStatus::Approved)
        ->and($profile->rank_id)->toBe(Rank::orderBy('level')->value('id'))
        ->and($profile->approved_at)->not->toBeNull()
        ->and($profile->reviewed_by)->toBe($admin->id)
        ->and($profile->user->hasRole(Role::Trader->value))->toBeTrue()
        ->and(AdminAuditLog::latest('id')->first()->action)->toBe('trader.approved');

    Notification::assertSentTo($profile->user, TraderApplicationReviewed::class);
});

it('rejects an application with a reason', function () {
    $profile = pendingApplication(KycStatus::Verified);

    $this->actingAs(adminUser())
        ->post(route('admin.traders.reject', $profile), ['reason' => 'Belum ada track record.'])
        ->assertSessionHasNoErrors();

    $profile->refresh();

    expect($profile->status)->toBe(TraderStatus::Rejected)
        ->and($profile->rejection_reason)->toBe('Belum ada track record.')
        ->and($profile->reviewed_at)->not->toBeNull();

    Notification::assertSentTo($profile->user, TraderApplicationReviewed::class);
});

it('suspends and reinstates an approved trader', function () {
    $profile = TraderProfile::factory()->approved()->create();

    $this->actingAs(adminUser())
        ->post(route('admin.traders.suspend', $profile), ['reason' => 'Indikasi hedging antar akun.'])
        ->assertSessionHasNoErrors();

    expect($profile->fresh()->status)->toBe(TraderStatus::Suspended)
        ->and($profile->fresh()->suspension_reason)->toBe('Indikasi hedging antar akun.');

    $this->actingAs(adminUser())->post(route('admin.traders.reinstate', $profile));

    expect($profile->fresh()->status)->toBe(TraderStatus::Approved)
        ->and($profile->fresh()->suspension_reason)->toBeNull();
});

it('only suspends approved traders', function () {
    $profile = pendingApplication();

    $this->actingAs(adminUser())
        ->post(route('admin.traders.suspend', $profile), ['reason' => 'Coba.'])
        ->assertSessionHasErrors('application');
});

describe('KYC documents', function () {
    it('rejects unsigned document links', function () {
        $kyc = pendingApplication()->user->kycVerification;

        $this->actingAs(adminUser())
            ->get(route('admin.kyc.document', [$kyc, 'id_card']))
            ->assertForbidden();
    });

    it('serves signed documents to reviewers and logs the access', function () {
        $kyc = pendingApplication()->user->kycVerification;
        Storage::disk('local')->put($kyc->id_card_path, 'image-bytes');
        $admin = adminUser();

        $this->actingAs($admin)
            ->get(URL::temporarySignedRoute('admin.kyc.document', now()->addMinutes(5), [$kyc, 'id_card']))
            ->assertOk();

        $log = KycAccessLog::latest('id')->first();

        expect($log->user_id)->toBe($admin->id)
            ->and($log->kyc_verification_id)->toBe($kyc->id)
            ->and($log->document)->toBe('id_card');
    });

    it('does not serve documents to non-admins even with a signed link', function () {
        $kyc = pendingApplication()->user->kycVerification;

        $this->actingAs($kyc->user)
            ->get(URL::temporarySignedRoute('admin.kyc.document', now()->addMinutes(5), [$kyc, 'id_card']))
            ->assertForbidden();
    });
});
