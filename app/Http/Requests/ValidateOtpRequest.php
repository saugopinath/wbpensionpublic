<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\EncryptDecrypt;
use App\Rules\ValidateCaptchaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;

class ValidateOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        Log::info('ValidateOtpRequest start');
        if ($this->has('data') && !empty($this->data)) {
            try {
                $request_data = EncryptDecrypt::decrypt($this->data);
                Log::info('Decrypted OTP request data: ', is_array($request_data) ? $request_data : ['raw' => $request_data]);
                
                $this->merge([
                    'mobile_no' => $request_data['extraData']['mobile_no'],
                    'scheme_id' => $request_data['extraData']['scheme_id'],
                    'otp' => $request_data['formData']['otp'],
                    'captcha_token' => $request_data['formData']['captcha_token'],
                    'captcha_answer' => $request_data['formData']['captcha_answer'],
                ]);
            } catch (\Exception $e) {
                Log::error('Decryption failed in ValidateOtpRequest: ' . $e->getMessage());
            }
        }   
    }

    public function rules(): array
    {
        return [
            'mobile_no' => 'required_with:data|digits:10',
            'scheme_id' => 'required_with:data|integer|in:20',
            'otp' => 'required_with:data|digits:6',
            'captcha_token' => 'required_with:data',
            'captcha_answer' => ['required_with:data', new ValidateCaptchaRule($this->captcha_token)],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'data' => 'Payload',
            'mobile_no' => 'Mobile Number',
            'scheme_id' => 'Scheme ID',
            'otp' => 'OTP',
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
