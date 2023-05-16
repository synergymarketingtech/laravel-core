<?php

namespace Database\Seeders;

use Coderstm\Core\Models\Location;
use Coderstm\Core\Models\Template;
use Coderstm\Core\Models\ClassList;
use Coderstm\Core\Models\Instructor;
use Coderstm\Core\Models\WeekTemplate;
use Illuminate\Database\Seeder;
use Coderstm\Core\Models\TemplateSchedule;
use Coderstm\Core\Traits\Helpers;
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
