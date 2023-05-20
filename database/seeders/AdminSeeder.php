<?php

namespace Coderstm\Database\Seeders;

use Coderstm\Coderstm;
use Coderstm\Models\Address;
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
        Coderstm::$adminModel::factory()->count(20)->create()->each(function ($user) {
            $user->updateOrCreateAddress(Address::factory()->make()->toArray());
        });
    }
}
