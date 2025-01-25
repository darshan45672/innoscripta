<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_login_returns_token_and_user_data()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'preferred_categories',
                    'preferred_authors',
                    'preferred_sources'
                ]
            ]);

        $user->refresh();
        $this->assertNotNull($user->tokens->first());
        $this->assertTrue(Cache::has('user_' . $user->id));
    }

    public function test_login_fails_with_empty_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => '',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_invalid_email_format()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_non_existent_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The selected email is invalid.',
                'errors' => [
                    'email' => ['The selected email is invalid.']
                ]
            ]);
    }

    public function test_login_fails_with_empty_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_with_short_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'short'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_with_incorrect_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
            'phone' => '1234567890'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }

    public function test_login_fails_with_missing_email()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_missing_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_old_tokens_are_deleted_on_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890'
        ]);

        $user->createToken('expired_token', ['*'], now()->subMinutes(10));

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $this->postJson('/api/login', $loginData);

        $user->refresh();
        $this->assertCount(1, $user->tokens);
    }

    public function test_rate_limiting_prevents_multiple_login_attempts()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890'
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ];

        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', $loginData);
        }

        $response->assertStatus(429); 
    }

    public function test_login_uses_cache_for_user_resource()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'phone' => '1234567890'
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $this->postJson('/api/login', $loginData);

        $cacheKey = 'user_' . $user->id;
        $this->assertTrue(Cache::has($cacheKey));

        $cachedUser = Cache::get($cacheKey);
        $this->assertEquals($user->id, $cachedUser['id']);
    }
}
