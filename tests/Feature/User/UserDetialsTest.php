<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class UserDetialsTest extends TestCase
{
    use RefreshDatabase;
    public function test_it_returns_user_details_when_authenticated_and_cached()
    {
        $user = User::factory()->create();
        Auth::login($user);
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(new \App\Http\Resources\UserResource($user));

        $response = $this->getJson(route('user.details'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User Details',
                'user_id' => $user->id,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'id' => $user->id,
                ]
            ]);
    }
    public function test_it_returns_user_details_when_authenticated_and_cache_miss()
    {
        $user = User::factory()->create();

        Auth::login($user);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturnUsing(function ($cacheKey, $ttl, $callback) use ($user) {
                return $callback();
            });

        $response = $this->getJson(route('user.details'));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User Details',
                'user_id' => $user->id,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ]
            ]);
    }

    public function test_it_returns_unauthorized_when_not_authenticated()
    {
        $response = $this->getJson(route('user.details'));

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

}
