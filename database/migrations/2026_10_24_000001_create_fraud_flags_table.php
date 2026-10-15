<?php

use Database\Seeders\MasterDataSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_ip', 45)->nullable()->index();
        });

        Schema::create('fraud_flags', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('status', 20)->index();
            $table->json('subject_user_ids');
            $table->json('signal_ids')->nullable();
            $table->json('details')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        (new MasterDataSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_flags');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('registration_ip');
        });
    }
};
