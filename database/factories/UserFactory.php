<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a solo handyman user.
     */
    public function soloHandyman(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => \App\Models\User::TYPE_SOLO_HANDYMAN,
            'company_id' => null,
        ]);
    }

    /**
     * Create a company admin user.
     */
    public function companyAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => \App\Models\User::TYPE_COMPANY_ADMIN,
        ]);
    }

    /**
     * Create a company employee user.
     */
    public function companyEmployee(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => \App\Models\User::TYPE_COMPANY_EMPLOYEE,
        ]);
    }
}
