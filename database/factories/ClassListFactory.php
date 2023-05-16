<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClassListFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'capacity' => rand(10, 20),
            'description' => fake()->paragraph(15),
            'is_active' => rand(0, 1),
            'has_description' => rand(0, 1),
            'urls' => [],
        ];
    }
}
