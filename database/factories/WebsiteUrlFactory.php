<?php

namespace Database\Factories;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebsiteUrl>
 */
class WebsiteUrlFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain_id' => Domain::inRandomOrder()->first()->id,
            'category' => fake()->randomElement(['login', 'video_calling']),
            'category_type' => fake()->randomElement(['login', 'google_duo', 'textnow', 'apptime', 'whatsapp', 'facetime']),
            'site' => fake()->randomElement(['eros', 'mega', 'pd', 'skip', 'tryst']),
            'url' => $this->faker->url(),
        ];
    }
}
