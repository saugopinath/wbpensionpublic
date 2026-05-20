<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\EncryptDecrypt;
use App\Rules\ValidateCaptchaRule;

class ValidateMobileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
       return true;
        if ($this->has('data') && !empty($this->data)) {
            try {
                $request_data = EncryptDecrypt::decrypt($this->data);
                
                $this->merge([
                    'mobile_no' => $request_data['formData']['mobile_no'] ?? null,
                    'captcha_token' => $request_data['formData']['captcha_token'] ?? null,
                    'captcha_answer' => $request_data['formData']['captcha_answer'] ?? null,
                    'scheme_id' => $request_data['extraData']['scheme_id'] ?? null,
                ]);
            } catch (\Exception $e) {
               return false;
            }
        }

    }
    public function rules(): array
    {
        return [
            'data' => 'required',
            'mobile_no' => 'required_with:data|digits:10',
            'scheme_id' => 'required_with:data|integer|in:20',
            'captcha_token' => 'required_with:data',
            'captcha_answer' => ['required_with:data', new ValidateCaptchaRule($this->captcha_token)],
        ];
    }
    public function attributes(): array
    {
        return [
            'data' => 'Payload',
            'mobile_no' => 'Mobile Number',
            'scheme_id' => 'Scheme ID',
            'captcha_token' => 'Captcha Token',
            'captcha_answer' => 'Captcha Answer',
        ];
    }
}
