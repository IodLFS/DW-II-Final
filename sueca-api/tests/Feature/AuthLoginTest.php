<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TestUserSeeder;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_for_valid_credentials()
    {
        $this->seed(TestUserSeeder::class);

        $response = $this->postJson('/api/login', [
            'email' => '123@gmail.com',
            'password' => '123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
    }

    public function test_login_returns_unauthorized_for_invalid_credentials()
    {
        $this->seed(TestUserSeeder::class);

        $response = $this->postJson('/api/login', [
            'email' => '123@gmail.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Unauthorized']);
    }
}
