<?php

namespace Database\Factories\Shop;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(rand(8, 12)),
            'description' => $this->faker->paragraph(),
            'price' => rand(100, 1000),
            'is_active' => 1,
        ];
    }
}
