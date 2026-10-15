<?php

use App\Models\User;

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

it('shows login errors in Indonesian', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah'])
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
});

it('translates starter kit flash messages', function () {
    expect(__('Profile updated.'))->toBe('Profil diperbarui.')
        ->and(__('passwords.sent'))->toBe('Link reset kata sandi sudah dikirim ke email kamu.');
});
