<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $demoUsers = [
            ['Admin Sokotra', 'admin@sokotra.test', [Role::Member, Role::Admin]],
            ['Trader Demo', 'trader@sokotra.test', [Role::Member, Role::Trader]],
            ['Member Demo', 'member@sokotra.test', [Role::Member]],
        ];

        foreach ($demoUsers as [$name, $email, $roles]) {
            User::factory()
                ->create(['name' => $name, 'email' => $email])
                ->assignRole(array_map(fn (Role $role) => $role->value, $roles));
        }
    }
}
