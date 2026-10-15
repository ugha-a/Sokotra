<?php

use Database\Seeders\MasterDataSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->morphs('payable');
            $table->unsignedBigInteger('amount');
            $table->string('gateway', 30);
            $table->string('gateway_reference', 120)->unique();
            $table->string('checkout_url', 500)->nullable();
            $table->string('status', 20)->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('channel_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trader_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('monthly_price');
            $table->text('description')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('trader_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('months');
            $table->unsignedBigInteger('price_paid');
            $table->unsignedBigInteger('platform_fee');
            $table->unsignedBigInteger('trader_income');
            $table->string('status', 20)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamps();
            $table->index(['subscriber_id', 'trader_id', 'status']);
        });

        (new MasterDataSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('channel_plans');
        Schema::dropIfExists('payments');
    }
};
