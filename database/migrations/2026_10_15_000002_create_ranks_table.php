<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedTinyInteger('level')->unique();
            $table->unsignedInteger('min_closed_signals')->default(0);
            $table->decimal('min_win_rate', 5, 2)->default(0);
            $table->decimal('min_total_r', 8, 2)->default(0);
            $table->unsignedInteger('min_valid_followers')->default(0);
            $table->decimal('max_drawdown_r', 8, 2)->nullable();
            $table->boolean('can_sell_premium')->default(false);
            $table->unsignedBigInteger('subscription_min_price')->nullable();
            $table->unsignedBigInteger('subscription_max_price')->nullable();
            $table->boolean('can_sell_products')->default(false);
            $table->string('color', 20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranks');
    }
};
