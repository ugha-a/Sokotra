<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follower_brackets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('min_followers');
            $table->unsignedInteger('max_followers')->nullable();
            $table->unsignedBigInteger('bonus_amount')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follower_brackets');
    }
};
