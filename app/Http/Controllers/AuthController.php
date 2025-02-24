<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Tymon\JWTAuth\JWT;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // expects a password_confirmation field
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Fire the registered event to trigger email verification
        event(new Registered($user));

        // Generate JWT token for the user
        $token = FacadesJWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully. Please verify your email.',
            'token'   => $token,
            'user'    => $user, // optionally return user data
        ], 201);
    }


    // Login and return JWT
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = FacadesJWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        return response()->json(compact('token'));
    }

    public function user(Request $request)
    {
        // Get the authenticated user via the auth() helper.
        $user = Auth::user();

        // Optionally check if a user was found.
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Return the authenticated user data.
        return response()->json($user, 200);
    }

    
    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        // Check if the user's email is already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.'
            ], 400);
        }

        // Send the verification email
        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Email verification sent successfully!'
        ]);
    }

}
