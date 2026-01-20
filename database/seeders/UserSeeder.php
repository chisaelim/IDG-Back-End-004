<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'chisae0123@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'level' => 'admin',
        ]);
        // Create additional random users
        User::factory(20)->create();
        User::factory(10)->unverified()->create();
    }
}
