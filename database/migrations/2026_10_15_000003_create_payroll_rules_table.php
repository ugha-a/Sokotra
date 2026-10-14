<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rank_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('base_salary')->default(0);
            $table->unsignedBigInteger('per_win_amount')->default(0);
            $table->unsignedInteger('required_streak')->default(10);
            $table->unsignedInteger('min_closed_signals_month')->default(0);
            $table->decimal('max_drawdown_r_month', 8, 2)->nullable();
            $table->unsignedBigInteger('streak_bonus_amount')->default(0);
            $table->unsignedInteger('max_weekly_wins_counted')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_rules');
    }
};
