<?php

namespace Database\Seeders;

use App\Enums\KycStatus;
use App\Enums\Role;
use App\Models\KycVerification;
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

        $applicant = $this->demoUser('Pemohon Demo', 'pemohon@sokotra.test', Role::Member);
        $this->demoKyc($applicant, KycStatus::Submitted);
        TraderProfile::factory()->for($applicant)->create([
            'display_name' => 'Pemohon Demo',
            'slug' => 'pemohon-demo',
            'bio' => 'Swing trader XAUUSD dan EURUSD, fokus pada struktur pasar dan area supply-demand.',
        ]);
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
