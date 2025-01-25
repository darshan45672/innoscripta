<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogOutTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
   public function test_authenticated_user_can_logout()
   {
       $user = User::factory()->create();
       Sanctum::actingAs($user);

       $response = $this->postJson('/api/logout');

       $response->assertStatus(200)
           ->assertJson([
               'message' => 'Logged out'
           ]);

       $this->assertCount(0, $user->tokens);
   }

   public function test_unauthenticated_user_cannot_logout()
   {
       $response = $this->postJson('/api/logout');

       $response->assertStatus(401);
   }

   public function test_logout_deletes_only_current_token()
   {
    $user = User::factory()->create();
    
    $tokenInstance1 = $user->createToken('token1');
    $tokenInstance2 = $user->createToken('token2');
    
    $user->withAccessToken($tokenInstance1->accessToken);

    $this->actingAs($user, 'sanctum')
         ->postJson('/api/logout');

    $user->refresh();
    $remainingTokenIds = $user->tokens->pluck('id');
    
    $this->assertTrue($remainingTokenIds->contains($tokenInstance2->accessToken->id));
    $this->assertFalse($remainingTokenIds->contains($tokenInstance1->accessToken->id));
   }
}
