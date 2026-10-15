<?php

use Database\Seeders\MasterDataSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds payment.* platform settings for manual bank transfer.
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
