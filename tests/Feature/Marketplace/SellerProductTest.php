<?php

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->seller = sellerTrader();
});

function productPayload(array $overrides = []): array
{
    return [
        'category_id' => ProductCategory::where('slug', 'expert-advisor')->value('id'),
        'name' => 'Gold Trend EA',
        'summary' => 'EA trend-following untuk XAUUSD timeframe H1.',
        'description' => "Masuk saat EMA 50 di atas EMA 200 dan RSI konfirmasi.\nSelalu memakai SL.",
        'platform' => 'mt5',
        'price' => 350000,
        'version' => '1.0.0',
        'file' => UploadedFile::fake()->create('gold-trend.ex5', 120),
        'screenshots' => [UploadedFile::fake()->image('backtest.png', 1200, 700)],
        ...$overrides,
    ];
}

it('creates a draft with a private file and public screenshots', function () {
    $this->actingAs($this->seller)
        ->post(route('trader.products.store'), productPayload())
        ->assertSessionHasNoErrors();

    $product = Product::firstOrFail();

    expect($product->status)->toBe(ProductStatus::Draft)
        ->and($product->slug)->toBe('gold-trend-ea')
        ->and($product->seller_id)->toBe($this->seller->id)
        ->and($product->file_path)->toStartWith("products/{$this->seller->id}/")
        ->and($product->screenshots)->toHaveCount(1);

    Storage::disk('local')->assertExists($product->file_path);
    Storage::disk('public')->assertExists($product->screenshots[0]);
});

it('requires a rank that can sell and verified KYC', function () {
    $newbie = approvedTrader();
    verifiedKyc($newbie);

    $this->actingAs($newbie)
        ->post(route('trader.products.store'), productPayload())
        ->assertSessionHasErrors('product');

    expect(Product::count())->toBe(0);
});

it('validates products', function (array $overrides, string $field) {
    $this->actingAs($this->seller)
        ->post(route('trader.products.store'), productPayload($overrides))
        ->assertSessionHasErrors($field);
})->with([
    'unsupported file' => [['file' => UploadedFile::fake()->create('virus.exe', 10)], 'file'],
    'tradingview needs a url' => [['platform' => 'tradingview', 'file' => null, 'tradingview_url' => 'not a url'], 'tradingview_url'],
    'negative price' => [['price' => -1], 'price'],
]);

it('submits a draft for review and blocks prohibited claims', function () {
    $this->actingAs($this->seller)->post(route('trader.products.store'), productPayload(['summary' => 'EA ini pasti profit setiap hari di XAUUSD.']));
    $product = Product::firstOrFail();

    $this->actingAs($this->seller)
        ->post(route('trader.products.submit', $product))
        ->assertSessionHasErrors('product');

    $product->update(['summary' => 'EA trend-following untuk XAUUSD timeframe H1.']);

    $this->actingAs($this->seller)
        ->post(route('trader.products.submit', $product))
        ->assertSessionHasNoErrors();

    expect($product->fresh()->status)->toBe(ProductStatus::InReview);
});

it('sends content edits of a published product back to review, but not price changes', function () {
    $product = publishedProduct($this->seller);

    $this->actingAs($this->seller)
        ->put(route('trader.products.update', $product), productPayload(['name' => $product->name, 'summary' => $product->summary, 'description' => $product->description, 'file' => null, 'screenshots' => [], 'price' => 400000]))
        ->assertSessionHasNoErrors();

    expect($product->fresh()->status)->toBe(ProductStatus::Published)
        ->and($product->fresh()->price)->toBe(400000);

    $this->actingAs($this->seller)
        ->put(route('trader.products.update', $product), productPayload(['name' => $product->name, 'summary' => 'Ringkasan baru yang lebih jelas.', 'description' => $product->description, 'file' => null, 'screenshots' => [], 'price' => 400000]));

    expect($product->fresh()->status)->toBe(ProductStatus::InReview);
});

it('protects products of other sellers', function () {
    $product = publishedProduct();

    $this->actingAs($this->seller)
        ->put(route('trader.products.update', $product), productPayload())
        ->assertForbidden();
});

it('lists the seller products', function () {
    publishedProduct($this->seller);

    $this->actingAs($this->seller)
        ->get(route('trader.products.index'))
        ->assertInertia(fn (Assert $page) => $page->component('trader/products/index')->has('products', 1)->where('canSell', true));
});
