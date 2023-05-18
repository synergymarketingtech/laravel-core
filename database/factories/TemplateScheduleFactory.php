<?php

namespace Database\Factories;

use Coderstm\Models\Location;
use Coderstm\Models\ClassList;
use Coderstm\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory
 */
class TemplateScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'day' => ['Monday',  'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'][rand(0, 6)],
            'start_at' => fake()->time('H:i', '10:30'),
            'end_at' => fake()->time('H:i', '19:30'),
            'class_id' => ClassList::inRandomOrder()->first()->id,
            'location_id' => Location::inRandomOrder()->first()->id,
            'instructor_id' => Instructor::inRandomOrder()->first()->id,
        ];
    }
}
