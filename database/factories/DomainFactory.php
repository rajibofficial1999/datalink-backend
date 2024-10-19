<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain>
 */
class DomainFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'name' => fake()->domainName(),
            'screenshot' => fake()->imageUrl,
            'amount' => '10',
            'skype_url' => fake()->url,
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'is_default' => fake()->randomElement([true, false])
        ];
    }
}
