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
            ['email' => 'marmocreativo@gmail.com'],
            [
                'name' => 'Manuel',
                'password' => Hash::make('Angeles1#'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
        );
    }
}
