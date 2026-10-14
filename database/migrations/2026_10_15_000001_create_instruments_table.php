<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instruments', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique();
            $table->string('name');
            $table->string('category', 20);
            $table->decimal('pip_size', 18, 5);
            $table->unsignedTinyInteger('digits');
            $table->decimal('spread_pips', 8, 2)->default(0);
            $table->decimal('min_sl_pips', 8, 2)->default(0);
            $table->string('feed_symbol')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instruments');
    }
};
