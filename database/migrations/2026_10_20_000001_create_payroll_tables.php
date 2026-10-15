<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->char('month', 7)->unique();
            $table->string('status', 20);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('rank_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('eligible');
            $table->json('ineligible_reasons');
            $table->unsignedInteger('closed_signals')->default(0);
            $table->unsignedInteger('best_streak')->default(0);
            $table->decimal('max_drawdown_r', 10, 2)->default(0);
            $table->unsignedBigInteger('base_amount')->default(0);
            $table->unsignedInteger('wins_counted')->default(0);
            $table->unsignedBigInteger('wins_amount')->default(0);
            $table->unsignedInteger('followers_counted')->default(0);
            $table->unsignedBigInteger('followers_amount')->default(0);
            $table->unsignedBigInteger('streak_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('total_amount')->default(0);
            $table->string('status', 20);
            $table->string('hold_note')->nullable();
            $table->timestamps();
            $table->unique(['payroll_period_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_periods');
    }
};
