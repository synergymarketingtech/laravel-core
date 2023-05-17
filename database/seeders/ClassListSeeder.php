<?php

namespace Database\Seeders;

use CoderstmCore\Models\ClassList;
use CoderstmCore\Traits\Helpers;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClassListSeeder extends Seeder
{
    use Helpers;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = $this->csvToArray(base_path("database/data/class_lists.csv"));
        foreach ($rows as $item) {
            ClassList::updateOrCreate([
                'name' => $item['name'],
            ], ClassList::factory()->make()->toArray());
        }
    }
}
