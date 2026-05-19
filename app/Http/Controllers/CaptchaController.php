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
         // Generate random numbers (e.g., 1 to 10)
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);

    // Randomly choose an operation: 1 = Addition, 2 = Subtraction, 3 = Multiplication
    $operation = rand(1, 3);
    $operator = '';
    $answer = 0;

    switch ($operation) {
        case 1:
            $operator = '+';
            $answer = $num1 + $num2;
            break;
        case 2:
            // Prevent negative answers by ensuring num1 >= num2
            if ($num1 < $num2) {
                list($num1, $num2) = array($num2, $num1);
            }
            $operator = '-';
            $answer = $num1 - $num2;
            break;
        case 3:
            $operator = '*';
            $answer = $num1 * $num2;
            break;
        }
        $captchaToken = (string) Str::uuid();
        Cache::put("captcha:{$captchaToken}", $answer, now()->addMinutes(5));
        $question = "{$num1} {$operator} {$num2}";
        return response()->json([
            'captcha_token' => base64_encode(EncryptDecrypt::encrypt($captchaToken)),
            'captcha_question' => base64_encode(EncryptDecrypt::encrypt($question))
        ]);
    }
}
