<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use App\Models\User;
use App\Notifications\PasswordResetLink;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{

    /**
     * Register a new user.
     *
     * This method handles the registration of a new user. It validates the incoming request data,
     * creates a new user with the provided information, generates an authentication token for the user,
     * and returns a JSON response containing the access token, token type, and user information.
     *
     * @param \Illuminate\Http\Request $request The incoming request instance containing user registration data.
     * 
     * @return \Illuminate\Http\JsonResponse A JSON response containing the access token, token type, and user information.
     * 
     * @throws \Illuminate\Validation\ValidationException If the validation of the request data fails.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|min:10|max:10',
            'preferred_categories' => 'nullable|array',
            'preferred_categories.*' => ['integer', Rule::exists('categories', 'id')],
            'preferred_authors' => 'nullable|array',
            'preferred_authors.*' => ['integer', Rule::exists('authors', 'id')],
            'preferred_sources' => 'nullable|array',
            'preferred_sources.*' => ['integer', Rule::exists('news_sources', 'id')],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
        ]);

        $this->syncPreferences($user, $data);

        $user->tokens()->where('expires_at', '<', now())->delete();

        $token = $user->createToken('auth_token', ['*'], now()->addMinutes(config('sanctum.expiration')))->plainTextToken;


        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user->load(['preferredCategories', 'preferredAuthors', 'preferredSources'])),
        ]);
    }


    /**
     * Handle the login request.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user->tokens()->where('expires_at', '<', now())->delete();

        $token = $user->createToken('auth_token', ['*'], now()->addMinutes(config('sanctum.expiration')))->plainTextToken;

        $cacheKey = 'user_' . $user->id;
        $user = Cache::remember($cacheKey, 60, function () use ($user) {
            return new UserResource($user->load(['preferredCategories', 'preferredAuthors', 'preferredSources']));
        });

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }



    /**
     * Log out the authenticated user by deleting their current access token.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse JSON response indicating the user has been logged out.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ]);
    }


    /**
     * Retrieve the authenticated user's details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user()
    {
        $cacheKey = 'user_' . Auth::id();
        $user = Cache::remember($cacheKey, 60, function () {
            $user = User::findOrFail(Auth::id());
            return new UserResource($user->load(['preferredCategories', 'preferredAuthors', 'preferredSources']));
        });

        return response()->json([
            'status' => 'success',
            'message' => 'User Details',
            'user_id' => Auth::id(),
            'user' => $user,
        ]);
    }



    /**
     * Handle the forgot password request.
     *
     * This method validates the request, checks if the user exists, generates a temporary signed URL
     * for password reset, and sends a notification email with the reset link to the user.
     *
     * @param \Illuminate\Http\Request $request The incoming request instance.
     * 
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the operation.
     * 
     * @throws \Illuminate\Validation\ValidationException If the validation fails.
     */

    public function forgotPassword(Request $request)
    {

        $data = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->firstOrFail();


        /**
         * Generate a temporary signed URL for password reset.
         *
         * This URL will be valid for 30 minutes and includes the user's email as a parameter.
         *
         * @return string The temporary signed URL for password reset.
         */
        $url = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(30),
            ['email' => $user->email]
        );


        /**
         * Replaces the base URL of the application with the frontend application URL.
         *
         * This function takes the given URL and replaces the base URL defined in the
         * environment variable `APP_URL` with the frontend application URL defined in
         * the environment variable `FRONTEND_APP_URL`.
         *
         * @param string $url The URL to be modified.
         * @return string The modified URL with the frontend application base URL.
         */
        $url = str_replace(env('APP_URL'), env('FRONTEND_APP_URL'), $url);


        $user->notify(new PasswordResetLink($user->email, $url));

        return response()->json([
            'message' => 'Password reset link sent on your email id'
        ], 200);
    }


    /**
     * Reset the password for the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * This method validates the incoming request to ensure that the email exists in the users table
     * and that the password is confirmed and has a minimum length of 6 characters. If the user is found,
     * their password is updated with the new hashed password. A success message is returned upon successful
     * password reset. If the user is not found, a 404 response with an error message is returned.
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        return response()->json([
            'message' => 'Password reset successfully'
        ], 200);

    }

    /**
     * Sync user preferences (categories, authors, and sources).
     * 
     * @param \App\Models\User $user
     * @param array $data
     */
    private function syncPreferences(User $user, array $data)
    {
        if (!empty($data['preferred_categories'])) {
            $user->preferredCategories()->sync($data['preferred_categories']);
        }

        if (!empty($data['preferred_authors'])) {
            $user->preferredAuthors()->sync($data['preferred_authors']);
        }

        if (!empty($data['preferred_sources'])) {
            $user->preferredSources()->sync($data['preferred_sources']);
        }
    }
}
