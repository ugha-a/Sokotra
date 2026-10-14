<?php

use Database\Seeders\MasterDataSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Default master data ships with the schema so every environment
     * (production and tests) starts from the values in docs/logic/master-data.md.
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
