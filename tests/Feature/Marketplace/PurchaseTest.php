<?php

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\Role;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Wallet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    fakePaymentGateway();
    $this->seller = sellerTrader();
    $this->product = publishedProduct($this->seller, ['price' => 350000]);
    $this->buyer = userWithRoles(Role::Member);
});

it('lists only published products publicly', function () {
    Product::factory()->for($this->seller, 'seller')->create(['status' => ProductStatus::Draft]);

    $this->get(route('marketplace.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('public/marketplace/index')->has('products.data', 1)->has('categories'));

    $this->get(route('marketplace.show', $this->product->slug))
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/marketplace/show')
            ->where('product.name', $this->product->name)
            ->where('owned', false));
});

it('hides unpublished products', function () {
    $draft = Product::factory()->for($this->seller, 'seller')->create(['status' => ProductStatus::Draft]);

    $this->get(route('marketplace.show', $draft->slug))->assertNotFound();
});

it('buys a product, then unlocks the license, download and seller income', function () {
    $this->actingAs($this->buyer)
        ->post(route('orders.store', $this->product), ['accept_risk' => true])
        ->assertRedirect(Payment::latest('id')->first()?->checkout_url);

    $order = Order::firstOrFail();
    expect($order->status)->toBe(OrderStatus::PendingPayment)
        ->and($order->commission)->toBe(52500)
        ->and($order->seller_income)->toBe(297500);

    payLatestPayment();
    $order->refresh();

    expect($order->status)->toBe(OrderStatus::Paid)
        ->and($order->license_key)->toMatch('/^SKT(-[A-Z0-9]{4}){4}$/')
        ->and($this->product->fresh()->sales_count)->toBe(1)
        ->and(Wallet::where('user_id', $this->seller->id)->value('pending_balance'))->toBe(297500);

    Storage::disk('local')->put($this->product->file_path, 'ex5-bytes');

    $this->actingAs($this->buyer)->get(route('purchases.download', $order))->assertRedirect();
    $this->actingAs($this->buyer)->get(URL::temporarySignedRoute('purchases.file', now()->addMinutes(10), $order))->assertOk();
    $this->actingAs(userWithRoles(Role::Member))->get(URL::temporarySignedRoute('purchases.file', now()->addMinutes(10), $order))->assertForbidden();
});

it('gives free products immediately', function () {
    $free = publishedProduct($this->seller, ['price' => 0]);

    $this->actingAs($this->buyer)
        ->post(route('orders.store', $free), ['accept_risk' => true])
        ->assertRedirect(route('purchases.index'));

    expect(Order::where('product_id', $free->id)->first()->status)->toBe(OrderStatus::Paid)
        ->and(Payment::count())->toBe(0);
});

it('refuses buying your own, an unpublished or an already owned product', function () {
    $this->actingAs($this->seller)->post(route('orders.store', $this->product), ['accept_risk' => true])->assertSessionHasErrors('order');

    $this->actingAs($this->buyer)->post(route('orders.store', $this->product), ['accept_risk' => true]);
    payLatestPayment();
    $this->actingAs($this->buyer)->post(route('orders.store', $this->product), ['accept_risk' => true])->assertSessionHasErrors('order');

    $draft = Product::factory()->for($this->seller, 'seller')->create(['status' => ProductStatus::Draft]);
    $this->actingAs($this->buyer)->post(route('orders.store', $draft), ['accept_risk' => true])->assertNotFound();
});

it('lists purchases with license keys', function () {
    $this->actingAs($this->buyer)->post(route('orders.store', $this->product), ['accept_risk' => true]);
    payLatestPayment();

    $this->actingAs($this->buyer)
        ->get(route('purchases.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('member/purchases')
            ->has('orders', 1)
            ->where('orders.0.license_key', fn ($key) => str_starts_with((string) $key, 'SKT-')));
});

it('lets admins refund an order while the income is held', function () {
    $this->actingAs($this->buyer)->post(route('orders.store', $this->product), ['accept_risk' => true]);
    payLatestPayment();
    $order = Order::firstOrFail();

    $this->actingAs(adminUser())
        ->post(route('admin.orders.refund', $order), ['reason' => 'File rusak'])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe(OrderStatus::Refunded)
        ->and(Wallet::where('user_id', $this->seller->id)->value('pending_balance'))->toBe(0);

    $this->actingAs($this->buyer)->get(route('purchases.download', $order))->assertForbidden();

    $this->actingAs(adminUser())->get(route('admin.orders.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/orders/index')->has('orders.data', 1));
});
