<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_the_register_user_returns_success_response(): void
    {
        $response = $this->postJson('/api/register', [
            'email' => 'testMember@gmail.com',
            'password' => '123456'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'testMember@gmail.com',
        ]);
    }

    public function test_the_validation_of_register(): void
    {
        $response = $this->postJson('/api/register', [
            'email' => 'testMember',
            'password' => '123'
        ]);

        $response->assertStatus(400);
    }

    public function test_duplicate_data_entry_register(): void
    {
        $response = $this->postJson('/api/register', [
            'email' => 'testMember@gmail.com',
            'password' => '123456'
        ]);

        $response->assertStatus(400);
    }

    public function test_the_login_user_returns_success_response(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'testMember@gmail.com',
            'password' => '123456'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(
                [
                    'status',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'type',
                            'created_at',
                            'updated_at'
                        ],
                        'token'
                    ],
                ]
            );
    }

    public function test_the_login_user_wrong_email_or_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'testMember@gmail.com',
            'password' => '987654321'
        ]);

        $response->assertStatus(401);
    }

    public function test_the_logout_user(): void
    {
        $testUser = User::factory()->create([
            'name' => 'John',
            'email' => 'john@gmail.com',
            'password' => Hash::make('password')
        ]);

        $token = $testUser->createToken('appAuthenticationToken')->accessToken;

        $testResponse = $this
            ->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/logout');

        $testUser->delete();

        $testResponse
            ->assertOk()
            ->assertJson([
                'message' => 'You are Logged out Successfully!'
            ]);
    }
}
