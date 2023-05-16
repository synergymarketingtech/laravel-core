<?php

namespace Database\Seeders;

use Coderstm\Core\Models\Admin;
use Coderstm\Core\Models\Core\Task;
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
        Task::factory()->for(Admin::factory(), 'user')->has(Admin::factory()->count(rand(2, 3)), 'users')->count(3)->create();
    }
}
