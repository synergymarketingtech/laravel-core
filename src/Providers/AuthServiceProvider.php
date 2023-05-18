<?php

namespace Coderstm\Providers;

use Coderstm\Models\Admin;
use Coderstm\Models\Group;
use Coderstm\Policies\AdminPolicy;
use Coderstm\Policies\GroupPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Admin::class => AdminPolicy::class,
        Group::class => GroupPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        ResetPassword::createUrlUsing(function ($user, string $token) {
            return request()->headers->get('origin') . config('app.reset_password_url') . "?token={$token}&email={$user->email}";
        });
    }
}
