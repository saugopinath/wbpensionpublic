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

class StoreDeclarationRequest extends FormRequest
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
                //dump($request_data);
                $this->merge([
                    'application_id' => $request_data['formData']['application_id'],
                    'is_resident' => $request_data['formData']['doc_is_resident'],
                    'earn_monthly_remuneration' => $request_data['formData']['earn_monthly_remuneration'],
                    'no_financial_assistance' => $request_data['formData']['no_financial_assistance'] ?? null,
                    'no_income_tax' => $request_data['formData']['no_income_tax'] ?? null,
                    'info_genuine_decl' => $request_data['formData']['info_genuine_decl'],
                    'aadhaar_consent' => $request_data['formData']['aadhaar_consent'] ?? null,
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




        ];
    }
    public function attributes(): array
    {
        return [
            'data' => 'Payload',


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
            'is_resident' => 'required|in:1',
            'earn_monthly_remuneration' => 'required|in:1',
            'no_financial_assistance' => 'required|in:1',
            'no_income_tax' => 'required|in:1',
            'info_genuine_decl' => 'required|in:1',
            'aadhaar_consent' => 'required|in:1',
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
