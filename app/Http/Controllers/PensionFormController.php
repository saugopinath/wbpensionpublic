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
use Illuminate\Database\Eloquent\Collection;
use App\Jobs\PersonaEntryJob;
use App\Jobs\AadhaarEntryJob;
use App\Jobs\ContactEntryJob;
use App\Jobs\BankEntryJob;
use App\Jobs\DeclarationEntryJob;
use App\Jobs\EnCloserEntryJob;


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
       // dd($validated);
        $request_data = EncryptDecrypt::decrypt($request->data);
        $mobile_no=$request_data['extraData']['mobile_no'];
        $scheme_id=$request_data['extraData']['scheme_id'];
        $add_edit_status=$request_data['formData']['add_edit_status'];
             
             if(!TokenValidation::mobileNoValidation($mobile_no)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $scheme_id=$request_data['extraData']['scheme_id'];
             //dd($this->schemeValidation($scheme_id));
             if(!TokenValidation::schemeValidation($scheme_id)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
        $token_valid=TokenValidation::checkTokenMobileScheme($request_data,$request);
         if(!$token_valid){
                 $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $token_expire=TokenValidation::checTokenExpireTime($request_data,$request);
            //dd($token_expire);
             if(!$token_expire){
                 $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
         if($add_edit_status==1){
             $application_id=$request_data['formData']['application_id'];
            if (empty($application_id)) {
              $errorMsg = __('messages.invaliddata');
             return response()->json(["is_success" => false,'error' => $errorMsg]);
           }
           if (config('app.queue_enable')) {
            $pension_details=BeneficiaryPersonal::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();
            $pension_details->add_edit_status= 1;
           }
           else{
            $pension_details=BeneficiaryPersonal::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();
           }

           }
           else{
            $application_id = Str::uuid();
            $beneficiary_id = Str::uuid();
              if (config('app.queue_enable')) {
                $pension_details = new Collection();
                $pension_details->scheme_id= $scheme_id;
                $pension_details->add_edit_status= 0;
              }
              else{
            $pension_details=new BeneficiaryPersonal();
              }
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
                
                if($add_edit_status==1){
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
                if (config('app.queue_enable')) {
                  $is_saved = PersonaEntryJob::dispatch($pension_details);
               }
               else{
                $is_saved = $pension_details->save();
                }
                if($is_saved){
                    if (config('app.queue_enable')) {
                     $pension_details_aadhar = new Collection();
                    }
                    else{
                          $pension_details_aadhar=new BeneficiaryAadhaar();
                    }
                  $pension_details_aadhar->scheme_id= $scheme_id;

                  $pension_details_aadhar->encoded_aadhar = Crypt::encryptString($validated['aadhar_no']);
                  $pension_details_aadhar->aadhar_hash = md5($validated['aadhar_no']);
                  $pension_details_aadhar->application_id =   $pension_details->application_id;
                  $pension_details_aadhar->otp_validation_id= $decrpt_sub;
                  $pension_details_aadhar->is_clean= 2;
                  if (config('app.queue_enable')) {
                      $is_saved_aadhar = AadhaarEntryJob::dispatch($pension_details_aadhar);
                  }else{
                  $is_saved_aadhar = $pension_details_aadhar->save();
                  }
                  if (config('app.queue_enable')) {
                    $AcceptRejectInfo = new Collection();
                  }
                  else{
                  $AcceptRejectInfo = new AcceptRejectInfo;
                  }
                  $AcceptRejectInfo->application_id = $pension_details->application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21101');
                  if (config('app.queue_enable')) {
                    $accpt_reject_save = AcceptrejectInfoEntryJob::dispatch($AcceptRejectInfo);
                  }
                  else{
                  $accpt_reject_save = $AcceptRejectInfo->save();
                  }
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
         //dd($validated);
         $request_data = EncryptDecrypt::decrypt($request->data);
         $mobile_no=$request_data['extraData']['mobile_no'];
         $scheme_id=$request_data['extraData']['scheme_id'];
         $application_id=$request_data['formData']['application_id'];
         $add_edit_status=$request_data['formData']['add_edit_status'];

             if(!TokenValidation::mobileNoValidation($mobile_no)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $scheme_id=$request_data['extraData']['scheme_id'];
             //dd($this->schemeValidation($scheme_id));
             if(!TokenValidation::schemeValidation($scheme_id)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
        $token_valid=TokenValidation::checkTokenMobileScheme($request_data,$request);
         if(!$token_valid){
                 $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $token_expire=TokenValidation::checTokenExpireTime($request_data,$request);
            // dd($token_expire);
             if(!$token_expire){
                 $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $district_list=District::all(); 
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
           
             if($add_edit_status==1){
                if (config('app.queue_enable')) {
                $pension_details_contact = new Collection();
                $pension_details_contact->scheme_id  = $scheme_id;
                $pension_details_contact->application_id  = $application_id;
                $pension_details_contact->add_edit_status  = 1;
                }else{
                $pension_details_contact=BeneficiaryContact::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();
                }
             }
             else{
                if (config('app.queue_enable')) {
                     $pension_details_contact = new Collection();
                     $pension_details_contact->scheme_id  = $scheme_id;
                     $pension_details_contact->application_id  = $application_id;
                     $pension_details_contact->add_edit_status  = 0;
                }
                else{
                  $pension_details_contact=new BeneficiaryContact();
                  $pension_details_contact->scheme_id  = $scheme_id;
                  $pension_details_contact->is_clean  = 2;
                }
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
                    $gp_ward =  Panchayat::where('id', $validated['gp_ward'])->first();
                    //$pension_details_contact->block_ulb_name= trim($block_ulb->block_name);
                    //pension_details_contact->gp_ward_name= trim($gp_ward->gram_panchyat_name);
                    $blockulbCode= $block_ulb->block_ulb;
                }
                $pension_details_contact->district_id = $validated['district'];
                $pension_details_contact->rural_urban = $validated['urban_code'];
                $pension_details_contact->policestation = trim($validated['police_station']);
                $pension_details_contact->blockurban = $validated['block_muncipality'];
                $pension_details_contact->gpward = $validated['gp_ward'];
                $pension_details_contact->village_town_city = trim($validated['village_town_city']);
                $pension_details_contact->house_premise_no = trim($validated['house_premise_no']);
                $pension_details_contact->post_office = trim($validated['post_office']);
                $pension_details_contact->pincode = trim($validated['pin_code']);
                $pension_details_contact->ip_address = $request->ip();
                $pension_details_contact->created_by_dist_code = $validated['district'];
                $pension_details_contact->created_by_local_body_code = $blockulbCode;
                if (config('app.queue_enable')) {
                    $is_saved = ContactEntryJob::dispatch($pension_details_contact);
                }
                else{
                $is_saved = $pension_details_contact->save();
                }
                if (config('app.queue_enable')) {
                    $AcceptRejectInfo = new Collection();
                }
                else{
                  $AcceptRejectInfo = new AcceptRejectInfo;
                }
                  $AcceptRejectInfo->application_id = $application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21102');
                  if (config('app.queue_enable')) {
                    $accpt_reject_save = AcceptrejectInfoEntryJob::dispatch($AcceptRejectInfo);
                  }
                  else{
                  $accpt_reject_save = $AcceptRejectInfo->save();
                  }
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
         $validated = $request->validated();
         //dd($validated);
         $request_data = EncryptDecrypt::decrypt($request->data);
         $mobile_no=$request_data['extraData']['mobile_no'];
         $scheme_id=$request_data['extraData']['scheme_id'];
         $application_id=$request_data['formData']['application_id'];
         $add_edit_status=$request_data['formData']['add_edit_status'];
             if(!TokenValidation::mobileNoValidation($mobile_no)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $scheme_id=$request_data['extraData']['scheme_id'];
             //dd($this->schemeValidation($scheme_id));
             if(!TokenValidation::schemeValidation($scheme_id)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
        $token_valid=TokenValidation::checkTokenMobileScheme($request_data,$request);
         if(!$token_valid){
                 $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $token_expire=TokenValidation::checTokenExpireTime($request_data,$request);
            // dd($token_expire);
             if(!$token_expire){
                 $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $district_list=District::all(); 
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
            $ifsc = trim($request->bank_ifsc_code);

            $row_count1 = Ifsccodemaster::where('is_active', 1)->where('code', $ifsc)->count();
            if ($row_count1 == 0) {
                
                $errorMsg = __('messages.ifscnotvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
           

           if($add_edit_status==1){
            if (config('app.queue_enable')) {
                 $pension_details_bank = new Collection();
                 $pension_details_bank->add_edit_sttus  = 1;
            }else{
                                    $pension_details_bank=BeneficiaryBank::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();
            }

           }
           else{
            if (config('app.queue_enable')) {
                   $pension_details_bank = new Collection();
                                 $pension_details_bank->add_edit_sttus  = 0;

            }else{
                        $pension_details_bank=new BeneficiaryBank();
            }
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
                if (config('app.queue_enable')) {
                     $is_saved = BankEntryJob::dispatch($pension_details_bank);
                }
                else{

                $is_saved = $pension_details_bank->save();
                }
                    if (config('app.queue_enable')) {
                    $AcceptRejectInfo = new Collection();
                  }
                  else{
                  $AcceptRejectInfo = new AcceptRejectInfo;
                  }
                  $AcceptRejectInfo->application_id = $application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21103');
                  if (config('app.queue_enable')) {
                    $accpt_reject_save = AcceptrejectInfoEntryJob::dispatch($AcceptRejectInfo);
                  }
                  else{
                  $accpt_reject_save = $AcceptRejectInfo->save();
                  }
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
            
       
          }
        catch (\Exception $e) {
               dd($e);
                $errorMsg = __('messages.invaliddata');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
    }
     public function declarationEntry(StoreDeclarationRequest $request)
    {
        try{
         $validated = $request->validated();
         //dd($validated);
         $request_data = EncryptDecrypt::decrypt($request->data);
         $mobile_no=$request_data['extraData']['mobile_no'];
         $scheme_id=$request_data['extraData']['scheme_id'];
         $application_id=$request_data['formData']['application_id'];
         $add_edit_status=$request_data['formData']['add_edit_status'];

             if(!TokenValidation::mobileNoValidation($mobile_no)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $scheme_id=$request_data['extraData']['scheme_id'];
             //dd($this->schemeValidation($scheme_id));
             if(!TokenValidation::schemeValidation($scheme_id)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
        $token_valid=TokenValidation::checkTokenMobileScheme($request_data,$request);
         if(!$token_valid){
                 $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $token_expire=TokenValidation::checTokenExpireTime($request_data,$request);
            // dd($token_expire);
             if(!$token_expire){
                 $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
              if($add_edit_status==1){
                if (config('app.queue_enable')) {
                     $pension_details_declaration = new Collection();
                     $pension_details_declaration->add_edit_status= 1;
                }
                else{
                $pension_details_declaration=BeneficiarySelfDeclaration::where('scheme_id',$scheme_id)->where('application_id',$application_id)->first();
                }

             }
             else{
                if (config('app.queue_enable')) {
                  $pension_details_declaration = new Collection();
                  $pension_details_declaration->add_edit_status= 0;

                }
                else
                  $pension_details_declaration=new BeneficiarySelfDeclaration();

             }
            $pension_details_declaration->scheme_id= $scheme_id;
            $pension_details_declaration->is_clean= 2;
            $pension_details_declaration->application_id= $application_id;
            

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
                    if (config('app.queue_enable')) {
                         $pension_details_declaration_save = DeclarationEntryJob::dispatch($pension_details_declaration);
                    }else
                   $pension_details_declaration_save = $pension_details_declaration->save();
                  $AcceptRejectInfo = new AcceptRejectInfo;
                  $AcceptRejectInfo->application_id = $application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21104');
                   if (config('app.queue_enable')) {
                     $accpt_reject_save = AcceptrejectInfoEntryJob::dispatch($AcceptRejectInfo);
                   }else
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
            
       
          }
        catch (\Exception $e) {
               dd($e);
                $errorMsg = __('messages.invaliddata');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
    }
   
     public function encloserEntry(Request $request)
    {
        //dd(base64_encode(EncryptDecrypt::encrypt(111)));

         try {
        $request_data = EncryptDecrypt::decrypt($request->data);
        //dd($request_data);
        $application_id=$request_data['formData']['application_id'];
        $document_type=$request_data['formData']['document_type'];
        $scheme_id=$request_data['extraData']['scheme_id'];
        $mobile_no=$request_data['extraData']['mobile_no'];
        $add_edit_status=$request_data['formData']['add_edit_status'];

         if(!TokenValidation::schemeValidation($scheme_id)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
        $token_valid=TokenValidation::checkTokenMobileScheme($request_data,$request);
         if(!$token_valid){
                 $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $token_expire=TokenValidation::checTokenExpireTime($request_data,$request);
            // dd($token_expire);
             if(!$token_expire){
                 $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        $token= request()->bearerToken();
        $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
        $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
        //dd($document_type);
        
        if (empty($decrpt_sub) || !is_int($decrpt_sub)) {
          $errorMsg = __('messages.invaliddata');
           return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
        if (empty($application_id)) {
              $errorMsg = __('messages.invaliddata');
             return response()->json(["is_success" => false,'error' => $errorMsg]);
        }
       
        
         //dd($request->file('file'));
        // dd( $document_type);
        
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
        $rules['file'] = $required . '|max:' . $doc_arr->max_file_size;
        $messages['file.max'] = "The file uploaded for " . $doc_arr->docType->name . " size must be less than :max KB";
        $messages['file.mimes'] = "The file uploaded for " . $doc_arr->docType->name . " must be of type " . $doc_arr->extension_type;
        $messages['file.required'] = "Document for " . $doc_arr->docType->name . " must be uploaded";
        //dd($messages);
        $validator = Validator::make($request->all(), $rules, $messages, $attributes);
        if ($validator->passes()) {
            $valid = 1;
        } else {
            //dd('file3',$validator->errors()->all());
            $return_msg = $validator->errors()->all();
            return response()->json(["is_success" => false,'errors' => $return_msg]);
        }


        if ($valid == 1) {
             if($add_edit_status==1){
                if (config('app.queue_enable')) {
                     $pension_details_enc = new Collection();
                   $pension_details_enc->add_edit_status = 1;
                }else
                $pension_details_enc=BeneficiaryEnclosure::where('scheme_id',$scheme_id)->where('application_id',$application_id)->where('document_type',$document_type)->first();

             }
             else{
                 if (config('app.queue_enable')) {
                     $pension_details_enc = new Collection();
                     $pension_details_enc->add_edit_status = 0;
                 }else
                  $pension_details_enc=new BeneficiaryEnclosure();
                  //$pension_details_enc->add_edit_status = 0;
                  
             }
             $pension_details_enc->application_id = $application_id;
            $pension_details_enc->scheme_id = $scheme_id;
            $pension_details_enc->document_type = $doc_arr->id;
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
                  $pension_details_enc->attched_document = $base64;
                  $pension_details_enc->document_extension = $extension;
                  $pension_details_enc->document_mime_type = $mime_type;
                  $pension_details_enc->ip_address = $request->ip();
                  $pension_details_enc->otp_validation_id= $decrpt_sub;
                  if (config('app.queue_enable')) {
                    $is_saved = EnCloserEntryJob::dispatch($pension_details_enc);

                  }else
                  $is_saved = $pension_details_enc->save();
                  if (config('app.queue_enable')) {
                    $AcceptRejectInfo = new Collection();
                  }
                  else{
                  $AcceptRejectInfo = new AcceptRejectInfo;
                  } 
                  $AcceptRejectInfo->application_id = $application_id;
                  $AcceptRejectInfo->ip_address = request()->ip();
                  $AcceptRejectInfo->browser = request()->header('User-Agent');
                  $AcceptRejectInfo->model_name = null;
                  $AcceptRejectInfo->op_type = Codemaster::getIdByCode('21105');
                  if (config('app.queue_enable')) {
                    $AcceptRejectInfo = new Collection();
                  }
                  else{
                  $AcceptRejectInfo = new AcceptRejectInfo;
                  }
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
    
    
    
   
}
