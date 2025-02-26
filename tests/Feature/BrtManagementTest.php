<?php

namespace Tests\Feature;

use App\Models\Brt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\BrtCreated;
use App\Events\BrtUpdated;
use App\Events\BrtDeleted;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

uses(RefreshDatabase::class);

/**
 * Get the JWT Authorization headers for the current user.
 */
function getAuthHeaders($user)
{
    $token = JWTAuth::fromUser($user);
    return ['Authorization' => "Bearer $token"];
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->token = JWTAuth::fromUser($this->user); // Generate JWT token for the user
});

test('index returns all brts', function () {
    Brt::factory()->count(3)->create(['user_id' => $this->user->id]);
    Brt::factory()->create(['user_id' => $this->otherUser->id]);

    $response = $this->withHeaders(getAuthHeaders($this->user))->getJson('/api/brts');

    $response->assertStatus(200)
        ->assertJsonCount(4); // Adjust this if you filter by user_id in the controller
});

test('store creates new brt', function () {
    Event::fake();

    $response = $this->withHeaders(getAuthHeaders($this->user))->postJson('/api/brts', [
        'reserved_amount' => 1000,
        'status' => 'active'
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'user_id', 'brt_code', 'reserved_amount', 'status'
        ]);

    $this->assertDatabaseHas('brts', [
        'user_id' => $this->user->id,
        'reserved_amount' => 1000
    ]);

    Event::assertDispatched(BrtCreated::class);
});

test('show returns user brt', function () {
    $brt = Brt::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders(getAuthHeaders($this->user))->getJson("/api/brts/{$brt->id}");

    $response->assertStatus(200)
        ->assertJson(['id' => $brt->id]);
});

test('show denies access to other users brts', function () {
    $brt = Brt::factory()->create(['user_id' => $this->otherUser->id]);

    $response = $this->withHeaders(getAuthHeaders($this->user))->getJson("/api/brts/{$brt->id}");

    $response->assertStatus(404);
});

test('update modifies existing brt', function () {
    Event::fake();
    $brt = Brt::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders(getAuthHeaders($this->user))->putJson("/api/brts/{$brt->id}", [
        'reserved_amount' => 2000,
        'status' => 'expired'
    ]);

    $response->assertStatus(200)
        ->assertJson(['reserved_amount' => 2000]);

    Event::assertDispatched(BrtUpdated::class);
});

test('store validation errors', function () {
    $response = $this->withHeaders(getAuthHeaders($this->user))
        ->postJson("/api/brts", [
            // "reserved_amount" is missing, and "status" is invalid.
            'status' => 'invalid'
        ]);

    $response->assertStatus(400)
        ->assertJsonStructure(['reserved_amount', 'status']);
});

test('update validation errors', function () {
    $brt = Brt::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders(getAuthHeaders($this->user))
        ->putJson("/api/brts/{$brt->id}", [
            'status' => 'invalid'
        ]);

    $response->assertStatus(400)
        ->assertJsonStructure(['status']);
});

test('destroy deletes brt', function () {
    Event::fake();
    $brt = Brt::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeaders(getAuthHeaders($this->user))
        ->deleteJson("/api/brts/{$brt->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'BRT deleted'
        ]);

    // Since Brt model is not using soft deletes, verify it's missing.
    $this->assertDatabaseMissing('brts', ['id' => $brt->id]);
    Event::assertDispatched(BrtDeleted::class);
});

test('destroy denies access to other users brts', function () {
    $brt = Brt::factory()->create(['user_id' => $this->otherUser->id]);

    $response = $this->withHeaders(getAuthHeaders($this->user))->deleteJson("/api/brts/{$brt->id}");

    $response->assertStatus(404);
});