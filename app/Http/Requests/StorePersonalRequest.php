<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Helpers\EncryptDecrypt;
use Illuminate\Http\Request;
use App\Rules\Aadhaar;
use App\Rules\ValidateCaptchaRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;

class StorePersonalRequest extends FormRequest
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
                    'beneficiary_name' => $request_data['formData']['beneficiary_name'] ?? null,
                    'gender' =>  $request_data['formData']['gender'] ?? null,
                    'dob' =>  $request_data['formData']['dob'] ?? null,
                    'father_first_name' =>  $request_data['formData']['father_first_name'] ?? null,
                    'father_middle_name' =>  $request_data['formData']['father_middle_name'] ?? '',
                    'father_last_name' =>  $request_data['formData']['father_last_name'] ?? null,
                    'mother_first_name' =>  $request_data['formData']['mother_first_name'] ?? null,
                    'mother_middle_name' =>  $request_data['formData']['mother_middle_name'] ?? '',
                    'mother_last_name' =>  $request_data['formData']['mother_last_name'] ?? null,
                    'caste_category' =>  $request_data['formData']['caste_category'] ?? null,
                    'aadhar_no' =>  $request_data['formData']['aadhar_no'] ?? null,
                    'ben_mobile_no' =>  $request_data['formData']['ben_mobile_no'] ?? null,
                    'captcha_token' =>  $request_data['formData']['captcha_token'] ?? null,
                    'captcha_answer' =>  $request_data['formData']['captcha_answer'] ?? null,
                    'caste_certificate_no' =>  $request_data['formData']['caste_certificate_no'] ?? '',
                    'marital_status' =>  $request_data['formData']['marital_status'] ?? null,
                    'spouse_first_name' =>  $request_data['formData']['spouse_first_name'] ?? '',
                    'spouse_middle_name' =>  $request_data['formData']['spouse_middle_name'] ?? '',
                    'spouse_last_name' =>  $request_data['formData']['spouse_last_name'] ?? '',
                    'email' =>  $request_data['formData']['email'] ?? '',
                    'ration_card_no' =>  $request_data['formData']['ration_card_no'] ?? null,
                    'epic_card_no' =>  $request_data['formData']['epic_card_no'] ?? null,
                    'pan_no' =>  $request_data['formData']['pan_no'] ?? null,
                    'has_pan_card' =>  $request_data['formData']['has_pan_card'] ?? null,
                    'is_taxpayer' =>  $request_data['formData']['is_taxpayer'] ?? null,
                ]);
            } catch (\Exception $e) {
                // Ignore decryption errors here, will fail validation rules
            }
        }
    }
    public function messages(): array
    {
        return [
            'data.required' =>  __('validation.required'),
            'beneficiary_name.required' =>  __('validation.required'),
            'beneficiary_name.required_with' =>  __('validation.required'),
            'dob.required' =>  __('validation.required'),
            'dob.required_with' =>  __('validation.required'),
            'father_first_name.required' =>  __('validation.required'),
            'father_first_name.required_with' =>  __('validation.required'),
            'mother_first_name.required' =>  __('validation.required'),
            'mother_first_name.required_with' =>  __('validation.required'),
            'caste_category.required' =>  __('validation.required'),
            'caste_category.required_with' =>  __('validation.required'),

            'aadhar_no.required' =>  __('validation.required'),
            'aadhar_no.required_with' =>  __('validation.required'),
            'ben_mobile_no.required' =>  __('validation.required'),
            'ben_mobile_no.required_with' =>  __('validation.required'),



        ];
    }
    public function attributes(): array
    {
        return [
            'data' => 'Payload',
            'beneficiary_name' => 'Applicant Name',
            'dob' => 'Date of Birth',
            'father_first_name' => 'Father First Name',
            'father_middle_name' => 'Father Middle Name',
            'father_last_name' => 'Father Last Name',
            'mother_first_name' => 'Mother First Name',
            'mother_middle_name' => 'Mother Middle Name',
            'mother_last_name' => 'Mother Last Name',
            'caste_category' => 'Caste',
            'caste_certificate_no' => 'SC/ST Certificate No.',
            'marital_status' => 'Marital Status',
            'aadhar_no' => 'Applicant Aadhaar Number',
            'ben_mobile_no' => 'Mobile Number',
            'spouse_first_name' => 'Spouse First Name',
            'spouse_middle_name' => 'Spouse Middle Name',
            'spouse_last_name' => 'Spouse Last Name',
        ];
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        date_default_timezone_set('Asia/Kolkata');
        //$phaseArr = DsPhase::where('is_current', TRUE)->first();
        //$mydate = $phaseArr->base_dob;
        $myYear =  date("Y");
        //$mydate =  $myYear.'-'.'01'.'-'.'01';
        $mydate = date('Y-m-d');
        $max_date = strtotime("-25 year", strtotime($mydate));
        $max_date = date("Y-m-d", $max_date);
        $min_date = strtotime("-60 year", strtotime($mydate));
        $min_date = date("Y-m-d", $min_date);
        $max_dob = $max_date;
        $min_dob = $min_date;
        $caste_key =  array_keys(Config::get('constants.caste_lb'));
        $marital_status_key =  array_keys(Config::get('constants.marital_status'));
        return [
            'data' => 'required',
            'beneficiary_name' => 'required_with:data',
            'dob' => 'required_with:data|date|before_or_equal:' . $max_dob . '|after_or_equal:' . $min_dob,
            'father_first_name' => 'required_with:data|string|max:200|regex:/^[A-Za-z\s]+$/',
            'father_middle_name' => 'nullable|string|max:200',
            'father_last_name' => 'required_with:data|string|max:200',
            'mother_first_name' => 'required_with:data|string|max:200|regex:/^[A-Za-z\s]+$/',
            'mother_middle_name' => 'nullable|string|max:200',
            'mother_last_name' => 'required_with:data|string|max:200',
            'caste_category' => 'required_with:data|in:' . implode(",", $caste_key),
            'caste_certificate_no' => 'nullable|string|max:200',
            'aadhar_no' => ['required_with:data', 'digits:12', new Aadhaar],
            'ben_mobile_no' => 'required_with:data|digits:10',
            'captcha_token' => Config::get('constants.enable_captcha') ? 'required' : 'nullable',
            'captcha_answer' => Config::get('constants.enable_captcha')
                ? ['required', new ValidateCaptchaRule($this->captcha_token)]
                : ['nullable'],
            'marital_status' => 'required_with:data|in:'.implode(",", $marital_status_key),
            'spouse_first_name' => 'nullable|string|max:200',
            'spouse_middle_name' => 'nullable|string|max:200',
            'spouse_last_name' => 'nullable|string|max:200',
            'email' => 'nullable|email|max:200',
            'ration_card_no' => 'nullable|string|min:8|max:20|regex:/^[A-Za-z0-9]+$/',
            'epic_card_no' => 'required_with:data|string|min:8|max:20|regex:/^[A-Za-z0-9]+$/',
            'has_pan_card' => 'required_with:data|string|in:1,0',
            'pan_no' => 'required_if:has_pan_card,1|nullable|string|size:10|regex:/^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/',
            'is_taxpayer' => 'required_with:data|string|in:1,0',
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
