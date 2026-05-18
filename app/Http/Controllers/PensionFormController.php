<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\EncryptDecrypt;
use App\Helpers\TokenValidation;

use App\Services\SendSmsService;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;  
use App\Models\UniqueAppBenId;
use App\Models\BeneficiaryPersonal;
use App\Models\BeneficiaryAadhaar;
use App\Models\BeneficiaryContact;
use App\Models\BeneficiaryEnclosure;
use App\Models\BeneficiarySelfDeclaration;
use App\Models\District;
use App\Models\Municipality;
use App\Models\Ward;
use App\Models\Block;
use App\Models\Codemaster;
use App\Models\Panchayat;
use App\Models\Ifsccodemaster;
use App\Models\BeneficiaryBank;
use App\Models\SchemeAttachedDocMappings;
use App\Models\AcceptRejectInfo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\StoreBankRequest;
use App\Http\Requests\StoreDeclarationRequest;
use App\Http\Requests\StoreEncloserRequest;
use Config;


class PensionFormController extends Controller
{
   public function __construct(

        protected SendSmsService $sendsmsService,
    ) {
        //date_default_timezone_set('Asia/Kolkata');
        
    }
    public function personalEntry(StorePersonalRequest $request)
    {
        
      try{
        $validated = $request->validated();
        //dd( $validated);
        $token_valid=TokenValidation::checkTokenMobileScheme($validated,$request);
        $scheme_id=$validated['scheme_id'];
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
         if($request->add_edit_status==1){
            $application_id=$validated['scheme_id'];
            if (empty($application_id)) {
              $errorMsg = __('messages.invaliddata');
             return response()->json(["is_success" => false,'error' => $errorMsg]);
           }
            $pension_details=BeneficiaryPersonal::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();

           }
           else{
            $application_id = Str::uuid();
            $beneficiary_id = Str::uuid();
            $pension_details=new BeneficiaryPersonal();
            $unquie_ben_id_obj=new UniqueAppBenId();
            $unquie_ben_id_obj->scheme_id= $scheme_id;
            $unquie_ben_id_obj->application_id=$application_id;
            $unquie_ben_id_obj->beneficiary_id=$beneficiary_id;
             $pension_details->application_id=$application_id;
           }
         
            $female_code_obj=Codemaster::where('code',52)->first();
           
            DB::beginTransaction();
           
            $is_saved = 0;
            try {
                
                if($request->add_edit_status==1){
                $is_saved_unqie = 1;
                }
                else{
                    $is_saved_unqie = $unquie_ben_id_obj->save();
                }
                if($is_saved_unqie){
                $pension_details->scheme_id= $scheme_id;
                $pension_details->application_date= Carbon::now()->format('Y-m-d H:i:s');   
                $pension_details->beneficiary_name= trim($validated['beneficiary_name']);
                $pension_details->gender= $female_code_obj->id;
                $pension_details->dob= $request->dob;
                $pension_details->father_fname= trim($validated['father_first_name']);
                if(!empty($validated['father_middle_name'])){
                $pension_details->father_mname= trim($validated['father_middle_name']);
                }
                if(!empty($validated['father_last_name'])){
                $pension_details->father_lname= trim($validated['father_last_name']);
                }
                $pension_details->mother_fname= trim($validated['mother_first_name']);
                if(!empty($validated['mother_middle_name'])){
                $pension_details->mother_mname= trim($validated['mother_middle_name']);
                }
                if(!empty($validated['mother_last_name'])){
                $pension_details->mother_lname= trim($validated['mother_last_name']);
                }
                $pension_details->caste= trim($validated['caste_category']);
                if(!empty($validated['caste_certificate_no'])){
                $pension_details->caste_certificate_no= trim($validated['caste_certificate_no']);
                }
                $pension_details->aadhar_no= '********' . substr($validated['aadhar_no'], -4);
                $pension_details->mobile_no= $validated['ben_mobile_no'];
                if(!empty($validated['email'])){
                $pension_details->email= $validated['email'];
                }
                if(!empty($validated['spouse_first_name'])){
                $pension_details->spouse_fname= trim($validated['spouse_first_name']);
                }
                if(!empty($validated['spouse_middle_name'])){
                $pension_details->spouse_mname= trim($validated['spouse_middle_name']);
                }
                if(!empty($validated['spouse_last_name'])){
                $pension_details->spouse_lname= trim($validated['spouse_last_name']);
                }
                $pension_details->ip_address= $request->ip();
                $pension_details->otp_validation_id= $decrpt_sub;
                $pension_details->is_clean= 2;
                $is_saved = $pension_details->save();
                if($is_saved){
                  $pension_details_aadhar=new BeneficiaryAadhaar();
                  $pension_details_aadhar->scheme_id= $scheme_id;

                  $pension_details_aadhar->encoded_aadhar = Crypt::encryptString($validated['aadhar_no']);
                  $pension_details_aadhar->aadhar_hash = md5($validated['aadhar_no']);
                  $pension_details_aadhar->application_id =   $pension_details->application_id;
                  $pension_details_aadhar->otp_validation_id= $decrpt_sub;
                  $pension_details_aadhar->is_clean= 2;
                  $is_saved_aadhar = $pension_details_aadhar->save();
                  $AcceptRejectInfo = new AcceptRejectInfo;
                  $AcceptRejectInfo->application_id = $pension_details->application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21101');
                  $accpt_reject_save = $AcceptRejectInfo->save();
                  if($is_saved_aadhar &&  $accpt_reject_save){
                     DB::commit();
                    return response()->json(["is_success" => true,'temp_application_id' => base64_encode(EncryptDecrypt::encrypt($pension_details->application_id))]);

                  }
                  else{
                     DB::rollBack();
                     $errorMsg = __('messages.dbroolback');
                     return response()->json(["is_success" => false,'error' => $errorMsg]);
                  }
                }
                else{
                     DB::rollBack();
                     $errorMsg = __('messages.dbroolback');
                     return response()->json(["is_success" => false,'error' => $errorMsg]);
                }
                }
                else{
                     DB::rollBack();
                     $errorMsg = __('messages.dbroolback');
                     return response()->json(["is_success" => false,'error' => $errorMsg]);
                }
            }
            catch (\Exception $e) {
            dd($e);
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
       }
      catch (\Exception $e) {
            dd($e);
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        //return response()->json(['return_status' => $return_status, 'application_id' => $application_id, 'return_msg' => $return_msg, 'max_tab_code' => $max_tab_code,'session_lb_lifecertificate' => $session_lb_lifecertificate,'session_lb_castecertificate' => $session_lb_castecertificate,'session_lb_aadhaar_no'=> $session_lb_aadhaar_no]);
    }
    public function contactEntry(StoreContactRequest $request)
    {
        try{
         $validated = $request->validated();
        //dd( $validated);
         $token_valid=$this->checkToken($validated,$request);
         $mobile_no=$validated['mobile_no'];
         $scheme_id=$validated['scheme_id'];
         $application_id=$validated['application_id'];
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
            $sel_district = $validated['district'];
            $cnt = $district_list->where('id', $sel_district)->count();
            if ($cnt == 0) {
                $return_text = __('messages.districtinvalid');
                return response()->json(["is_success" => false, 'error' => $return_text]);
            }
            $sel_urban_code =  $validated['urban_code'];
            $sel_block =  $validated['block_muncipality'];
            $sel_gp_ward =  $validated['gp_ward'];
            if ($sel_urban_code == 1) {
                $cnt1 = Municipality::where('district_id', $sel_district)->where('id', $sel_block)->count();
                if ($cnt1 == 0) {
                    $return_text = __('messages.contactinvalid');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
                }
                $cnt2 = Ward::where('municipality_id', $sel_block)->where('id', $sel_gp_ward)->count();
                if ($cnt2 == 0) {
                    $return_status = 0;
                    $return_text = __('messages.gpwardinvalid');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
                }
            } else if ($sel_urban_code == 2) {
                $cnt1 = Block::where('district_id', $sel_district)->where('id', $sel_block)->count();
                if ($cnt1 == 0) {
                    $return_text = __('messages.contactinvalid');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
                }
                $cnt2 = Panchayat::where('block_id', $sel_block)->where('id', $sel_gp_ward)->count();
                if ($cnt2 == 0) {
                    $return_text = __('messages.gpwardinvalid');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
                }
            }
            else{
                  $return_text = __('messages.invaliddata');
                    return response()->json(["is_success" => false, 'error' => $return_text]);
            }
           
             if($request->add_edit_status==1){
                $pension_details_contact=BeneficiaryContact::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();

             }
             else{
                  $pension_details_contact=new BeneficiaryContact();
                  $pension_details_contact->scheme_id  = $scheme_id;
                  $pension_details_contact->is_clean  = 2;
             }

            DB::beginTransaction();
            $is_saved = 0;
            try {

               
                $pension_details_contact->otp_validation_id= $decrpt_sub;
                $pension_details_contact->scheme_id= $scheme_id;
                $pension_details_contact->application_id= $application_id;

                if ($validated['urban_code'] == 1) {
                    $block_ulb = Municipality::where('id', $validated['block_muncipality'])->first();
                    $gp_ward = Ward::where('id', $validated['gp_ward'])->first();
                    //$pension_details_contact->block_ulb_name = trim($block_ulb->urban_body_name);
                    //$pension_details_contact->gp_ward_name  = trim($gp_ward->urban_body_ward_name);
                    $blockulbCode= $block_ulb->subdivision_id;
                } else {
                    $block_ulb =  Block::where('id', $validated['block_muncipality'])->first();
                    $gp_ward =  Panchayat::where('id', $$validated['gp_ward'])->first();
                    //$pension_details_contact->block_ulb_name= trim($block_ulb->block_name);
                    //pension_details_contact->gp_ward_name= trim($gp_ward->gram_panchyat_name);
                    $blockulbCode= $block_ulb->block_ulb;
                }
                $pension_details_contact->district_id       =      $validated['district'];
                $pension_details_contact->rural_urban     =     $validated['urban_code'];
                $pension_details_contact->policestation  = trim($validated['police_station']);
                $pension_details_contact->blockurban  = $validated['block_muncipality'];
                $pension_details_contact->gpward = $validated['gp_ward'];
                $pension_details_contact->village_town_city  = trim($validated['village_town_city']);
                $pension_details_contact->house_premise_no  = trim($validated['house_premise_no']);
                $pension_details_contact->post_office   = trim($validated['post_office']);
                $pension_details_contact->pincode  = trim($validated['pin_code']);
                $pension_details_contact->ip_address  = $request->ip();
                $pension_details_contact->created_by_dist_code = $validated['district'];
                $pension_details_contact->created_by_local_body_code = $blockulbCode;
                $is_saved = $pension_details_contact->save();
                   $AcceptRejectInfo = new AcceptRejectInfo;
                  $AcceptRejectInfo->application_id = $application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21102');
                  $accpt_reject_save = $AcceptRejectInfo->save();
                if($is_saved &&  $accpt_reject_save){
                   
                        DB::commit();
                       return response()->json(["is_success" => true,'temp_application_id' => base64_encode(EncryptDecrypt::encrypt($application_id))]);
                   
                }
                else{
                     DB::rollback();
                       $errorMsg = __('messages.dbroolback');
                       return response()->json(["is_success" => false,'error' => $errorMsg]);
                }
                
            } catch (\Exception $e) {
               dd($e);
                DB::rollback();
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
            
       
          }
        catch (\Exception $e) {
               dd($e);
                $errorMsg = __('messages.invaliddata');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
    }
    public function bankEntry(StoreBankRequest $request)
    {
   try{
        $scheme_id=EncryptDecrypt::decrypt(base64_decode($request->scheme_id));
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
        $application_id=EncryptDecrypt::decrypt(base64_decode($request->application_id));


        if (empty($scheme_id) || !is_int($scheme_id)) {
          $errorMsg = __('messages.invaliddata');
           return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        if (empty($decrpt_sub) || !is_int($decrpt_sub)) {
          $errorMsg = __('messages.invaliddata');
           return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        if (empty($application_id)) {
              $errorMsg = __('messages.invaliddata');
             return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        $rules = [
            'bank_ifsc_code' => 'required',
            'bank_account_number' => 'required|numeric|required_with:confirm_bank_account_number|same:confirm_bank_account_number',
            'confirm_bank_account_number' => 'required|numeric',

        ];
        $attributes = array();
        $messages = array();
        $attributes['bank_ifsc_code'] = 'IFS Code';
        $attributes['bank_account_number'] = 'Bank Account Number';

        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $application_id=EncryptDecrypt::decrypt(base64_decode($request->application_id));
            $ifsc = trim($request->bank_ifsc_code);

            $row_count1 = Ifsccodemaster::where('is_active', 1)->where('code', $ifsc)->count();
            if ($row_count1 == 0) {
                
                $errorMsg = __('messages.ifscnotvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
           

           if($request->add_edit_status==1){
                                    $pension_details_bank=BeneficiaryBank::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();

           }
           else{
                        $pension_details_bank=new BeneficiaryBank();
                        $pension_details_bank->scheme_id  = $scheme_id;
                        $pension_details_bank->is_clean  = 2;

                        $pension_details_bank->application_id  = $application_id;

           }
           
            DB::beginTransaction();
            
           
           
            $is_saved = 0;
            try {
                
                $pension_details_bank->otp_validation_id= $decrpt_sub;

                $pension_details_bank->bankaccountnumber    = trim($request->bank_account_number);
                $pension_details_bank->ifscode   = trim($request->bank_ifsc_code);
                $pension_details_bank->ip_address = $request->ip();
                $is_saved = $pension_details_bank->save();
                     $AcceptRejectInfo = new AcceptRejectInfo;
                  $AcceptRejectInfo->application_id = $application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21103');
                  $accpt_reject_save = $AcceptRejectInfo->save();
                 if($is_saved && $accpt_reject_save){
                        DB::commit();
                       return response()->json(["is_success" => true,'temp_application_id' => base64_encode(EncryptDecrypt::encrypt($application_id))]);
                 }
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
            
        } else {
             $return_msg = $validator->errors()->all();
            return response()->json(["is_success" => false,'errors' => $return_msg]);
        }
          }
        catch (\Exception $e) {
                $errorMsg = __('messages.invaliddata');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
    }
    public function declarationEntry(StoreDeclarationRequest $request)
    {
        try{
       $scheme_id=EncryptDecrypt::decrypt(base64_decode($request->scheme_id));
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
        $application_id=EncryptDecrypt::decrypt(base64_decode($request->application_id));


        if (empty($scheme_id) || !is_int($scheme_id)) {
          $errorMsg = __('messages.invaliddata');
           return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        if (empty($decrpt_sub) || !is_int($decrpt_sub)) {
          $errorMsg = __('messages.invaliddata');
           return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        if (empty($application_id)) {
              $errorMsg = __('messages.invaliddata');
             return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        $rules = [
            'is_resident' => 'required|in:1',
            'earn_monthly_remuneration' => 'required|in:1',
            'info_genuine_decl' => 'required|in:1'
        ];
        $attributes = array();
        $messages = array();
        $attributes['doc_is_resident.required'] = "Please check the checkbox That I am a resident of West Bengal";
        $attributes['doc_is_resident.in'] = "Please check the checkbox That I am a resident of West Bengal";
        $attributes['earn_monthly_remuneration.required'] = "Please check the checkbox That I do not earn any monthly remuneration from any regular Government job";
        $attributes['earn_monthly_remuneration.in'] = "Please check the checkbox That I do not earn any monthly remuneration from any regular Government job";
        $attributes['info_genuine_decl.required'] = "Please check the checkbox That all the information and documents submitted by me are correct/ genuine. In case any of the information/ document is found to be false, penal action shall be taken against me and the benefit will be terminated. ";
        $attributes['info_genuine_decl.in'] = "Please check the checkbox That all the information and documents submitted by me are correct/ genuine. In case any of the information/ document is found to be false, penal action shall be taken against me and the benefit will be terminated. ";
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
           
          
             if($request->add_edit_status==1){
                $pension_details_declaration=BeneficiarySelfDeclaration::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();

             }
             else{
                  $pension_details_declaration=new BeneficiarySelfDeclaration();
                  $pension_details_declaration->scheme_id= $scheme_id;
                  $pension_details_declaration->is_clean= 2;
                  $pension_details_declaration->application_id= $application_id;

             }
            

            DB::beginTransaction();
            $is_saved = 0;
            try {

               
                $pension_details_declaration->otp_validation_id= $decrpt_sub;

                 $pension_details_declaration->is_resident = trim($request->is_resident);
                 $pension_details_declaration->earn_monthly_remuneration= trim($request->earn_monthly_remuneration);
                 $pension_details_declaration->info_genuine_decl = trim($request->info_genuine_decl);
                 $pension_details_declaration->ip_address = $request->ip();
                 $pension_details_declaration->other_details =  [
                        'is_resident' => trim($request->is_resident),
                        'earn_monthly_remuneration' => trim($request->earn_monthly_remuneration),
                        'info_genuine_decl' =>trim($request->info_genuine_decl)
                    ];
                 $pension_details_declaration_save = $pension_details_declaration->save();
                  $AcceptRejectInfo = new AcceptRejectInfo;
                  $AcceptRejectInfo->application_id = $application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21104');
                  $accpt_reject_save = $AcceptRejectInfo->save();
                  if($pension_details_declaration_save && $accpt_reject_save){
                       DB::commit();
                       return response()->json(["is_success" => true,'temp_application_id' => base64_encode(EncryptDecrypt::encrypt($application_id))]);
                  }
                  else{
                    DB::rollback();
                    $errorMsg = __('messages.dbroolback');
                    return response()->json(["is_success" => false,'error' => $errorMsg]);
                  }

            }
            catch (\Exception $e) {
                dd($e);
                DB::rollback();
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
                
        } else {
            $return_msg = $validator->errors()->all();
            return response()->json(["is_success" => false,'errors' => $return_msg]);
        }
    }
        catch (\Exception $e) {
                $errorMsg = __('messages.invaliddata');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
    }
     public function encloserEntry(StoreEncloserRequest $request)
    {
        //dd(base64_encode(EncryptDecrypt::encrypt(111)));

         try {
        $scheme_id=EncryptDecrypt::decrypt(base64_decode($request->scheme_id));
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
        $application_id=EncryptDecrypt::decrypt(base64_decode($request->application_id));
        $document_type=EncryptDecrypt::decrypt(base64_decode($request->document_type));
        //dd($document_type);
        if (empty($scheme_id) || !is_int($scheme_id)) {
          $errorMsg = __('messages.invaliddata');
           return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        if (empty($decrpt_sub) || !is_int($decrpt_sub)) {
          $errorMsg = __('messages.invaliddata');
           return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        if (empty($application_id)) {
              $errorMsg = __('messages.invaliddata');
             return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
       
        

        // dd( $document_type);
        if (empty($scheme_id) || !is_int($scheme_id)) {
          $errorMsg = __('messages.invaliddata');
         return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        //dd( $document_type);
        $query = SchemeAttachedDocMappings::with('docType')->where('doc_type_id', $document_type)->where('scheme_id', $scheme_id);
        $doc_arr = $query->first();
        //dd( $doc_arr->toArray());
        if (empty($doc_arr->id)) {
            $errorMsg = __('messages.invaliddata');
            return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        $attributes = array();
        $messages = array();
        $valid = 0;
        // dump($request->add_edit_status);

        //dump(explode(',',$doc_arr->mime_type));
        //dd('ok');
        $required = 'required';
        $rules['file'] = $required . '|mimes:' .$doc_arr->mime_type. '|max:' . $doc_arr->docType->max_file_size . ',';
        $messages['file.max'] = "The file uploaded for " . $doc_arr->docType->name . " size must be less than :max KB";
        $messages['file.mimes'] = "The file uploaded for " . $doc_arr->docType->name . " must be of type " . $doc_arr->extension_type;
        $messages['file.required'] = "Document for " . $doc_arr->docType->name . " must be uploaded";
        //dd($rules);
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $valid = 1;
        } else {
            dd('file3',$validator->errors()->all());
            $return_msg = $validator->errors()->all();
            return response()->json(["is_success" => false,'errors' => $return_msg]);
        }


        if ($valid == 1) {
             $scheme_id=EncryptDecrypt::decrypt(base64_decode($request->scheme_id));
             $application_id=EncryptDecrypt::decrypt(base64_decode($request->application_id));
             if($request->add_edit_status==1){
                $pension_details_enc=BeneficiaryEnclosure::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();

             }
             else{
                  $pension_details_enc=new BeneficiaryEnclosure();
                  $pension_details_enc->application_id = $application_id;
                  $pension_details_enc->scheme_id = $scheme_id;
             }
             DB::beginTransaction();
             DB::connection('pgsql_encwrite')->beginTransaction();
            try {
                  $token= request()->bearerToken();
                  $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
                  $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
                  $image_file = $request->file('file');
                  $img_data = file_get_contents($image_file);
                  $extension = $image_file->getClientOriginalExtension();
                  $mime_type = $image_file->getMimeType();
                    //$type = pathinfo($image_file, PATHINFO_EXTENSION);
                  $base64 = base64_encode($img_data);
                  $pension_details_enc->document_type = $doc_arr->id;
                  $pension_details_enc->attched_document = $base64;
                  $pension_details_enc->document_extension = $extension;
                  $pension_details_enc->document_mime_type = $mime_type;
                  $pension_details_enc->ip_address = $request->ip();
                  $pension_details_enc->otp_validation_id= $decrpt_sub;
                  $is_saved = $pension_details_enc->save();
                  $AcceptRejectInfo = new AcceptRejectInfo;
                  $AcceptRejectInfo->application_id = $application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21105');
                  $accpt_reject_save = $AcceptRejectInfo->save();
                  if($is_saved && $accpt_reject_save){
                     DB::commit();
                     DB::connection('pgsql_encwrite')->commit();
                     return response()->json(["is_success" => true,'temp_application_id' => base64_encode(EncryptDecrypt::encrypt($application_id))]);

                  }
                  else{
                    DB::rollback();
                    DB::connection('pgsql_encwrite')->rollBack();
                     $errorMsg = __('messages.dbroolback');
                     return response()->json(["is_success" => false,'error' => $errorMsg]);
                  }
            }
            catch (\Exception $e) {
                dd('file1',$e);
                    DB::rollback();
                    DB::connection('pgsql_encwrite')->rollBack();
                    $errorMsg = __('messages.dbroolback');
                    return response()->json(["is_success" => false,'error' => $errorMsg]);
                }
           
        }
    }
         catch (\Exception $e) {
            dd('file'.$e);
                $errorMsg = __('messages.invaliddata');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        
    }
    public function isAadharValid($num)
    {
        settype($num, "string");
        $expectedDigit = substr($num, -1);
        $actualDigit = $this->CheckSumAadharDigit(substr($num, 0, -1));
        return ($expectedDigit == $actualDigit) ? $expectedDigit == $actualDigit : 0;
    }

    function CheckSumAadharDigit($partial)
    {
        $dihedral = array(
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
            array(1, 2, 3, 4, 0, 6, 7, 8, 9, 5),
            array(2, 3, 4, 0, 1, 7, 8, 9, 5, 6),
            array(3, 4, 0, 1, 2, 8, 9, 5, 6, 7),
            array(4, 0, 1, 2, 3, 9, 5, 6, 7, 8),
            array(5, 9, 8, 7, 6, 0, 4, 3, 2, 1),
            array(6, 5, 9, 8, 7, 1, 0, 4, 3, 2),
            array(7, 6, 5, 9, 8, 2, 1, 0, 4, 3),
            array(8, 7, 6, 5, 9, 3, 2, 1, 0, 4),
            array(9, 8, 7, 6, 5, 4, 3, 2, 1, 0)
        );
        $permutation = array(
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
            array(1, 5, 7, 6, 2, 8, 3, 0, 9, 4),
            array(5, 8, 0, 3, 7, 9, 6, 1, 4, 2),
            array(8, 9, 1, 6, 0, 4, 3, 5, 2, 7),
            array(9, 4, 5, 3, 1, 2, 6, 8, 7, 0),
            array(4, 2, 8, 6, 5, 7, 3, 9, 0, 1),
            array(2, 7, 9, 3, 8, 0, 6, 4, 1, 5),
            array(7, 0, 4, 6, 9, 1, 3, 2, 5, 8)
        );

        $inverse = array(0, 4, 3, 2, 1, 5, 6, 7, 8, 9);
        settype($partial, "string");
        $partial = strrev($partial);
        $digitIndex = 0;
        for ($i = 0; $i < strlen($partial); $i++) {
            $digitIndex = $dihedral[$digitIndex][$permutation[($i + 1) % 8][$partial[$i]]];
        }
        return $inverse[$digitIndex];
    }
    function ajaxgetage(Request $request)
    {
        $diff = 0;
        if ($request->dob != '') {
            $diff = $this->ageCalculate($request->dob);
            // $diff = Carbon::parse($request->dob)->diffInYears($this->base_dob_chk_date);
        }
        return intval($diff);
    }
    function ageCalculate($dob)
    {
        $diff = 0;
        if ($dob != '') {
            // $diff = $this->ageCalculate($dob);
            $diff = Carbon::parse($dob)->diffInYears($this->base_dob_chk_date);
        }
        return intval($diff);
    }
    private function notEmptyCheck(Request $request)
    {

          
            if(is_null($request->data) || trim($request->data)==''){
              //dd('ok');
               return false;
              }
               return true;
    }
    private function mobileNoValidation($mobile_no)
    {

             if (!preg_match('/^[0-9]{10}+$/', $mobile_no)) {
                 return false;
                        //$errorMsg =  __('messages.mobilenoinvalid');
                        //return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
            if ($mobile_no < 1000000000 || strlen($mobile_no)!=10) {
                     return false;
                        //$errorMsg =  __('messages.mobilenoinvalid');
                       // return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
             return true;
           
    }
    
   
}
