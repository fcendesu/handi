<?php

namespace Database\Factories;

use App\Models\Discovery;
use App\Models\User;
use App\Models\Company;
use App\Models\WorkGroup;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discovery>
 */
class DiscoveryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Discovery::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'creator_id' => User::factory(),
            'assignee_id' => null,
            'company_id' => null,
            'work_group_id' => null,
            'property_id' => null,
            'customer_name' => $this->faker->name(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_email' => $this->faker->safeEmail(),
            'address' => $this->faker->address(),
            'discovery' => $this->faker->paragraph(3),
            'todo_list' => $this->faker->paragraph(2),
            'note_to_customer' => $this->faker->optional()->sentence(),
            'note_to_handi' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement([
                Discovery::STATUS_PENDING,
                Discovery::STATUS_APPROVED,
                Discovery::STATUS_IN_PROGRESS,
                Discovery::STATUS_COMPLETED,
                Discovery::STATUS_CANCELLED,
            ]),
            'priority' => Discovery::PRIORITY_LOW, // Default priority
            'completion_time' => $this->faker->optional()->numberBetween(30, 480),
            'offer_valid_until' => $this->faker->optional()->dateTimeBetween('+1 day', '+30 days'),
            'service_cost' => $this->faker->randomFloat(2, 0, 1000),
            'transportation_cost' => $this->faker->randomFloat(2, 0, 100),
            'labor_cost' => $this->faker->randomFloat(2, 0, 500),
            'extra_fee' => $this->faker->randomFloat(2, 0, 100),
            'discount_rate' => $this->faker->randomFloat(2, 0, 25),
            'discount_amount' => $this->faker->randomFloat(2, 0, 100),
            'payment_method' => $this->faker->optional()->randomElement(['cash', 'card', 'bank_transfer']),
            'images' => null,
            'share_token' => Str::random(32),
        ];
    }

    /**
     * Indicate that the discovery has low priority.
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Discovery::PRIORITY_LOW,
        ]);
    }

    /**
     * Indicate that the discovery has medium priority.
     */
    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Discovery::PRIORITY_MEDIUM,
        ]);
    }

    /**
     * Indicate that the discovery has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Discovery::PRIORITY_HIGH,
        ]);
    }

    /**
     * Indicate that the discovery is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Discovery::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the discovery is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Discovery::STATUS_APPROVED,
        ]);
    }

    /**
     * Indicate that the discovery is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Discovery::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Indicate that the discovery is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Discovery::STATUS_COMPLETED,
        ]);
    }

    /**
     * Indicate that the discovery is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Discovery::STATUS_CANCELLED,
        ]);
    }

    /**
     * Indicate that the discovery has an assignee.
     */
    public function withAssignee(): static
    {
        return $this->state(fn (array $attributes) => [
            'assignee_id' => User::factory(),
        ]);
    }

    /**
     * Indicate that the discovery belongs to a company.
     */
    public function withCompany(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => Company::factory(),
        ]);
    }

    /**
     * Indicate that the discovery belongs to a work group.
     */
    public function withWorkGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'work_group_id' => WorkGroup::factory(),
        ]);
    }

    /**
     * Indicate that the discovery is associated with a property.
     */
    public function withProperty(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_id' => Property::factory(),
        ]);
    }
}
