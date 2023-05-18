<?php

use Coderstm\Models\AppSetting;
use Illuminate\Support\Facades\Notification;

if (!function_exists('guard')) {
    function guard()
    {
        if (request()->user()) {
            return request()->user()->guard;
        }
        return null;
    }
}

if (!function_exists('currentUser')) {
    function currentUser()
    {
        return request()->user();
    }
}

if (!function_exists('is_user')) {
    function is_user()
    {
        return guard() == 'users';
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return guard() == 'admins';
    }
}

if (!function_exists('app_url')) {
    function app_url($subdomain = 'app')
    {
        $scheme = request()->getScheme();
        if ($subdomain) {
            return "{$scheme}://$subdomain." . config('app.domain');
        }
        return "{$scheme}://" . config('app.domain');
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '')
    {
        return config('app.admin_url') . '/' . $path;
    }
}

if (!function_exists('members_url')) {
    function member_url($path = '')
    {
        return config('app.members_url') . '/' . $path;
    }
}

if (!function_exists('is_active')) {
    function is_active(...$routes)
    {
        return request()->is($routes) ? 'active' : '';
    }
}

if (!function_exists('has_recaptcha')) {
    function has_recaptcha()
    {
        return !empty(config('recaptcha.site_key'));
    }
}

if (!function_exists('app_settings')) {
    function app_settings($key)
    {
        return AppSetting::findByKey($key);
    }
}

if (!function_exists('opening_times')) {
    function opening_times()
    {
        return app_settings('opening-times')->map(function ($item, $key) {
            $item['is_today'] = now()->format('l') == $item['name'];
            return $item;
        });
    }
}

if (!function_exists('string2hex')) {
    function string2hex($name = 'Name')
    {
        $alphabet = range('A', 'Z');
        $numbers = collect(explode(' ', $name))->map(function ($item) use ($alphabet) {
            return array_search(mb_substr($item, 0, 1), $alphabet) + 1;
        })->sum();

        return sprintf("#%06x", $numbers * 3333);
    }
}

if (!function_exists('string2hsl')) {
    function string2hsl($str, $saturation = 35, $lightness = 65)
    {
        $hash = 0;

        for ($i = 0; $i < strlen($str); $i++) {
            $hash = ord(mb_substr($str, $i, 1)) + (($hash << 5) - $hash);
        }

        $hue = $hash % 360;
        return "hsl($hue, $saturation%, $lightness%)";
    }
}

if (!function_exists('admin_notification')) {
    function admin_notification()
    {
        return Notification::route('mail', [
            config('app.admin_email') => 'Admin'
        ]);
    }
}

/**
 * Send the admin notification.
 *
 * @param  mixed  $notification
 * @return void
 */
if (!function_exists('admin_notify')) {
    function admin_notify($notification)
    {
        return Notification::route('mail', [
            config('app.admin_email') => 'Admin'
        ])->notify($notification);
    }
}
