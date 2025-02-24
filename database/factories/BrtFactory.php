<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brt>
 */
class BrtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
        return [
            'user_id'        => User::factory(), // Creates a new user for the BRT record.
            'brt_code'       => strtoupper(Str::random(10)), // Generates a random, unique BRT code.
            'reserved_amount'=> $this->faker->randomFloat(2, 10, 1000), // Generates a random reserved amount.
            'status'         => $this->faker->randomElement(['active', 'expired']), // Randomly selects a status.
        ];
    }
}
