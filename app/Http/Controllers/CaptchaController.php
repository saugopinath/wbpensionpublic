<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Helpers\EncryptDecrypt;
class CaptchaController extends Controller
{
    public function generate()
    {
        // Generate a random 2-digit number
        $num = rand(10, 99);
        $answer = $num;

        $captchaToken = (string) Str::uuid();
        Cache::put("captcha:{$captchaToken}", $answer, now()->addMinutes(5));
        $question = "{$num}";

        return response()->json([
            'captcha_token' => base64_encode(EncryptDecrypt::encrypt($captchaToken)),
            'captcha_question' => base64_encode(EncryptDecrypt::encrypt($question))
        ]);
    }
}
