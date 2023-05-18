<?php

namespace Coderstm;

use Coderstm\Http\Routing\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        // $this->registerMiddleware();
        // $this->registerResources();
        // $this->registerMigrations();
        // $this->registerPublishing();
        // $this->registerCommands();

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
        logger('Coderstm::shouldRunMigrations()', [Coderstm::shouldRunMigrations()]);
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
                __DIR__ . '/../resources/views' => $this->app->resourcePath('views/vendor/coderstm'),
            ], 'coderstm-views');
        }
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (Coderstm::shouldRegistersRoutes()) {
            Route::swap(app(Router::class));
            // register tunnel domain
            if (config('app.tunnel_domain')) {
                Route::group([
                    'domain' => config('app.tunnel_domain'),
                    'middleware' => 'api',
                    'as' => 'coderstm.tunnel.',
                ], function () {
                    $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
                });
            }

            $options = [
                'prefix' => config('app.api_prefix'),
                'middleware' => 'api',
                'as' => 'coderstm.api.',
            ];

            // modify default api route
            if (config('app.domain')) {
                unset($options['prefix']);
                $options['domain'] = config('app.api_prefix') . '.' . config('app.domain');
            }

            Route::group($options, function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            });
        }
    }

    /**
     * Register the package middlewares.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $kernel = app()->make(Kernel::class);

        $kernel->appendMiddlewareToGroup('guard', [
            GuardMiddleware::class
        ]);

        $kernel->appendMiddlewareToGroup('subscribed', [
            CheckSubscribed::class
        ]);
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
                SubscriptionsCancel::class,
                SubscriptionsInvoice::class,
            ]);
        }
    }
}
