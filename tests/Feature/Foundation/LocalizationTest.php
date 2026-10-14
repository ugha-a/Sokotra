<?php

it('uses Indonesian as the application locale', function () {
    expect(app()->getLocale())->toBe('id');
});

it('shows validation messages in Indonesian with field labels', function () {
    $this->actingAs(adminUser())
        ->post(route('admin.master.store', 'instruments'), ['symbol' => '', 'pip_size' => 0])
        ->assertSessionHasErrors([
            'symbol' => 'Simbol wajib diisi.',
            'pip_size' => 'Pip size harus lebih besar dari 0.',
        ]);
});
