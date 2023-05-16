<?php

namespace Database\Factories;

use Coderstm\Core\Models\Plan;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $created_at = fake()->dateTimeBetween('-3 years');
        $gender = ['male', 'female'][rand(0, 1)];
        return [
            'title' => fake()->title($gender),
            'first_name' => fake()->firstName($gender),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail,
            'phone_number' => fake()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'status' => ['Active', 'Pending'][rand(0, 1)],
            'member_id' => $created_at->format('dmy'),
            'created_at' => $created_at,
            'plan_id' => Plan::inRandomOrder()->first()->id,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Indicate that the model's status should be deactive.
     *
     * @return static
     */
    public function deactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
