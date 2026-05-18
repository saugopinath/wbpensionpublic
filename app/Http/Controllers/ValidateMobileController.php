<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\EncryptDecrypt;

use App\Services\SendSmsService;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;  
use App\Helpers\TokenValidation;
class ValidateMobileController extends Controller
{
   public function __construct
   (
       
        protected SendSmsService $sendsmsService,
    ) {
         $this->data_not_supplied=__('messages.invaliddata');
         $this->scheme_id=20;
    }
    public function mobilecheck(Request $request)
    {
       // dd('ok');
        try {
            if(!$this->notEmptyCheck($request)){
                 return response()->json(['is_sucess' => false, 'error' => $this->data_not_supplied], 400);
              }
             $request_data = EncryptDecrypt::decrypt($request->data);
             $mobile_no=$request_data['mobile_no'];
             if(!$this->mobileNoValidation($mobile_no)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             
                if (config('app.env') == 'production')
                $otp = rand(111111, 999999);
            else
                $otp = 853154;
            $message = __('messages.otpmessage')." is " . $otp . ".   
__('messages.govtwb').";
            
            $encrpt_otp=EncryptDecrypt::encrypt($otp);
            DB::beginTransaction();
            $snd_sms = $this->sendsmsService->sendSms($mobile_no, $message);
            $smsTrack = $this->sendsmsService->SmstrackInsert($mobile_no,$encrpt_otp,$request);
            if( $snd_sms && $smsTrack){

               /* $customClaims = ['iss' => config('app.url'),
                'iat' => Carbon::now(),
                'exp' => Carbon::now()->addMinutes(config('app.otp_expiration')),
                'mobile_no' => EncryptDecrypt::encrypt($request->mobile_no), 
                'otp' => $encrpt_otp];
                $payload = JWTFactory::make($customClaims);*/
               // dd(Carbon::now()->addMinutes((int) config('app.otp_expiration')));
                $otp_expire=Carbon::now()->addMinutes((int) config('app.otp_expiration'));
                //dd($otp_expire->format('Y-m-d H:i:s'));
                $payload = JWTFactory::sub(base64_encode(EncryptDecrypt::encrypt($smsTrack)))
                ->mobile_no(base64_encode(EncryptDecrypt::encrypt($mobile_no)))
                ->scheme_id(base64_encode(EncryptDecrypt::encrypt($this->scheme_id)))
                ->otpexpire( $otp_expire->format('Y-m-d H:i:s'))
                ->otp(base64_encode($encrpt_otp))
                ->make();

               // $token = JWTAuth::encode($payload);
                $token = JWTAuth::encode($payload);
               // dd($token);
                DB::commit();
                return response()->json(["is_success" => true,'token' => base64_encode($token)]);

            }   
            else{
                 DB::rollBack();
                $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
            }   
        } catch (\Throwable $e) {
            dd($e);
            return response()->json([
            "is_success" => false,'error' => __('messages.unexpectederror') ,
            'message' => $e->getMessage()
        ], 500);
        } 
        
    }
    public function otpcheck(Request $request)
    {
        if(!$this->notEmptyCheck($request)){
                 return response()->json(['is_sucess' => false, 'error' => $this->data_not_supplied], 400);
              }
             $request_data = EncryptDecrypt::decrypt($request->data);
             $mobile_no=$request_data['mobile_no'];
             $scheme_id=$request_data['scheme_id'];
             $otp=$request_data['otp'];
             $token_valid=TokenValidation::checkTokenMobileScheme($request_data,$request);
            // dd($token_valid);
             if(!$token_valid){
                 $errorMsg =  __('messages.invalidToken');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
             $token_expire=TokenValidation::checkOtp($request_data,$request);
            //dd($token_expire);
             if(!$token_expire){
                 $errorMsg =  __('messages.invalidOtp');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
       
                
                
                $token= request()->bearerToken();
                $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));

                $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
                
                $insert = $this->sendsmsService->OtpValidationLogInsert($mobile_no,$decrpt_sub,$request);
if( $insert){
               $payload = JWTFactory::sub(base64_encode(EncryptDecrypt::encrypt($insert)))
                ->mobile_no($mobile_no)
                ->scheme_id($scheme_id)
                ->otpValidatetimeexp(Carbon::now()->addMinutes((int) config('jwt.ttl'))->format('Y-m-d H:i:s'))
                ->otpValidate(1)
                ->make();

               // $token = JWTAuth::encode($payload);
                $token = JWTAuth::encode($payload);
               // dd($token);
                return response()->json(["is_success" => true,'token' => base64_encode($token)]);
}
else{
     $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
}

                 
        
    }
    public function guestdashboardcheck(Request $request)
    {
          if(!$this->notEmptyCheck($request)){
                 return response()->json(['is_sucess' => false, 'error' => $this->data_not_supplied], 400);
              }
             $request_data = EncryptDecrypt::decrypt($request->data);
             $mobile_no=$request_data['mobile_no'];
             if(!$this->mobileNoValidation($mobile_no)){
                 $errorMsg =  __('messages.mobilenoinvalid');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
            
            
               
                $token= request()->bearerToken();
                $mobile_no = $request->input('mobile_no');
                $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));

                $decrpt_sub=EncryptDecrypt::decrypt(base64_decode($claims['sub']));
                
                $insert = $this->sendsmsService->OtpValidationLogInsert($request->mobile_no,$decrpt_sub,$request);
if( $insert){
               $payload = JWTFactory::sub(base64_encode(EncryptDecrypt::encrypt($insert)))
               ->mobile_no($mobile_no)
                ->otpValidatetimeexp(Carbon::now()->addMinutes((int) config('jwt.ttl'))->format('Y-m-d H:i:s'))
                ->otpValidate(1)
                ->make();

               // $token = JWTAuth::encode($payload);
                $token = JWTAuth::encode($payload);
               // dd($token);
                return response()->json(["is_success" => true,'token' => base64_encode($token)]);
}
else{
     $errorMsg = __('messages.dbroolback');
                return response()->json(["is_success" => false,'error' => $errorMsg]);
}

                 
        
    }
    public function sample_encrypt(){

    $dataToEncrypt = [
                  'mobile_no' => '8583035693',
                  'scheme_id' => 20,
                  
              ];
             $array = json_decode(json_encode($dataToEncrypt), true);
             $encryptedJson = EncryptDecrypt::encrypt($array);
            dump('mobileScheme--'.$encryptedJson);

            $dataToEncrypt = [
                  'mobile_no' => '8583035693',
                  'scheme_id' => 20,
                  'otp' => 853154,
                  
              ];
             $array = json_decode(json_encode($dataToEncrypt), true);
             $encryptedJson = EncryptDecrypt::encrypt($array);
            dump('mobileSchemeOtp--'.$encryptedJson);

         $dataToEncrypt = [
                  'mobile_no' => '8583035693',
                  'scheme_id' => 20,
                  'beneficiary_name' => 'Gopinath Sau',
                  'gender' => 52,
                  'dob' => '1985-09-07',
                  'father_first_name' => 'Dilip',
                  'father_middle_name' => 'Kumar',
                  'father_last_name' => 'Sau',
                  'mother_first_name' => 'Chaya',
                  'mother_middle_name' => 'Rani',
                  'mother_last_name' => 'Sau',
                  'caste_category' => 173,
                  'aadhar_no' => '769585340046',
                  'ben_mobile_no' => '8583035693',
                  
              ];
             $array = json_decode(json_encode($dataToEncrypt), true);
             $encryptedJson = EncryptDecrypt::encrypt($array);
            dump('personal--'.$encryptedJson);

            $dataToEncrypt = [
                  'mobile_no' => '8583035693',
                  'scheme_id' => 20,
                  'district' => 318,
                  'urban_code' => 2,
                  'police_station' => 'Daspur',
                  'block_muncipality' => 2979,
                  'gp_ward' => 110282,
                  'village_town_city' => 'Joyramchak',
                  'post_office' => 'Panchgechia',
                  'pin_code' => '721148',
                  'application_id' => '1sdrttttt',
                  
              ];
             $array = json_decode(json_encode($dataToEncrypt), true);
             $encryptedJson = EncryptDecrypt::encrypt($array);
            dump('contact--'.$encryptedJson);
            $dataToEncrypt = [
                  'mobile_no' => '8583035693',
                  'scheme_id' => 20,
                  'ifsc' => 318,
                  'bank_account_no' => 318,
                  'confirm_bank_account_no' => 318,
                  'application_id' => '1sdrttttt',
                  
                  
              ];
             $array = json_decode(json_encode($dataToEncrypt), true);
             $encryptedJson = EncryptDecrypt::encrypt($array);
            dump('bank--'.$encryptedJson);
            $dataToEncrypt = [
                  'mobile_no' => '8583035693',
                  'scheme_id' => 20,
                  'doc_is_resident' => 1,
                  'doc_is_resident' => 1,
                  'earn_monthly_remuneration' => 1,
                  'application_id' => '1sdrttttt',
                  
                  
              ];
             $array = json_decode(json_encode($dataToEncrypt), true);
             $encryptedJson = EncryptDecrypt::encrypt($array);
            dump('declaration--'.$encryptedJson);

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
     private function OtpValidation($otp)
    {

             if (!preg_match('/^[0-9]{10}+$/', $otp)) {
                 return false;
                        //$errorMsg =  __('messages.mobilenoinvalid');
                        //return response()->json(["is_success" => false,'error' => $errorMsg]);
             }
            if (strlen($otp)!=6) {
                     return false;
                        //$errorMsg =  __('messages.mobilenoinvalid');
                       // return response()->json(["is_success" => false,'error' => $errorMsg]);
            }
             return true;
           
    }
}
