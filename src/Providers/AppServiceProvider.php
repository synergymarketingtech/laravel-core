<?php

namespace Coderstm\Providers;

use Laravel\Cashier\Cashier;
use Coderstm\Models\Cashier\Subscription;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Coderstm\Models\Cashier\SubscriptionItem;
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
            'User' => 'Coderstm\Models\User',
            'Admin' => 'Coderstm\Models\Admin',
            'Address' => 'Coderstm\Models\Address',
            'Group' => 'Coderstm\Models\Group',
        ]);

        Paginator::useBootstrapFive();

        Cashier::ignoreMigrations();

        Cashier::useSubscriptionModel(Subscription::class);
        Cashier::useSubscriptionItemModel(SubscriptionItem::class);
    }
}
