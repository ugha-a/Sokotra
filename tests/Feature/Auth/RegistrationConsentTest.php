<?php

use App\Models\User;

it('requires accepting the terms and risk disclaimer to register', function () {
    $this->post(route('register.store'), [
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('accept_terms');

    expect(User::where('email', 'budi@example.com')->exists())->toBeFalse();
});

it('records both consents on registration', function () {
    $this->post(route('register.store'), [
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'accept_terms' => true,
    ]);

    expect(User::where('email', 'budi@example.com')->firstOrFail()->consents()->pluck('type')->map->value->sort()->values()->all())
        ->toBe(['risk_disclaimer', 'terms']);
});
