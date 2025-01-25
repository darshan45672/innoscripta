<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_their_account()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('delete'));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_unauthenticated_user_cannot_access_delete_route()
    {
        $response = $this->deleteJson(route('delete'));

        $response->assertStatus(401);
    }

    public function test_handles_user_not_found_exception_gracefully()
    {
        // Create and authenticate a user
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Delete the authenticated user to simulate "not found"
        $user->delete();

        $response = $this->deleteJson(route('delete'));

        $response->assertStatus(500);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Failed to delete user. Please try again later.',
        ]);
    }

    public function test_handles_exception_during_deletion_process()
{
    Sanctum::actingAs(User::factory()->create());

    // Simulate the exception during deletion
    $user = User::findOrFail(Auth::id());

    // Throwing the exception manually to simulate a deletion failure
    DB::shouldReceive('transaction')->andThrow(new \Exception('Deletion failed'));

    $response = $this->deleteJson(route('delete'));

    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
        'message' => "User deleted successfully",
    ]);
}
}
