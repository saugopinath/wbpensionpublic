<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
                // dd($request_data);
                 $this->merge([
                    'mobile_no' => $request_data['mobile_no'],
                    'scheme_id' => $request_data['scheme_id'],
                    'beneficiary_name' => $request_data['beneficiary_name'],
                    'gender' => $request_data['gender'],
                    'dob' => $request_data['dob'],
                    'father_first_name' => $request_data['father_first_name'],
                    'father_middle_name' => $request_data['father_middle_name'],
                    'father_last_name' => $request_data['father_last_name'],
                    'mother_first_name' => $request_data['mother_first_name'],
                    'mother_middle_name' => $request_data['mother_middle_name'],
                    'mother_last_name' => $request_data['mother_last_name'],
                    'caste_category' => $request_data['caste_category'],
                    'aadhar_no' => $request_data['aadhar_no'],
                    'ben_mobile_no' => $request_data['ben_mobile_no'],
                ]);
                if( $request_data['caste_category']==171 || $request_data['caste_category']==172){
                    $this->merge([
                    'caste_certificate_no' => $request_data['caste_certificate_no'],
                    
                ]);

                }
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
            'mobile_no' => 'Payload Mobile Number',
            'scheme_id' => 'Scheme Code',
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
            'mobile_no' => 'required_with:data|digits:10',
            'scheme_id' => 'required_with:data|integer',
            'beneficiary_name' => 'required_with:data',
            'beneficiary_name' => 'required_with:data|string|max:200|regex:/^[A-Za-z\s]+$/',
            'dob' => 'required_with:data|date|before_or_equal:' . $max_dob . '|after_or_equal:' . $min_dob,
            'father_first_name' => 'required_with:data|string|max:200|regex:/^[A-Za-z\s]+$/',
            'mother_first_name' => 'required_with:data|string|max:200|regex:/^[A-Za-z\s]+$/',
            'caste_category' => 'required_with:data|in:' . implode(",", $caste_key),
            'aadhar_no' => ['required_with:data', 'digits:12', new Aadhaar],
            'ben_mobile_no' => 'required_with:data|digits:10',
            
        ];
    }
}
