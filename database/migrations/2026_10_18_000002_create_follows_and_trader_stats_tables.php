<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_active_at')->nullable()->index();
            $table->boolean('is_fraud_flagged')->default(false);
        });

        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trader_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->unique(['follower_id', 'trader_id']);
            $table->index('trader_id');
        });

        Schema::create('trader_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period', 10);
            $table->date('period_start')->nullable();
            $table->unsignedInteger('closed_signals')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->decimal('win_rate', 5, 2)->default(0);
            $table->decimal('total_r', 10, 2)->default(0);
            $table->decimal('avg_rr', 6, 2)->default(0);
            $table->decimal('total_pips', 12, 1)->default(0);
            $table->unsignedInteger('max_consecutive_wins')->default(0);
            $table->unsignedInteger('max_consecutive_losses')->default(0);
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('best_streak')->default(0);
            $table->decimal('max_drawdown_r', 10, 2)->default(0);
            $table->decimal('profit_factor', 8, 2)->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->unique(['user_id', 'period']);
            $table->index(['period', 'closed_signals']);
        });

        Schema::create('trader_rank_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->foreignId('to_rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->json('stats');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trader_rank_histories');
        Schema::dropIfExists('trader_stats');
        Schema::dropIfExists('follows');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_active_at', 'is_fraud_flagged']);
        });
    }
};
