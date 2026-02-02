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
                'password' => bcrypt('123'),
            ]
        );
    }
}
