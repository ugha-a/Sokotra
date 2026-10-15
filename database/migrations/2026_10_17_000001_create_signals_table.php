<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trader_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('instrument_id')->constrained()->restrictOnDelete();
            $table->decimal('pip_size_snapshot', 18, 5);
            $table->decimal('spread_pips_snapshot', 8, 2);
            $table->unsignedTinyInteger('digits_snapshot');
            $table->string('direction', 4);
            $table->string('order_type', 10);
            $table->decimal('entry_price', 18, 5);
            $table->decimal('stop_loss', 18, 5);
            $table->decimal('take_profit', 18, 5);
            $table->decimal('rr', 8, 2);
            $table->decimal('sl_pips', 10, 1);
            $table->string('visibility', 10);
            $table->text('analysis');
            $table->string('chart_image_path')->nullable();
            $table->string('status', 20);
            $table->timestamp('published_at');
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('close_price', 18, 5)->nullable();
            $table->decimal('result_pips', 10, 1)->nullable();
            $table->decimal('result_r', 8, 2)->nullable();
            $table->boolean('excluded_from_payroll')->default(false);
            $table->string('publish_ip', 45)->nullable();
            $table->char('publish_device_hash', 64)->nullable();
            $table->timestamps();

            $table->index(['status', 'instrument_id']);
            $table->index(['trader_id', 'status']);
            $table->index('published_at');
        });

        Schema::create('signal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signal_id')->constrained()->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('price', 18, 5)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->index(['signal_id', 'id']);
        });

        Schema::create('instrument_feed_cursors', function (Blueprint $table) {
            $table->foreignId('instrument_id')->primary()->constrained()->cascadeOnDelete();
            $table->timestamp('last_candle_at');
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_feed_cursors');
        Schema::dropIfExists('signal_events');
        Schema::dropIfExists('signals');
    }
};
