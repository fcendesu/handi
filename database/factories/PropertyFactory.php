<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cities = ['Lefkoşa', 'Girne', 'Mağusa', 'İskele', 'Güzelyurt', 'Lefke'];
        $districts = ['Dereboyu', 'Köşklüçiftlik', 'Küçük Kaymaklı', 'Hamitköy', 'Ortaköy'];

        return [
            'user_id' => \App\Models\User::factory(),
            'name' => fake()->words(3, true),
            'city' => fake()->randomElement($cities),
            'district' => fake()->randomElement($districts),
            'site_name' => fake()->optional()->words(2, true),
            'building_name' => fake()->optional()->words(2, true),
            'street' => fake()->optional()->streetAddress(),
            'door_apartment_no' => fake()->optional()->bothify('##-#'),
            'latitude' => fake()->latitude(35.0, 35.5),
            'longitude' => fake()->longitude(33.0, 34.0),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
