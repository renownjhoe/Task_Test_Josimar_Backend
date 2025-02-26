<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Brt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class BrtManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a verified user for testing
        $this->user = User::factory()->create([
            'email_verified_at' => now()
        ]);
        // Generate a JWT token for the user
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function a_user_can_create_a_brt()
    {
        $data = [
            'reserved_amount' => 100,
            'status' => 'active'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/brts', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id', 'user_id', 'brt_code', 'reserved_amount', 'status', 'created_at', 'updated_at'
                 ]);
    }

    /** @test */
    public function a_user_can_get_all_their_brts()
    {
        // Create two BRT records for our user
        Brt::factory()->create(['user_id' => $this->user->id]);
        Brt::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/brts');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    /** @test */
    public function a_user_can_get_a_single_brt()
    {
        $brt = Brt::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/brts/{$brt->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $brt->id,
                     'brt_code' => $brt->brt_code,
                 ]);
    }

    /** @test */
    public function a_user_can_update_a_brt()
    {
        $brt = Brt::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'reserved_amount' => 200,
            'status' => 'expired'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/brts/{$brt->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $brt->id,
                     'reserved_amount' => 200,
                     'status' => 'expired'
                 ]);
    }

    /** @test */
    public function a_user_can_delete_a_brt()
    {
        $this->actingAs($this->user); // Make sure the test is using the correct user

        $brt = Brt::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/brts/{$brt->id}");

        $response->assertStatus(200)
                ->assertJson(['message' => 'BRT deleted']);

        $this->assertDatabaseMissing('brts', ['id' => $brt->id]);
    }

}
