<?php

namespace App\Listeners;

use Illuminate\Support\Str;
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
    }

    /**
     * Handle the event.
     *
     * @param  \RachidLaasri\LaravelInstaller\Events\EnvironmentSaved  $event
     * @return void
     */
    public function handle(EnvironmentSaved $event)
    {
        $event->getRequest()->merge([
            'app_key' => 'base64:' . base64_encode(Str::random(32)),
            'db_connection' => $event->getRequest()->database_connection,
            'db_host' => $event->getRequest()->database_hostname,
            'db_port' => $event->getRequest()->database_port,
            'db_database' => $event->getRequest()->database_name,
            'db_username' => $event->getRequest()->database_username,
            'db_password' => $event->getRequest()->database_password,
        ]);
        $envFileData = file_get_contents(base_path('.env.installer'));
        foreach ($event->getRequest()->input() as $key => $value) {
            $key = Str::of($key)->upper();
            $envFileData = str_replace("{{{$key}}}", $value, $envFileData);
        }
        file_put_contents(base_path('.env'), $envFileData);
    }
}
