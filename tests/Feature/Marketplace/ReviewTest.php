<?php

use App\Enums\Role;
use App\Models\Order;
use App\Models\ProductReview;

beforeEach(function () {
    fakePaymentGateway();
    $this->product = publishedProduct(null, ['price' => 100000]);
    $this->buyer = userWithRoles(Role::Member);
    $this->actingAs($this->buyer)->post(route('orders.store', $this->product), ['accept_risk' => true]);
    payLatestPayment();
    $this->order = Order::firstOrFail();
});

it('lets buyers review once and updates the product rating', function () {
    $this->actingAs($this->buyer)
        ->post(route('purchases.review', $this->order), ['rating' => 4, 'comment' => 'Sinyal entry jelas, setting mudah.'])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->buyer)
        ->post(route('purchases.review', $this->order), ['rating' => 1, 'comment' => 'Lagi'])
        ->assertSessionHasErrors('review');

    expect(ProductReview::count())->toBe(1)
        ->and((float) $this->product->fresh()->rating_avg)->toBe(4.0)
        ->and($this->product->fresh()->rating_count)->toBe(1);
});

it('only lets the buyer review', function () {
    $this->actingAs(userWithRoles(Role::Member))
        ->post(route('purchases.review', $this->order), ['rating' => 5])
        ->assertForbidden();
});

it('validates the rating', function () {
    $this->actingAs($this->buyer)
        ->post(route('purchases.review', $this->order), ['rating' => 6])
        ->assertSessionHasErrors('rating');
});
