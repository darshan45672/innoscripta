<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;
    // public function test_user_can_reset_password_with_valid_signed_url()
    // {
    //     $user = User::factory()->create([
    //         'email' => 'test@example.com',
    //         'password' => Hash::make('oldpassword')
    //     ]);

    //     $signedUrl = URL::temporarySignedRoute(
    //         'password.reset',
    //         now()->addMinutes(30),
    //         ['email' => $user->email]
    //     );

    //     $response = $this->postJson('/api/password/reset', [
    //         'email' => $user->email,
    //         'password' => 'newpassword123',
    //         'password_confirmation' => 'newpassword123'
    //     ]);

    //     $response->assertStatus(200)
    //         ->assertJson(['message' => 'Password reset successfully']);

    //     $user->refresh();
    //     $this->assertTrue(Hash::check('newpassword123', $user->password));
    // }

    // public function test_reset_fails_with_invalid_email()
    // {
    //     $response = $this->postJson('/api/password/reset', [
    //         'email' => 'nonexistent@example.com',
    //         'password' => 'newpassword123',
    //         'password_confirmation' => 'newpassword123'
    //     ]);

    //     $response->assertStatus(422)
    //         ->assertJsonValidationErrors(['email']);
    // }

    // public function test_reset_fails_with_short_password()
    // {
    //     $user = User::factory()->create([
    //         'email' => 'test@example.com'
    //     ]);

    //     $response = $this->postJson('/api/password/reset', [
    //         'email' => $user->email,
    //         'password' => '123',
    //         'password_confirmation' => '123'
    //     ]);

    //     $response->assertStatus(422)
    //         ->assertJsonValidationErrors(['password']);
    // }

    // public function test_reset_fails_with_unconfirmed_password()
    // {
    //     $user = User::factory()->create([
    //         'email' => 'test@example.com'
    //     ]);

    //     $response = $this->postJson('/api/password/reset', [
    //         'email' => $user->email,
    //         'password' => 'newpassword123',
    //         'password_confirmation' => 'different123'
    //     ]);

    //     $response->assertStatus(422)
    //         ->assertJsonValidationErrors(['password']);
    // }

    // public function test_reset_requires_all_fields()
    // {
    //     $response = $this->postJson('/api/password/reset', []);

    //     $response->assertStatus(422)
    //         ->assertJsonValidationErrors(['email', 'password']);
    // }
    public function test_reset_fails_with_expired_signed_url()
    {
        $user = User::factory()->create([
            'email' => 'admin@admin.com'
        ]);

        $expiredUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->subMinutes(31),
            ['email' => $user->email]
        );

        $response = $this->postJson('/api/password/reset', [
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(403);
    }
}
