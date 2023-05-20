<?php

namespace Coderstm\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EnquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
            'subject' => $this->faker->sentence(),
            'message' => $this->faker->paragraph(),
            'status' => ['Pending', 'Replied', 'Staff Replied', 'Resolved'][rand(0, 3)],
        ];
    }
}
