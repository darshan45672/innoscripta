<?php

namespace Tests\Feature\Auth;

use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_successful_registration_with_full_details()
    {
        $categories = Category::factory()->count(3)->create();
        $authors = Author::factory()->count(3)->create();
        $sources = NewsSource::factory()->count(3)->create();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'preferred_categories' => $categories->pluck('id')->toArray(),
            'preferred_authors' => $authors->pluck('id')->toArray(),
            'preferred_sources' => $sources->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_registration_fails_with_empty_name()
    {
        $userData = [
            'name' => '',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_registration_fails_with_invalid_email()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_short_password()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => '12345',
            'password_confirmation' => '12345',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_unconfirmed_password()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_with_invalid_preferences()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'preferred_categories' => [9999],
            'preferred_authors' => [9999], 
            'preferred_sources' => [9999], 
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'preferred_categories.0',
                'preferred_authors.0',
                'preferred_sources.0'
            ]);
    }

    public function test_registration_with_invalid_phone_number()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '123',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_successful_registration_with_minimal_details()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
        ];

        $response = $this->postJson('/api/register', $userData);
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user'
            ]);
    }
}
