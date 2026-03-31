<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::forceCreate([
            'name' => 'Admin',
            'email' => 'admin@travelrequests.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::forceCreate([
            'name' => 'User',
            'email' => 'user@travelrequests.com',
            'email_verified_at' => now(),
            'password' => Hash::make('user123'),
            'role' => 'user',
        ]);
    }
}
