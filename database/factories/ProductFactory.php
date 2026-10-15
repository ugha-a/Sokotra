<?php

namespace Database\Factories;

use App\Enums\ProductPlatform;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = 'EA '.fake()->unique()->word().' '.fake()->word();

        return [
            'seller_id' => User::factory(),
            'category_id' => fn () => ProductCategory::where('slug', 'expert-advisor')->value('id'),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'summary' => 'EA trend-following untuk XAUUSD timeframe H1.',
            'description' => 'Masuk saat EMA 50 di atas EMA 200 dan RSI konfirmasi. Selalu memakai SL.',
            'platform' => ProductPlatform::Mt5,
            'price' => 250000,
            'version' => '1.0.0',
            'file_path' => fn (array $attributes) => "products/{$attributes['seller_id']}/ea.ex5",
            'screenshots' => [],
            'status' => ProductStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => ProductStatus::Published, 'submitted_at' => now(), 'published_at' => now()]);
    }
}
