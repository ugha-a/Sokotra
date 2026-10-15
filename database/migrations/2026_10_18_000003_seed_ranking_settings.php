<?php

use Database\Seeders\MasterDataSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds score.leaderboard_min_signals and score.consistent_min_signals (idempotent seeder).
     */
    public function up(): void
    {
        (new MasterDataSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
