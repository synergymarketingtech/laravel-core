<?php

namespace Database\Seeders;

use CoderstmCore\Models\ClassList;
use CoderstmCore\Models\ClassSchedule;
use CoderstmCore\Models\Instructor;
use CoderstmCore\Models\Location;
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
