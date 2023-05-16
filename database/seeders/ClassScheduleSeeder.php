<?php

namespace Database\Seeders;

use App\Models\ClassList;
use App\Models\ClassSchedule;
use App\Models\Instructor;
use App\Models\Location;
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
