<?php

namespace Coderstm\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ReCaptchaRule implements Rule
{
    const URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $response = Http::asForm()->post(static::URL, [
            'secret' => config('recaptcha.secret_key'),
            'response' => $value,
            'remoteip' => request()->ip()
        ])->json();

        return $response['success'] === true ?? false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The recaptcha verification failed. Please try again.';
    }
}
