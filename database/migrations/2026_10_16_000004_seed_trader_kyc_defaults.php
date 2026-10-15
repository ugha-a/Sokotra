<?php

use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds the kyc.review permission and the Trader & KYC settings.
     * Both seeders are idempotent and keep values an admin already changed.
     */
    public function up(): void
    {
        (new RoleSeeder)->run();
        (new MasterDataSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
