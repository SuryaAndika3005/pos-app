<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@buildpro.test'],
            [
                'name'     => 'Dinda (Admin)',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@buildpro.test'],
            [
                'name'     => 'Surya Andika',
                'password' => Hash::make('password'),
                'role'     => 'cashier',
            ]
        );
    }
}
