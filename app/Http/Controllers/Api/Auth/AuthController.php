<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\PasswordResetLink;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

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
    public function register(Request $request){
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Handle the login request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request){
        $data = $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required|min:6'
        ]);

        $user = new UserResource(User::where('email', $data['email'])->first());

        if(!$user || !Hash::check($data['password'], $user->password)){
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Log out the authenticated user by deleting their current access token.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @return \Illuminate\Http\JsonResponse JSON response indicating the user has been logged out.
     */
    public function logout(Request $request){
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
    public function user(){

        $user = new UserResource(User::findOrFail(Auth::id()));

        return response()->json([
            'status' => 'success',
            'message' => 'User Detials',
            'user' => $user,
            'id' => $user->id,
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

    public function forgotPassword(Request $request){

        $data = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        $url = URL::temporarySignedRoute(
            'password.reset',
            now()->addMinutes(30),
            ['email' => $user->email]
        );

        $url = str_replace(env('APP_URL'), env('FRONTEND_APP_URL'), $url);

        if($user){

            $user->notify(new PasswordResetLink($user->email, $url));

            return response()->json([
                'message' => 'Password reset link sent on your email id'
            ], 200);
        }

        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    /**
     * Reset the password for the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * This method validates the request data to ensure that the email exists in the users table
     * and that the password meets the required criteria. If the user is found, their password is
     * updated with the new hashed password. A success message is returned upon successful update.
     * If the user is not found, a 404 response with an error message is returned.
     */
    public function resetPassword(Request $request){
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::where('email', $data['email'])->first();

        if($user){
            $user->update([
                'password' => Hash::make($data['password'])
            ]);

            return response()->json([
                'message' => 'Password reset successfully'
            ], 200);
        }

        return response()->json([
            'message' => 'User not found'
        ], 404);
    }
}
