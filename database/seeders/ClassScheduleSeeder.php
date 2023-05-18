<?php

namespace Database\Seeders;

use Coderstm\Models\ClassList;
use Coderstm\Models\ClassSchedule;
use Coderstm\Models\Instructor;
use Coderstm\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClassScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClassSchedule::factory()->count(10)
            ->create();
    }
}
