<?php

namespace Database\Factories;

use App\Models\WorkGroup;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkGroup>
 */
class WorkGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->words(2, true) . ' Team',
            'creator_id' => function (array $attributes) {
                // If company_id is provided, use a user from that company
                // Otherwise create a new user
                return \App\Models\User::factory();
            },
        ];
    }
}
