<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_be_created()
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    /** @test */
    public function it_returns_jwt_identifier_and_custom_claims()
    {
        $user = User::factory()->create();
        
        // Check JWT identifier
        $jwtId = $user->getJWTIdentifier();
        $this->assertNotEmpty($jwtId);

        // Check JWT custom claims
        $claims = $user->getJWTCustomClaims();
        $this->assertIsArray($claims);
    }

    /** @test */
    public function email_verification_status_is_handled_correctly()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        
        // Initially, the email is not verified
        $this->assertNull($user->email_verified_at);

        // Mark the email as verified
        $user->markEmailAsVerified();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
