<?php

namespace Coderstm;

use Coderstm\Http\Routing\Router;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Events\WebhookReceived;
use Coderstm\Http\Middleware\CheckSubscribed;
use Coderstm\Http\Middleware\GuardMiddleware;
use Coderstm\Events\Cashier\SubscriptionProcessed;
use Coderstm\Listeners\Cashier\CashierEventListener;
use Coderstm\Listeners\Cashier\SubscriptionEventListener;

class CoderstmServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/coderstm.php', 'coderstm');
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::swap(app(Router::class));

        if (app()->runningInConsole()) {
            $this->registerMigrations();

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'coderstm-migrations');

            $this->publishes([
                __DIR__ . '/../config/coderstm.php' => config_path('coderstm.php'),
            ], 'coderstm-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => $this->app->resourcePath('views/vendor/coderstm'),
            ], 'coderstm-views');
        }

        $this->defineRoutes();
        $this->configureMiddleware();

        $this->commands([
            SubscriptionsCancel::class,
            SubscriptionsInvoice::class,
        ]);
    }

    /**
     * Register Coderstm's migration files.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (Coderstm::shouldRunMigrations()) {
            return $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Define the Coderstm routes.
     *
     * @return void
     */
    protected function defineRoutes()
    {
        if (Coderstm::shouldRegistersRoutes()) {
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

            // modify default api route
            if (config('app.domain')) {
                Route::group([
                    'domain' => config('app.api_prefix') . '.' . config('app.domain'),
                    'middleware' => 'api',
                    'as' => 'coderstm.api.',
                ], function () {
                    $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
                });
            } else {
                Route::group([
                    'prefix' => config('app.api_prefix'),
                    'middleware' => 'api',
                    'as' => 'coderstm.api.',
                ], function () {
                    $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
                });
            }
        }
    }

    /**
     * Configure the Coderstm middleware and priority.
     *
     * @return void
     */
    protected function configureMiddleware()
    {
        $kernel = app()->make(Kernel::class);

        $kernel->middlewareGroup('guard', [
            GuardMiddleware::class
        ]);

        $kernel->middlewareGroup('subscribed', [
            CheckSubscribed::class
        ]);
    }

    /**
     * Configure the Coderstm event listeners.
     *
     * @return void
     */
    protected function registerEventListeners()
    {
        $this->app->events->listen(
            SubscriptionProcessed::class,
            CashierEventListener::class
        );
        $this->app->events->listen(
            WebhookReceived::class,
            SubscriptionEventListener::class
        );
    }
}
