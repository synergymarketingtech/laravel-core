<?php

namespace Database\Seeders;

use Coderstm\Models\User;
use Coderstm\Enum\AppStatus;
use Coderstm\Models\Address;
use Illuminate\Database\Seeder;
use Coderstm\Models\Cashier\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()
            ->count(2000)
            ->create()
            ->each(function ($user) {
                $user->updateOrCreateAddress(Address::factory()->make()->toArray());
                if ($user->status == AppStatus::ACTIVE) {
                    $price = $user->plan->prices[rand(0, 1)]; // 0 => month and 1 => year

                    // Generate a fake subscription
                    $subscription = Subscription::create([
                        'name' => 'default',
                        'stripe_id' => fake()->unique()->lexify('sub_????????????????'),
                        'stripe_status' => 'active',
                        'stripe_price' => $price->stripe_id,
                        'quantity' => 1,
                        'trial_ends_at' => now()->addDays(14),
                        'created_at' => $user->created_at,
                        'ends_at' => rand(0, 1) ? $user->created_at->addMonths(rand(5, 8)) : null,
                    ]);

                    // Associate the subscription with the user
                    $user->subscriptions()->save($subscription);
                }
            });
    }
}
