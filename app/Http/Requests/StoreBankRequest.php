<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Helpers\EncryptDecrypt;
use Illuminate\Http\Request;
use App\Rules\ValidateCaptchaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;

class StoreBankRequest extends FormRequest
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
                //dd($request_data);
                $this->merge([
                    'application_id' => $request_data['formData']['application_id'],
                    'bank_ifsc_code' => $request_data['formData']['bank_ifsc_code'],
                    'bank_account_number' => $request_data['formData']['bank_account_number'],
                    'confirm_bank_account_number' => $request_data['formData']['confirm_bank_account_number'],
                    'captcha_token' =>  $request_data['formData']['captcha_token'],
                    'captcha_answer' =>  $request_data['formData']['captcha_answer'],

                ]);
            } catch (\Exception $e) {
                // If the data cannot be decrypted, it fails validation naturally
                // or you can handle it by doing nothing, leaving it invalid.
            }
        }
    }
    public function messages(): array
    {
        return [
            'data.required' =>  __('validation.required'),
            'bank_ifsc_code.required' =>  __('validation.required'),
            'bank_account_number.required' =>  __('validation.required'),
            'confirm_bank_account_number.required' =>  __('validation.required'),
            'captcha_token.required' =>  __('validation.required'),
            'captcha_answer.required' =>  __('validation.required'),



        ];
    }
    public function attributes(): array
    {
        return [
            'data' => 'Payload',
            'bank_ifsc_code' => 'Bank Ifsc',
            'bank_account_number' => 'Account Number',
            'confirm_bank_account_number' => 'Confirm Account Number',

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
            'bank_ifsc_code' => 'required',
            'bank_account_number' => 'required|numeric|required_with:confirm_bank_account_number|same:confirm_bank_account_number',
            'confirm_bank_account_number' => 'required|numeric',
            'captcha_token' => Config::get('constants.enable_captcha') ? 'required' : 'nullable',
            'captcha_answer' => Config::get('constants.enable_captcha') 
                ? ['required', new ValidateCaptchaRule($this->captcha_token)]
                : ['nullable'],

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
