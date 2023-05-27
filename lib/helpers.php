<?php

use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
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

if (!function_exists('current_user')) {
    function current_user()
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
            return "{$scheme}://$subdomain." . config('coderstm.domain');
        }
        return "{$scheme}://" . config('coderstm.domain');
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '')
    {
        return config('coderstm.admin_url') . '/' . $path;
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

if (!function_exists('string_to_hex')) {
    function string_to_hex($name = 'Name')
    {
        $alphabet = range('A', 'Z');
        $numbers = collect(explode(' ', $name))->map(function ($item) use ($alphabet) {
            return array_search(mb_substr($item, 0, 1), $alphabet) + 1;
        })->sum();

        return sprintf("#%06x", $numbers * 3333);
    }
}

if (!function_exists('string_to_hsl')) {
    function string_to_hsl($str, $saturation = 35, $lightness = 65)
    {
        $hash = 0;

        for ($i = 0; $i < strlen($str); $i++) {
            $hash = ord(mb_substr($str, $i, 1)) + (($hash << 5) - $hash);
        }

        $hue = $hash % 360;
        return "hsl($hue, $saturation%, $lightness%)";
    }
}

if (!function_exists('model_log_name')) {
    function model_log_name($model)
    {
        if ($model->logName) {
            return $model->logName;
        }
        return Str::of(class_basename(get_class($model)))->snake()->replace('_', ' ')->title();
    }
}

if (!function_exists('format_amount')) {
    function format_amount($amount, $currency = null, $locale = null, array $options = [])
    {
        return Cashier::formatAmount($amount, $currency, $locale, $options);
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
            config('coderstm.admin_email') => 'Admin'
        ])->notify($notification);
    }
}
