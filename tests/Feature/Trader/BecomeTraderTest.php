<?php

use App\Enums\KycStatus;
use App\Enums\Role;
use App\Enums\TraderStatus;
use App\Models\Instrument;
use App\Models\KycVerification;
use App\Models\TraderProfile;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
});

function traderApplication(array $overrides = []): array
{
    return [
        'display_name' => 'Rina Swing',
        'bio' => 'Fokus swing trading XAUUSD dengan analisa struktur pasar.',
        'trading_style' => 'swing',
        'experience_years' => 4,
        'main_instruments' => Instrument::whereIn('symbol', ['XAUUSD', 'EURUSD'])->pluck('id')->all(),
        'full_name' => 'Rina Kartika',
        'nik' => '3171234567890123',
        'birth_date' => '1994-05-12',
        'id_card' => UploadedFile::fake()->image('ktp.jpg', 800, 500),
        'selfie' => UploadedFile::fake()->image('selfie.jpg', 600, 800),
        'accept_terms' => true,
        ...$overrides,
    ];
}

function memberUser(): User
{
    return userWithRoles(Role::Member);
}

it('redirects guests to login', function () {
    $this->get(route('become-trader.show'))->assertRedirect(route('login'));
});

it('sends approved traders to their dashboard', function () {
    $trader = memberUser();
    TraderProfile::factory()->for($trader)->approved()->create();
    $trader->assignRole(Role::Trader->value);

    $this->actingAs($trader)->get(route('become-trader.show'))->assertRedirect(route('trader.dashboard'));
});

