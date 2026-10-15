<?php

namespace Database\Seeders;

use App\Actions\Ranking\RecalculateRanks;
use App\Actions\Ranking\RefreshTraderStats;
use App\Enums\KycStatus;
use App\Enums\Role;
use App\Enums\SignalStatus;
use App\Models\Broker;
use App\Models\ChannelPlan;
use App\Models\Follow;
use App\Models\Instrument;
use App\Models\KycVerification;
use App\Models\Product;
use App\Models\Rank;
use App\Models\Signal;
use App\Models\TraderProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Local demo data. Every demo account uses the password "password".
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $this->demoUser('Admin Sokotra', 'admin@sokotra.test', Role::Member, Role::Admin);
        $this->demoUser('Member Demo', 'member@sokotra.test', Role::Member);

        $trader = $this->demoUser('Trader Demo', 'trader@sokotra.test', Role::Member, Role::Trader);
        $this->demoKyc($trader, KycStatus::Verified);
        TraderProfile::factory()->approved()->for($trader)->create([
            'display_name' => 'Trader Demo',
            'slug' => 'trader-demo',
        ]);

        $this->demoTrackRecord($trader);

        // Premium channel to try subscriptions with the simulated payment gateway.
        $elite = $this->demoUser('Elite Demo', 'elite@sokotra.test', Role::Member, Role::Trader);
        $this->demoKyc($elite, KycStatus::Verified);
        TraderProfile::factory()->approved()->for($elite)->create([
            'display_name' => 'Elite Demo',
            'slug' => 'elite-demo',
            'rank_id' => Rank::where('slug', 'elite')->value('id'),
        ]);
        ChannelPlan::create(['trader_id' => $elite->id, 'monthly_price' => 150000, 'description' => 'Sinyal premium XAUUSD harian.', 'is_open' => true]);
        Signal::factory()->for($elite, 'trader')->create(['visibility' => 'premium']);

        $product = Product::factory()->for($elite, 'seller')->published()->create([
            'name' => 'Gold Trend EA',
            'slug' => 'gold-trend-ea',
            'price' => 350000,
        ]);
        Storage::disk('local')->put((string) $product->file_path, 'demo-ex5');

        Broker::create([
            'name' => 'Broker Contoh Berjangka',
            'slug' => 'broker-contoh',
            'regulator_license_no' => 'CONTOH/BAPPEBTI/SI/00/0000',
            'website_url' => 'https://example.com',
            'affiliate_url' => 'https://example.com/daftar?ib={ref}',
            'account_types' => [
                ['name' => 'Micro', 'min_deposit' => 1000000, 'spread_from' => 1.2, 'commission' => 0],
                ['name' => 'Standard', 'min_deposit' => 10000000, 'spread_from' => 0.8, 'commission' => 0],
            ],
            'description' => 'Data contoh untuk pengembangan. Ganti dengan broker partner asli yang izinnya sudah diverifikasi.',
            'rebate_per_lot_usd' => 5,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $applicant = $this->demoUser('Pemohon Demo', 'pemohon@sokotra.test', Role::Member);
        $this->demoKyc($applicant, KycStatus::Submitted);
        TraderProfile::factory()->for($applicant)->create([
            'display_name' => 'Pemohon Demo',
            'slug' => 'pemohon-demo',
            'bio' => 'Swing trader XAUUSD dan EURUSD, fokus pada struktur pasar dan area supply-demand.',
        ]);
    }

    /**
     * 30 closed signals over the last weeks plus a few followers, so the leaderboard,
     * profile and dashboard have something to show.
     */
    private function demoTrackRecord(User $trader): void
    {
        $results = [2, -1, 1.5, 2, -1, 2.5, 1.5, -1, 2, 2, -1, -1, 3, 1.5, 2, -1, 2, 1.5, -1, 2, 2, 2, -1, 1.5, 2, -1, 2.5, 2, 1.5, 2];
        $instruments = Instrument::whereIn('symbol', ['EURUSD', 'XAUUSD', 'GBPUSD'])->get();

        foreach ($results as $i => $r) {
            $closedAt = now()->subDays(count($results) - $i)->setTime(9 + $i % 8, 15);
            $instrument = $instruments[$i % $instruments->count()];

            Signal::factory()->for($trader, 'trader')->create([
                'instrument_id' => $instrument->id,
                'pip_size_snapshot' => $instrument->pip_size,
                'digits_snapshot' => $instrument->digits,
                'status' => $r > 0 ? SignalStatus::TpHit : SignalStatus::SlHit,
                'rr' => $r > 0 ? $r : 2,
                'result_r' => $r,
                'result_pips' => $r * 20,
                'published_at' => $closedAt->subHours(5),
                'triggered_at' => $closedAt->subHours(5),
                'closed_at' => $closedAt,
                'entry_price' => $instrument->symbol === 'XAUUSD' ? '2650.00' : '1.08000',
                'stop_loss' => $instrument->symbol === 'XAUUSD' ? '2645.00' : '1.07800',
                'take_profit' => $instrument->symbol === 'XAUUSD' ? '2660.00' : '1.08500',
            ]);
        }

        User::factory()->count(12)->create(['last_active_at' => now()])
            ->each(fn (User $follower) => Follow::create(['follower_id' => $follower->id, 'trader_id' => $trader->id]));

        app(RefreshTraderStats::class)->handle($trader);
        app(RecalculateRanks::class)->handle();
    }

    private function demoUser(string $name, string $email, Role ...$roles): User
    {
        $user = User::factory()->create(['name' => $name, 'email' => $email]);
        $user->assignRole(array_map(fn (Role $role) => $role->value, $roles));

        return $user;
    }

    /**
     * KYC with placeholder images so the admin review page has documents to show.
     */
    private function demoKyc(User $user, KycStatus $status): void
    {
        $kyc = KycVerification::factory()->for($user)->create([
            'full_name' => $user->name,
            'status' => $status,
            'reviewed_at' => $status === KycStatus::Submitted ? null : now(),
        ]);

        Storage::disk('local')->put($kyc->id_card_path, UploadedFile::fake()->image('ktp.jpg', 856, 540)->getContent());
        Storage::disk('local')->put($kyc->selfie_path, UploadedFile::fake()->image('selfie.jpg', 600, 800)->getContent());
    }
}
