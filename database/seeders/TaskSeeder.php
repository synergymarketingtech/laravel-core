<?php

namespace Coderstm\Database\Seeders;

use Coderstm\Database\Factories\AdminFactory;
use Coderstm\Database\Factories\TaskFactory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TaskFactory::new()->for(AdminFactory::new(), 'user')->has(AdminFactory::new()->count(rand(2, 3)), 'users')->count(3)->create();
    }
}
