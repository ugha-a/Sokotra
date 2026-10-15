<?php

use Database\Seeders\MasterDataSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds signal.min_analysis_chars and signal.chart_max_mb (idempotent seeder).
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