it('shows the application form to members', function () {
    $this->actingAs(memberUser())
        ->get(route('become-trader.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('member/become-trader')
            ->where('application', null)
            ->where('kyc', null)
            ->where('canSubmit', true)
            ->where('maxFileMb', 5)
            ->has('instruments', 9)
            ->has('tradingStyles', 4));
});

it('submits an application with encrypted KYC and private documents', function () {
    $member = memberUser();

    $this->actingAs($member)
        ->post(route('become-trader.store'), traderApplication())
        ->assertRedirect(route('become-trader.show'))
        ->assertSessionHasNoErrors();

    $profile = TraderProfile::where('user_id', $member->id)->firstOrFail();
    $kyc = KycVerification::where('user_id', $member->id)->firstOrFail();

    expect($profile->status)->toBe(TraderStatus::Applied)
        ->and($profile->slug)->toBe('rina-swing')
        ->and($profile->main_instruments)->toHaveCount(2)
        ->and($profile->applied_at)->not->toBeNull()
        ->and($kyc->status)->toBe(KycStatus::Submitted)
        ->and($kyc->nik)->toBe('3171234567890123')
        ->and(DB::table('kyc_verifications')->value('nik'))->not->toContain('3171234567890123')
        ->and($kyc->nik_hash)->toHaveLength(64)
        ->and($member->fresh()->hasRole(Role::Trader->value))->toBeFalse();

    Storage::disk('local')->assertExists($kyc->id_card_path);
    Storage::disk('local')->assertExists($kyc->selfie_path);
    expect($kyc->id_card_path)->toStartWith("kyc/{$member->id}/");

    expect($member->consents()->pluck('type')->map->value->sort()->values()->all())
        ->toBe(['risk_disclaimer', 'terms']);
});

it('generates a unique slug when the display name is taken', function () {
    TraderProfile::factory()->create(['display_name' => 'Rina Swing', 'slug' => 'rina-swing']);

    $this->actingAs(memberUser())->post(route('become-trader.store'), traderApplication());

    expect(TraderProfile::where('slug', 'rina-swing-2')->exists())->toBeTrue();
});

it('validates the application', function (array $overrides, string $field) {
    $this->actingAs(memberUser())
        ->post(route('become-trader.store'), traderApplication($overrides))
        ->assertSessionHasErrors($field);
})->with([
    'short nik' => [['nik' => '12345'], 'nik'],
    'letters in nik' => [['nik' => '31712345678901AB'], 'nik'],
    'pdf id card' => [['id_card' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf')], 'id_card'],
    'missing selfie' => [['selfie' => null], 'selfie'],
    'terms not accepted' => [['accept_terms' => false], 'accept_terms'],
    'no instruments' => [['main_instruments' => []], 'main_instruments'],
    'unknown style' => [['trading_style' => 'gambling'], 'trading_style'],
    'prohibited claim in bio' => [['bio' => 'Dijamin untung setiap minggu!'], 'bio'],
]);

it('enforces the configured minimum age', function () {
    $this->actingAs(memberUser())
        ->post(route('become-trader.store'), traderApplication([
            'birth_date' => now()->subYears(Settings::get('kyc.min_age'))->addDay()->toDateString(),
        ]))
        ->assertSessionHasErrors('birth_date');
});

it('limits the number of main instruments', function () {
    $this->actingAs(memberUser())
        ->post(route('become-trader.store'), traderApplication([
            'main_instruments' => Instrument::limit(Settings::get('trader.max_main_instruments') + 1)->pluck('id')->all(),
        ]))
        ->assertSessionHasErrors('main_instruments');
});

it('rejects id documents above the configured size', function () {
    $this->actingAs(memberUser())
        ->post(route('become-trader.store'), traderApplication([
            'id_card' => UploadedFile::fake()->image('ktp.jpg')->size(6 * 1024),
        ]))
        ->assertSessionHasErrors('id_card');
});

it('rejects inactive instruments', function () {
    $inactive = Instrument::where('symbol', 'XAGUSD')->firstOrFail();
    $inactive->update(['is_active' => false]);

    $this->actingAs(memberUser())
        ->post(route('become-trader.store'), traderApplication(['main_instruments' => [$inactive->id]]))
        ->assertSessionHasErrors('main_instruments.0');
});

it('allows one NIK per account', function () {
    $this->actingAs(memberUser())->post(route('become-trader.store'), traderApplication());

    $this->actingAs(memberUser())
        ->post(route('become-trader.store'), traderApplication(['nik' => '3171 2345 6789 0123']))
        ->assertSessionHasErrors('nik');
});

it('blocks a second submission while under review', function () {
    $member = memberUser();
    $this->actingAs($member)->post(route('become-trader.store'), traderApplication());

    $this->actingAs($member)
        ->post(route('become-trader.store'), traderApplication(['display_name' => 'Nama Lain']))
        ->assertSessionHasErrors('application');

    expect(TraderProfile::where('user_id', $member->id)->value('display_name'))->toBe('Rina Swing');
});

it('lets members resubmit after their KYC is rejected', function () {
    $member = memberUser();
    $this->actingAs($member)->post(route('become-trader.store'), traderApplication());

    $kyc = KycVerification::where('user_id', $member->id)->firstOrFail();
    $oldIdCard = $kyc->id_card_path;
    $kyc->update(['status' => KycStatus::Rejected, 'rejection_reason' => 'Foto KTP buram.']);

    $this->actingAs($member)
        ->post(route('become-trader.store'), traderApplication(['full_name' => 'Rina Kartika Dewi']))
        ->assertSessionHasNoErrors();

    $kyc->refresh();

    expect($kyc->status)->toBe(KycStatus::Submitted)
        ->and($kyc->full_name)->toBe('Rina Kartika Dewi')
        ->and($kyc->rejection_reason)->toBeNull()
        ->and(KycVerification::where('user_id', $member->id)->count())->toBe(1);

    Storage::disk('local')->assertMissing($oldIdCard);
});

it('enforces the reapply cooldown after a rejected application', function () {
    $member = memberUser();
    $this->actingAs($member)->post(route('become-trader.store'), traderApplication());

    TraderProfile::where('user_id', $member->id)->update([
        'status' => TraderStatus::Rejected,
        'rejection_reason' => 'Pengalaman belum cukup.',
        'reviewed_at' => now(),
    ]);

    $this->actingAs($member)
        ->post(route('become-trader.store'), traderApplication())
        ->assertSessionHasErrors('application');

    $this->travel(8)->days();

    $this->actingAs($member)
        ->post(route('become-trader.store'), traderApplication())
        ->assertSessionHasNoErrors();

    expect(TraderProfile::where('user_id', $member->id)->first()->status)->toBe(TraderStatus::Applied);
});
