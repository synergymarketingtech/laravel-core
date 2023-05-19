<?php

namespace Coderstm;

use Illuminate\Support\Facades\DB;
use Coderstm\Commands\InstallCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Coderstm\Commands\SubscriptionsCancel;
use Coderstm\Commands\SubscriptionsInvoice;
use Coderstm\Http\Middleware\CheckSubscribed;
use Coderstm\Http\Middleware\GuardMiddleware;

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
        $this->registerResources();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerCommands();

        DB::statement('SET @@auto_increment_offset = 100000');
    }

    /**
     * Setup the configuration for Coderstm.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/coderstm.php',
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
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Register the package resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'coderstm-views');
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
                __DIR__ . '/../config/coderstm.php' => $this->app->configPath('coderstm.php'),
            ], 'coderstm-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'coderstm-migrations');

            $this->publishes([
                __DIR__ . '/../stubs/CoderstmServiceProvider.stub' => app_path('Providers/CoderstmServiceProvider.php'),
                __DIR__ . '/../stubs/CoderstmRouteServiceProvider.stub' => app_path('Providers/CoderstmRouteServiceProvider.php'),
                __DIR__ . '/../stubs/app.blade.stub' => $this->app->resourcePath('views/app.blade.php'),
                __DIR__ . '/../stubs/routes/web.stub' => $this->app->basePath('routes/coderstm/web.php'),
                __DIR__ . '/../stubs/routes/api.stub' => $this->app->basePath('routes/coderstm/api.php'),
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
}
