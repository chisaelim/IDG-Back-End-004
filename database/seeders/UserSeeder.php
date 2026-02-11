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
        // Create your test user (admin)
        $main = User::firstOrCreate(
            ['email' => 'chisae0123@gmail.com'],
            [
                'name' => 'Chisae',
                'password' => Hash::make('chisae'),
                'email_verified_at' => now(),
                'level' => 'admin',
            ]
        );

        // Create additional test users for chat interactions
        $users = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'level' => 'user',
            ],
            [
                'name' => 'Bob Smith',
                'email' => 'bob@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'level' => 'user',
            ],
            [
                'name' => 'Charlie Brown',
                'email' => 'charlie@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'level' => 'user',
            ],
            [
                'name' => 'Diana Prince',
                'email' => 'diana@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'level' => 'user',
            ],
            [
                'name' => 'Edward Norton',
                'email' => 'edward@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'level' => 'user',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Generate 1000 additional users using factory
        User::factory()->count(1000)->create([
            'level' => 'user',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Users seeded successfully with 1000+ additional users!');
    }
}
