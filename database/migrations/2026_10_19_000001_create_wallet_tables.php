<?php

use Database\Seeders\MasterDataSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->bigInteger('available_balance')->default(0);
            $table->bigInteger('pending_balance')->default(0);
            $table->bigInteger('locked_balance')->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->restrictOnDelete();
            $table->string('type', 30);
            $table->string('bucket', 10);
            $table->bigInteger('amount');
            $table->bigInteger('balance_after');
            $table->string('reference', 120)->unique();
            $table->string('description')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['wallet_id', 'id']);
            $table->index(['bucket', 'available_at']);
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();
            $table->text('account_number');
            $table->string('account_name', 100);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('bank_account_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('fee');
            $table->unsignedBigInteger('net_amount');
            $table->string('status', 20)->index();
            $table->boolean('name_mismatch')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('disbursement_reference', 100)->nullable();
            $table->timestamps();
        });

        (new MasterDataSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
