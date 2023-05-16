<?php

namespace App\Providers;

use Laravel\Cashier\Cashier;
use App\Models\Cashier\Subscription;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Models\Cashier\SubscriptionItem;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'User' => 'App\Models\User',
            'Admin' => 'App\Models\Admin',
            'Address' => 'App\Models\Core\Address',
            'Group' => 'App\Models\Core\Group',
        ]);

        Paginator::useBootstrapFive();

        Cashier::ignoreMigrations();

        Cashier::useSubscriptionModel(Subscription::class);
        Cashier::useSubscriptionItemModel(SubscriptionItem::class);
    }
}
