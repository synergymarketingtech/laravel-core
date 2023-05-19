<?php

namespace Coderstm;

use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Coderstm\Commands\InstallCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Coderstm\Models\Cashier\Subscription;
use Coderstm\Commands\SubscriptionsCancel;
use Coderstm\Commands\SubscriptionsInvoice;
use Coderstm\Http\Middleware\CheckSubscribed;
use Coderstm\Http\Middleware\GuardMiddleware;
use Coderstm\Models\Cashier\SubscriptionItem;
use Illuminate\Database\Eloquent\Relations\Relation;

class CoderstmServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();

        $this->app->bind(\Illuminate\Routing\ResourceRegistrar::class, \Coderstm\Http\Routing\ResourceRegistrar::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRouteMiddleware();
        // $this->registerResources();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerCommands();

        DB::statement('SET @@auto_increment_offset = 100000');

        Relation::morphMap([
            'User' => Coderstm::$userModel,
            'Admin' => Coderstm::$adminModel,
            'Address' => 'Coderstm\Models\Address',
            'Group' => 'Coderstm\Models\Group',
        ]);

        Paginator::useBootstrapFive();

        Cashier::ignoreMigrations();

        Cashier::useSubscriptionModel(Subscription::class);
        Cashier::useSubscriptionItemModel(SubscriptionItem::class);
    }

    /**
     * Setup the configuration for Coderstm.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            $this->packagePath('config/coderstm.php'),
            'coderstm'
        );
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (Coderstm::shouldRunMigrations() && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom($this->packagePath('database/migrations'));
        }
    }

    /**
     * Register the package resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadViewsFrom($this->packagePath('resources/views'), 'coderstm');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->packagePath('config/coderstm.php') => $this->app->configPath('coderstm.php'),
            ], 'coderstm-config');

            $this->publishes([
                $this->packagePath('database/migrations') => $this->app->databasePath('migrations'),
            ], 'coderstm-migrations');

            $this->publishes([
                $this->packagePath('stubs/routes/web.stub') => $this->app->basePath('routes/coderstm/web.php'),
                $this->packagePath('stubs/routes/api.stub') => $this->app->basePath('routes/coderstm/api.php'),
            ], 'coderstm-routes');

            $this->publishes([
                $this->packagePath('stubs/views/app.blade.stub') => $this->app->resourcePath('views/app.blade.php'),
            ], 'coderstm-views');

            $this->publishes([
                $this->packagePath('stubs/controllers/AdminController.stub') => app_path('Http/Controllers/AdminController.php'),
                $this->packagePath('stubs/controllers/UserController.stub') => app_path('Http/Controllers/UserController.php'),
            ], 'coderstm-controllers');

            $this->publishes([
                $this->packagePath('stubs/policies/UserPolicy.stub') => app_path('Policies/UserPolicy.php'),
                $this->packagePath('stubs/policies/AdminPolicy.stub') => app_path('Policies/AdminPolicy.php'),
            ], 'coderstm-policies');

            $this->publishes([
                $this->packagePath('stubs/CoderstmServiceProvider.stub') => app_path('Providers/CoderstmServiceProvider.php'),
                $this->packagePath('stubs/CoderstmRouteServiceProvider.stub') => app_path('Providers/CoderstmRouteServiceProvider.php'),
            ], 'coderstm-provider');
        }
    }

    /**
     * Register the package route middlewares.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        Route::aliasMiddleware('guard', GuardMiddleware::class);
        Route::aliasMiddleware('subscribed', CheckSubscribed::class);
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                SubscriptionsCancel::class,
                SubscriptionsInvoice::class,
            ]);
        }
    }

    protected function packagePath(string $path)
    {
        return __DIR__ . '/../' . $path;
    }
}
