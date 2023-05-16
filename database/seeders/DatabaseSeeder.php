<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PlanSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            ClassListSeeder::class,
            InstructorSeeder::class,
            LocationSeeder::class,
            TemplateSeeder::class,
            ClassScheduleSeeder::class,
            EnquirySeeder::class,
            TaskSeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
