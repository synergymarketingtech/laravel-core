<?php

namespace Coderstm\Database\Seeders;

use Illuminate\Database\Seeder;
use Coderstm\Database\Seeders\PlanSeeder;
use Coderstm\Database\Seeders\TaskSeeder;
use Coderstm\Database\Seeders\UserSeeder;
use Coderstm\Database\Seeders\AdminSeeder;
use Coderstm\Database\Seeders\EnquirySeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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
            EnquirySeeder::class,
            TaskSeeder::class,
        ]);
    }
}
