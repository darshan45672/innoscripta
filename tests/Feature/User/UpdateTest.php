<?php

namespace Tests\Feature\User;

use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_update_their_details()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $newsSource = NewsSource::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '9876543210',
            'preferred_categories' => [$category->id],
            'preferred_authors' => [$author->id],
            'preferred_sources' => [$newsSource->id],
        ];

        $response = $this->postJson(route('user.update'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'User updated successfully',
        ]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'email' => 'updated@example.com']);
        $this->assertTrue($user->preferredCategories->contains($category));
        $this->assertTrue($user->preferredAuthors->contains($author));
        $this->assertTrue($user->preferredSources->contains($newsSource));
    }

    public function test_user_update_validation_errors()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'name' => '', 
            'email' => 'not-an-email', 
            'phone' => '123', 
            'preferred_categories' => [999],
        ];

        $response = $this->postJson(route('user.update'), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name',
            'email',
            'phone',
            'preferred_categories.0',
        ]);
    }
    public function test_unauthenticated_user_cannot_update_details()
    {
        $data = [
            'name' => 'Unauthorized',
            'email' => 'unauthorized@example.com',
        ];

        $response = $this->postJson(route('user.update'), $data);

        $response->assertStatus(401); 
    }

    public function test_user_cannot_update_email_to_existing_email()
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create(['email' => 'existing@example.com']);
        Sanctum::actingAs($user);

        $data = [
            'name' => 'Updated Name',
            'email' => 'existing@example.com', 
        ];

        $response = $this->postJson(route('user.update'), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

}
