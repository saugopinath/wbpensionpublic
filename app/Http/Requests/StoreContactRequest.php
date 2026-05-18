<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
                    'mobile_no' => $request_data['mobile_no'],
                    'scheme_id' => $request_data['scheme_id'],
                    'application_id' => $request_data['scheme_id'],
                    'urban_code' => $request_data['urban_code'],
                    'police_station' => $request_data['police_station'],
                    'block_muncipality' => $request_data['block_muncipality'],
                    'gp_ward' => $request_data['gp_ward'],
                    'village_town_city' => $request_data['village_town_city'],
                    'house_premise_no' => $request_data['house_premise_no'],
                    'post_office' => $request_data['post_office'],
                    'pin_code' => $request_data['pin_code'],
                    
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
            'residency_period' => 'Number of years Dwelling in WB',
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
            'data' => 'required',
            'mobile_no' => 'required_with:data|digits:10',
            'scheme_id' => 'required_with:data|integer',
            'application_id' => 'required_with:data',
            'district' => 'required',
            'urban_code' => 'required',
            'police_station' => 'required',
            'block_muncipality' => 'required',
            'gp_ward' => 'required',
            'village_town_city' => 'required|string|max:300',
            'house_premise_no' => 'string|nullable',
            'post_office' => 'required|string',
            'pin_code' => 'required|numeric|digits:6',
            
        ];
    }
}
