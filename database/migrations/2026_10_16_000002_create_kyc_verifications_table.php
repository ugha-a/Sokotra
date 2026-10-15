<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name', 100);
            $table->text('nik');
            $table->char('nik_hash', 64)->unique();
            $table->date('birth_date');
            $table->string('id_card_path');
            $table->string('selfie_path');
            $table->string('status', 20)->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('kyc_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kyc_verification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document', 20);
            $table->string('ip', 45)->nullable();
            $table->timestamp('accessed_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_access_logs');
        Schema::dropIfExists('kyc_verifications');
    }
};
