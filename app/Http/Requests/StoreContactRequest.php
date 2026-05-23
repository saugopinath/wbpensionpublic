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

class StoreContactRequest extends FormRequest
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
                // dd($request_data);
                $this->merge([
                    'application_id' => $request_data['formData']['application_id'],
                    'district' => $request_data['formData']['district'],
                    'assembly' => $request_data['formData']['assembly'] ?? null,
                    'urban_code' => $request_data['formData']['urban_code'],
                    'police_station' => $request_data['formData']['police_station'],
                    'block_muncipality' => $request_data['formData']['block_muncipality'],
                    'gp_ward' => $request_data['formData']['gp_ward'],
                    'village_town_city' => $request_data['formData']['village_town_city'],
                    'house_premise_no' => $request_data['formData']['house_premise_no'] ? $request_data['formData']['house_premise_no'] : '',
                    'post_office' => $request_data['formData']['post_office'],
                    'pin_code' => $request_data['formData']['pin_code'],
                    'captcha_token' =>  $request_data['formData']['captcha_token'],
                    'captcha_answer' =>  $request_data['formData']['captcha_answer'],

                ]);

                // dd($this->application_id);

            } catch (\Exception $e) {
                dd($e);
                // If the data cannot be decrypted, it fails validation naturally
                // or you can handle it by doing nothing, leaving it invalid.
            }
        }
    }
    public function messages(): array
    {
        return [
            'data.required' =>  __('validation.required'),
            'district.required' =>  __('validation.required'),
        ];
    }
    public function attributes(): array
    {
        return [
            'data' => 'Payload',
            'application_id' => 'Application Id',
            'district' => 'District',
            'urban_code' => 'Rural/ Urban',
            'police_station' => 'Police Station',
            'block_muncipality' => 'Block/Municipality/Corp',
            'gp_ward' => 'GP/Ward No.',
            'village_town_city' => 'Village/Town/City',
            'house_premise_no' => 'House / Premise No.',
            'post_office' => 'Post Office',
            'pin_code' => 'Pin Code',
        ];
    }

    public function rules(): array
    {

        return [
            'data' => 'required',
            'application_id' => 'required_with:data',
            'district' => 'required',
            'assembly' => 'required|integer',
            'urban_code' => 'required',
            'police_station' => 'required',
            'block_muncipality' => 'required',
            'gp_ward' => 'required',
            'village_town_city' => 'required|string|max:300',
            'house_premise_no' => 'string|nullable',
            'post_office' => 'required|string',
            'pin_code' => 'required|numeric|digits:6',
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
