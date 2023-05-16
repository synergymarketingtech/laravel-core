<?php

namespace App\Providers;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use RachidLaasri\LaravelInstaller\Events\EnvironmentSaved;

class ProcessEnvironment
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\RachidLaasri\LaravelInstaller\Events\EnvironmentSaved  $event
     * @return void
     */
    public function handle(EnvironmentSaved $event)
    {
        //
    }
}
