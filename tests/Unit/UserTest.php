<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

uses(TestCase::class, \Illuminate\Foundation\Testing\RefreshDatabase::class);

test('user registration', function () {
    Event::fake();

    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'token',
            'user' => ['id', 'name', 'email']
        ]);

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    Event::assertDispatched(Registered::class);
});

test('registration validation errors', function () {
    $response = $this->postJson('/api/register', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => 'short',
        'password_confirmation' => 'mismatch',
    ]);


    $response->assertStatus(400)
        ->assertJsonStructure([
            'name',
            'email',
            'password',
        ]);
});


test('user login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token']);
});

test('invalid login credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(400)
        ->assertJson(['error' => 'Invalid credentials']);
});

test('get authenticated user', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJsonFragment(['email' => $user->email]);
});

test('unauthenticated user access', function () {
    $response = $this->getJson('/api/user');
    $response->assertStatus(401);
});

test('resend verification email', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/email/resend');

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('prevent resend for verified email', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/email/resend');

    $response->assertStatus(400)
        ->assertJson(['success' => false]);
});

test('jwt token generation failure', function () {
    $user = User::factory()->create();
    
    // Mock JWTAuth to throw exception
    JWTAuth::shouldReceive('attempt')->andThrow(new \Tymon\JWTAuth\Exceptions\JWTException);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(500)
        ->assertJson(['error' => 'Could not create token']);
});