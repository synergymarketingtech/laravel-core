<?php

namespace CoderstmCore\Providers;

use Laravel\Cashier\Cashier;
use CoderstmCore\Models\Cashier\Subscription;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use CoderstmCore\Models\Cashier\SubscriptionItem;
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
            'User' => 'CoderstmCore\Models\User',
            'Admin' => 'CoderstmCore\Models\Admin',
            'Address' => 'CoderstmCore\Models\Address',
            'Group' => 'CoderstmCore\Models\Group',
        ]);

        Paginator::useBootstrapFive();

        Cashier::ignoreMigrations();

        Cashier::useSubscriptionModel(Subscription::class);
        Cashier::useSubscriptionItemModel(SubscriptionItem::class);
    }
}
