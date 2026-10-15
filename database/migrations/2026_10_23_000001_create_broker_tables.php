<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_id')->constrained()->cascadeOnDelete();
            $table->string('ref_code', 20)->unique();
            $table->timestamp('clicked_at');
        });

        Schema::create('broker_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('broker_id')->constrained()->restrictOnDelete();
            $table->string('account_login', 40);
            $table->string('account_type', 50)->nullable();
            $table->string('status', 20)->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->unique(['broker_id', 'account_login']);
        });

        Schema::create('broker_rebates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained()->restrictOnDelete();
            $table->foreignId('broker_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('account_login', 40);
            $table->char('period', 7)->index();
            $table->decimal('lots', 12, 2);
            $table->decimal('rebate_usd', 12, 2);
            $table->decimal('fx_rate', 12, 2);
            $table->unsignedBigInteger('rebate_idr');
            $table->timestamps();
            $table->unique(['broker_id', 'account_login', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_rebates');
        Schema::dropIfExists('broker_accounts');
        Schema::dropIfExists('broker_referrals');
    }
};
