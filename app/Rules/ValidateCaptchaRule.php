<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidateCaptchaRule implements ValidationRule
{
     protected $captcha_token;

    // Receive the argument here
    public function __construct(string $captcha_token)
    {
        $this->captcha_token = $captcha_token;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
     public function passes($attribute, $value)
    {
        $cachedAnswer = Cache::get("captcha:{$request->token}");

        // Validate if token expired or answer is incorrect
        if (!$cachedAnswer || (int)$value !== (int)$cachedAnswer) {
            return false;
        }

        // Clean up cache after successful validation
        Cache::forget("captcha:{$value}");

       return true;
    }

    public function message()
    {
        return 'The :attribute is not a valid Captcha.';
    }
}
