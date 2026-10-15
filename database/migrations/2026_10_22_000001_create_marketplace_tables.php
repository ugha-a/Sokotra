<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('summary', 255);
            $table->text('description');
            $table->string('platform', 20);
            $table->unsignedBigInteger('price');
            $table->string('version', 20);
            $table->string('file_path')->nullable();
            $table->string('tradingview_url')->nullable();
            $table->json('screenshots');
            $table->string('status', 20)->index();
            $table->text('rejection_reason')->nullable();
            $table->unsignedInteger('sales_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('price_paid');
            $table->unsignedBigInteger('commission');
            $table->unsignedBigInteger('seller_income');
            $table->string('status', 20)->index();
            $table->string('license_key', 40)->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamps();
            $table->index(['buyer_id', 'product_id']);
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
    }
};
