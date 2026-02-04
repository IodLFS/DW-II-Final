<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class TestUserSeeder extends Seeder
{
    
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => '123@gmail.com'],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => bcrypt('123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'username' => 'testuser123',
                'password' => bcrypt('password123'),
            ]
        );
    }
}
