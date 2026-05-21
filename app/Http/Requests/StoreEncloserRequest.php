<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Helpers\EncryptDecrypt;
use Illuminate\Http\Request;
use App\Rules\ValidateCaptchaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Config;

class StoreEncloserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('data') && !empty($this->data)) {
            try {
                $request_data = EncryptDecrypt::decrypt($this->data);
                $this->merge([
                    'application_id' => $request_data['formData']['application_id'] ?? null,
                    'captcha_token' =>  $request_data['formData']['captcha_token'] ?? null,
                    'captcha_answer' =>  $request_data['formData']['captcha_answer'] ?? null,
                ]);
            } catch (\Exception $e) {
                // If the data cannot be decrypted, leave it to fail validation
            }
        }
    }

    public function messages(): array
    {
        return [
            'data.required' => __('validation.required'),
            'application_id.required' => 'Application ID is required.',
            'captcha_answer.required' => 'Captcha answer is required.',
        ];
    }

    public function attributes(): array
    {
        return [
            'data' => 'Payload',
            'application_id' => 'Application ID',
            'captcha_answer' => 'Captcha Answer',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'application_id' => 'required',
            'captcha_token' => 'required',
            'captcha_answer' => ['required_with:data', new ValidateCaptchaRule($this->captcha_token)],
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
