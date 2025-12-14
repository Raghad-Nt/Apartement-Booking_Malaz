<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Apartment>
 */
class ApartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provinces = ['Damascus', 'Aleppo', 'Homs', 'Latakia', 'Tartous'];
        $cities = ['Damascus', 'Aleppo', 'Homs', 'Latakia', 'Tartous'];
        $features = ['wifi', 'parking', 'ac', 'kitchen', 'balcony', 'pool'];

        // Randomly select features
        $selectedFeatures = array_slice($features, 0, rand(1, count($features)));

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(3),
            'price' => fake()->randomFloat(2, 100, 1000),
            'location' => fake()->streetAddress(),
            'province' => fake()->randomElement($provinces),
            'city' => fake()->randomElement($cities),
            'features' => $selectedFeatures,
            'owner_id' => User::where('role', 'renter')->inRandomOrder()->first()->id ?? User::factory()->create(['role' => 'renter'])->id,
            'status' => fake()->randomElement(['available', 'booked', 'maintenance'])
        ];
    }
}