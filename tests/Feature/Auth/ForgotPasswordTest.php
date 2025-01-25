<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset_link()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/password/email', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password reset link sent on your email id'
            ]);

        Notification::assertSentTo(
            $user,
            PasswordResetLink::class
        );
    }

    public function test_cannot_request_password_reset_for_non_existent_email()
    {
        $response = $this->postJson('/api/password/email', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_email_is_required_for_password_reset()
    {
        $response = $this->postJson('/api/password/email', [
            'email' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_email_must_be_valid_format()
    {
        $response = $this->postJson('/api/password/email', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_generated_reset_url_is_signed_and_correctly_formatted()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);
    
        $signedUrl = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(30),
            ['email' => $user->email]
        );
    
        $frontendUrl = str_replace(
            env('APP_URL'), 
            env('FRONTEND_APP_URL'), 
            $signedUrl
        );
    
        $parsedUrl = parse_url($signedUrl);
        parse_str($parsedUrl['query'], $queryParams);
    
        $request = \Illuminate\Http\Request::create(
            $signedUrl, 
            'GET', 
            $queryParams
        );
    
        $this->assertTrue(URL::hasValidSignature($request));
        $this->assertStringContainsString(env('FRONTEND_APP_URL'), $frontendUrl);
    }
}
