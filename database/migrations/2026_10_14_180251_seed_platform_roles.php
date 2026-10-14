<?php

use Database\Seeders\RoleSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Roles are required for registration, so they ship with the schema.
     */
    public function up(): void
    {
        (new RoleSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
