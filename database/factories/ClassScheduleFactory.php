<?php

namespace Database\Factories;

use Carbon\Carbon;
use Coderstm\Core\Models\Location;
use Coderstm\Core\Models\ClassList;
use Coderstm\Core\Models\Instructor;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Coderstm\Core\Models\Instructor>
 */
class ClassScheduleFactory extends Factory
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
            'start_at' => fake()->time('H:i'),
            'end_at' => fake()->time('H:i'),
            'start_of_week' => Carbon::parse(fake()->dateTimeBetween('-1 years', '+1 years'))->startOfWeek()->format('Y-m-d'),
            'class_id' => ClassList::inRandomOrder()->first()->id,
            'location_id' => Location::inRandomOrder()->first()->id,
            'instructor_id' => Instructor::inRandomOrder()->first()->id,
        ];
    }
}
