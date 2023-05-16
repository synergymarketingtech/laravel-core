<?php

namespace Database\Seeders;

use Coderstm\Core\Models\ClassList;
use Coderstm\Core\Models\ClassSchedule;
use Coderstm\Core\Models\Instructor;
use Coderstm\Core\Models\Location;
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
