<?php

namespace Database\Seeders;

use Coderstm\Models\Location;
use Coderstm\Models\Template;
use Coderstm\Models\ClassList;
use Coderstm\Models\Instructor;
use Coderstm\Models\WeekTemplate;
use Illuminate\Database\Seeder;
use Coderstm\Models\TemplateSchedule;
use Coderstm\Traits\Helpers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TemplateSeeder extends Seeder
{
    use Helpers;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates =  [
            'Christmas Timetable',
            'March ' . date('Y'),
            'April ' . date('Y'),
            'June ' . date('Y'),
            'December ' . date('Y'),
            'General Timetable',
        ];

        foreach ($templates as $template) {
            Template::factory()
                ->has(TemplateSchedule::factory()->count(rand(25, 30)), 'schedules')
                ->create([
                    'label' => $template
                ]);
        }

        $weeks = $this->weeksBetweenTwoDates(now()->startOfWeek(), now()->addMonth()->endOfWeek()->addWeek());
        WeekTemplate::assignClassSchedule(collect($weeks)->map(function ($item) {
            return [
                'start_of_week' => $item,
                'template' => Template::inRandomOrder()->first()->toArray()
            ];
        })->toArray());
    }
}
