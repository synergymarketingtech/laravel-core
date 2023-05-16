<?php

namespace Database\Seeders;

use Coderstm\Core\Models\Admin;
use Coderstm\Core\Models\Address;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::factory()->count(20)->create()->each(function ($user) {
            $user->updateOrCreateAddress(Address::factory()->make()->toArray());
        });
    }
}
