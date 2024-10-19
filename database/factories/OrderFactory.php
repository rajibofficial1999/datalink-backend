<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package' => fake()->randomElement(['starter', 'standard', 'premium']),
            'user_id' => User::inRandomOrder()->first()->id,
            'payment_screenshot' => fake()->imageUrl(640, 480),
            'amount' => fake()->randomElement([5000, 3000, 10000]),
            'period' => fake()->randomElement([3, 1, 6]),
            'status' => fake()->randomElement(['pending', 'confirmed', 'rejected'])
        ];
    }
}
