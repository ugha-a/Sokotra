<?php

use App\Enums\ProductStatus;
use App\Models\AdminAuditLog;
use App\Models\Product;
use App\Notifications\ProductReviewed;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    Notification::fake();
    $this->product = Product::factory()->for(sellerTrader(), 'seller')->create(['status' => ProductStatus::InReview]);
});

it('queues products for admins only', function () {
    $this->actingAs($this->product->seller)->get(route('admin.products.index'))->assertForbidden();

    $this->actingAs(adminUser())
        ->get(route('admin.products.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/products/index')
            ->where('status', 'in_review')
            ->has('products.data', 1)
            ->where('products.data.0.file_url', fn ($url) => str_contains((string) $url, 'signature=')));
});

it('publishes an approved product', function () {
    $this->actingAs(adminUser())->post(route('admin.products.approve', $this->product))->assertSessionHasNoErrors();

    expect($this->product->fresh()->status)->toBe(ProductStatus::Published)
        ->and($this->product->fresh()->published_at)->not->toBeNull()
        ->and(AdminAuditLog::latest('id')->value('action'))->toBe('product.approved');

    Notification::assertSentTo($this->product->seller, ProductReviewed::class);
});

it('rejects with a reason', function () {
    $this->actingAs(adminUser())->post(route('admin.products.reject', $this->product), ['reason' => ''])->assertSessionHasErrors('reason');
    $this->actingAs(adminUser())->post(route('admin.products.reject', $this->product), ['reason' => 'Screenshot tidak sesuai.'])->assertSessionHasNoErrors();

    expect($this->product->fresh()->status)->toBe(ProductStatus::Rejected)
        ->and($this->product->fresh()->rejection_reason)->toBe('Screenshot tidak sesuai.');
});

it('lets admins download the submitted file through a signed link', function () {
    Storage::disk('local')->put($this->product->file_path, 'ex5-bytes');

    $this->actingAs(adminUser())
        ->get(URL::temporarySignedRoute('admin.products.file', now()->addMinutes(5), $this->product))
        ->assertOk();

    $this->actingAs(adminUser())->get(route('admin.products.file', $this->product))->assertForbidden();
});
