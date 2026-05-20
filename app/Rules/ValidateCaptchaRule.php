<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;
use App\Helpers\EncryptDecrypt;

class ValidateCaptchaRule implements ValidationRule
{
    protected $captcha_token;

    /**
     * Create a new rule instance.
     *
     * @param string|null $captcha_token
     */
    public function __construct(?string $captcha_token)
    {
        $this->captcha_token = $captcha_token;
    }

    /**
     * Run the validation rule.
     *
     * @param  \string  $attribute
     * @param  \mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($this->captcha_token)) {
            $fail('The captcha token is missing.');
            return;
        }

        try {
            $decoded = base64_decode($this->captcha_token, true);
            if ($decoded === false) {
                $fail('The captcha token is invalid.');
                return;
            }
            $decryptedToken = EncryptDecrypt::decrypt($decoded);
        } catch (\Throwable $e) {
            $fail('The captcha token is invalid.');
            return;
        }

        if (empty($decryptedToken)) {
            $fail('The captcha token is invalid.');
            return;
        }
        $cachedAnswer = Cache::get("captcha:{$decryptedToken}");
        if (is_null($cachedAnswer) || (int)$value !== (int)$cachedAnswer) {
            $fail('The captcha is invalid or has expired.');
            return;
        }
        Cache::forget("captcha:{$decryptedToken}");
    }
}
