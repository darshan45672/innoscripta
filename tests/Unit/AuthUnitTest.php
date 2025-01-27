<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use App\Models\User;
use App\Notifications\PasswordResetLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);
    }

    public function test_register_creates_new_user_successfully()
    {
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $source = NewsSource::factory()->create();

        $response = $this->postJson(route('register'), [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1234567890',
            'preferred_categories' => [$category->id],
            'preferred_authors' => [$author->id],
            'preferred_sources' => [$source->id]
        ]);

        if ($response->status() !== 200) {
            dump($response->getContent());
        }

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'preferred_categories',
                    'preferred_authors',
                    'preferred_sources'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'Test User',
            'phone' => '1234567890'
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertTrue($user->preferredCategories()->where('category_id', $category->id)->exists());
        $this->assertTrue($user->preferredAuthors()->where('author_id', $author->id)->exists());
        $this->assertTrue($user->preferredSources()->where('news_source_id', $source->id)->exists());
    }

     public function test_register_validates_required_fields()
    {
        $response = $this->postJson(route('register'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_validates_unique_email()
    {
        $response = $this->postJson(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com', 
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

      public function test_register_validates_password_confirmation()
    {
        $response = $this->postJson(route('register'), [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_validates_password_length()
    {
        $response = $this->postJson(route('register'), [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => '12345',
            'password_confirmation' => '12345'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }


}
