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

if (!function_exists('isUser')) {
    function isUser()
    {
        return guard() == 'users';
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return guard() == 'admins';
    }
}

if (!function_exists('appUrl')) {
    function appUrl($subdomain = 'app')
    {
        $scheme = request()->getScheme();
        if ($subdomain) {
            return "{$scheme}://$subdomain." . config('app.domain');
        }
        return "{$scheme}://" . config('app.domain');
    }
}

if (!function_exists('adminUrl')) {
    function adminUrl($path = '')
    {
        return config('app.adminUrl') . '/' . $path;
    }
}

if (!function_exists('isActive')) {
    function isActive(...$routes)
    {
        return request()->is($routes) ? 'active' : '';
    }
}

if (!function_exists('hasRecaptcha')) {
    function hasRecaptcha()
    {
        return !empty(config('recaptcha.site_key'));
    }
}

if (!function_exists('appSettings')) {
    function appSettings($key)
    {
        return AppSetting::findByKey($key);
    }
}

if (!function_exists('openingTimes')) {
    function openingTimes()
    {
        return appSettings('opening-times')->map(function ($item, $key) {
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

/**
 * Send the admin notification.
 *
 * @param  mixed  $notification
 * @return void
 */
if (!function_exists('adminNotify')) {
    function adminNotify($notification)
    {
        return Notification::route('mail', [
            config('app.admin_email') => 'Admin'
        ])->notify($notification);
    }
}
