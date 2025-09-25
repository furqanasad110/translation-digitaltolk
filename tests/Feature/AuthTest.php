<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_login_and_me()
    {
        // Register
        $payload = [
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => 'password123'
        ];

        $this
            ->postJson('/api/register', $payload)
            ->assertStatus(201)
            ->assertJsonStructure(['token']);

        // Login with same credentials
        $this->postJson('/api/login', [
            'email' => 'alice@example.test',
            'password' => 'password123'
        ])->assertStatus(200)->assertJsonStructure(['token']);

        // Login with wrong password
        $this->postJson('/api/login', [
            'email' => 'alice@example.test',
            'password' => 'wrong'
        ])->assertStatus(401);

        // create user and token and test /me
        $user = User::where('email', 'alice@example.test')->first();
        $token = $user->createToken('api')->plainTextToken;

        $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me')
            ->assertStatus(200)
            ->assertJsonFragment(['email' => 'alice@example.test']);
    }
}
