<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\EncryptDecrypt;
use App\Rules\ValidateCaptchaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValidateMobileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('data') && !empty($this->data)) {
            Log::info('Encrypted request data: ', is_array($this->data) ? $this->data : ['raw' => $this->data]);
            try {
                $request_data = EncryptDecrypt::decrypt($this->data);
                Log::info('Decrypted request data: ', is_array($request_data) ? $request_data : ['raw' => $request_data]);
                
                $this->merge([
                    'mobile_no' => $request_data['formData']['mobile_no'] ?? null,
                    'captcha_token' => $request_data['formData']['captcha_token'] ?? null,
                    'captcha_answer' => $request_data['formData']['captcha_answer'] ?? null,
                    'scheme_id' => $request_data['extraData']['scheme_id'] ?? null,
                ]);
            } catch (\Exception $e) {
                \Log::error('Decryption failed in ValidateMobileRequest: ' . $e->getMessage());
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

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'is_success' => false,
            'error' => $validator->errors()->first()
        ], 422));
    }
}
