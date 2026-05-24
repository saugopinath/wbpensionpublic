<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\EncryptDecrypt;
use App\Rules\ValidateCaptchaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;

class StoreFamilyRequest extends FormRequest
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
                    'family_members' => $request_data['formData']['family_members'] ?? null,
                    'captcha_token' =>  $request_data['formData']['captcha_token'] ?? null,
                    'captcha_answer' =>  $request_data['formData']['captcha_answer'] ?? null,
                ]);
            } catch (\Exception $e) {
                // If decryption fails, validation rules will fail naturally
            }
        }
    }

    public function rules(): array
    {
        return [
            'data' => 'required',
            'application_id' => 'required',
            'family_members' => 'required|array|min:1',
            'family_members.*.name' => 'required|string|max:255',
            'family_members.*.is_govt_employee' => 'required|in:1,0',
            'family_members.*.pays_income_tax' => 'required|in:1,0',
            'family_members.*.school_details' => 'required|string|max:500',
            'family_members.*.vaccination_details' => 'required|string|max:500',
            'family_members.*.family_income' => 'required|numeric',
            'family_members.*.has_four_wheeler' => 'required|in:1,0',
            'captcha_token' => Config::get('constants.enable_captcha') ? 'required' : 'nullable',
            'captcha_answer' => Config::get('constants.enable_captcha') 
                ? ['required', new ValidateCaptchaRule($this->captcha_token)]
                : ['nullable'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'is_success' => false,
            'error' => $validator->errors()->first()
        ], 422));
    }
}
