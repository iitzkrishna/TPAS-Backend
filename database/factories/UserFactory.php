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
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'user_name' => fake()->unique()->userName(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'contact_primary' => fake()->phoneNumber(),
            'contact_secondary' => fake()->optional()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'user_type' => fake()->randomElement(['tourist', 'partner', 'admin']),
            'avatar' => fake()->optional()->imageUrl(),
            'is_verified' => fake()->boolean(80), // 80% chance of being verified
            'nationality' => fake()->country(),
            'primary_language' => fake()->languageCode(),
            'secondary_language' => fake()->optional()->languageCode(),
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

    public function tourist(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'tourist',
        ]);
    }

    public function partner(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'partner',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'admin',
        ]);
    }
}
