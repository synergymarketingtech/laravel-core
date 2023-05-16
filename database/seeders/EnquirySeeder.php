<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Core\Enquiry;
use App\Models\Core\Enquiry\Reply;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EnquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Enquiry::factory()->count(150)
            ->has(
                Reply::factory()
                    ->for(Admin::inRandomOrder()->first(), 'user')
                    ->count(rand(0, 1))
            )
            ->create();
    }
}
