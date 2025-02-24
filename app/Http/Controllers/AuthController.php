<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Auth\Events\Registered;
use Tymon\JWTAuth\Facades\JWTAuth as FacadesJWTAuth;
use Tymon\JWTAuth\JWT;

class AuthController extends Controller
{
    // Register new user with email verification
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'name' => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users',
           'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        
        $user = User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => Hash::make($request->password),
        ]);

        // Fire the registered event for email verification
        event(new Registered($user));

        return response()->json(['success' => true, 'message' => 'User registered successfully. Please verify your email.'], 201);
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
}
