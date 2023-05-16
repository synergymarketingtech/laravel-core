<?php

namespace Coderstm\Core\Providers;

use Laravel\Cashier\Cashier;
use Coderstm\Core\Models\Cashier\Subscription;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Coderstm\Core\Models\Cashier\SubscriptionItem;
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
            'User' => 'Coderstm\Core\Models\User',
            'Admin' => 'Coderstm\Core\Models\Admin',
            'Address' => 'Coderstm\Core\Models\Core\Address',
            'Group' => 'Coderstm\Core\Models\Core\Group',
        ]);

        Paginator::useBootstrapFive();

        Cashier::ignoreMigrations();

        Cashier::useSubscriptionModel(Subscription::class);
        Cashier::useSubscriptionItemModel(SubscriptionItem::class);
    }
}
