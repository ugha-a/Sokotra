<?php

use App\Support\Settings;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds landing.* platform settings for landing page content customization.
     */
    public function up(): void
    {
        (new MasterDataSeeder)->run();
        Settings::flush();
    }

    public function down(): void
    {
        //
    }
};
