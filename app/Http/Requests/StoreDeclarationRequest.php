<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Helpers\EncryptDecrypt;
use Illuminate\Http\Request;
use App\Rules\ValidateCaptchaRule;
use Config;
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
                    'info_genuine_decl' => $request_data['formData']['info_genuine_decl'],
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
            'info_genuine_decl' => 'required|in:1',
            'captcha_token' => 'required',
            'captcha_answer' => 'required',
            
        ];
    }
}
