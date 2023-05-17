<?php

namespace CoderstmCore;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use CoderstmCore\Middleware\ExampleMiddleware;

class CoderstmCoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @param  \Illuminate\Contracts\Http\Kernel  $kernel
     * @return void
     */
    public function boot(Kernel $kernel)
    {
        $this->registerMiddleware($kernel);

        // Other boot logic goes here
    }

    /**
     * Register the middleware.
     *
     * @param  \Illuminate\Contracts\Http\Kernel  $kernel
     * @return void
     */
    protected function registerMiddleware(Kernel $kernel)
    {
        $kernel->prependMiddleware(ExampleMiddleware::class);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Register other services or bindings here
    }
}
