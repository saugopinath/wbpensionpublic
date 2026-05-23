<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Config;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;  
class TokenValidation
{
    
    public static function checkTokenMobileScheme(array $signature,Request $request)
    {
        
        try {
           // dd($signature);
                $token= request()->bearerToken();
                $mobile_no = $signature['extraData']['mobile_no'];
                $scheme_id = $signature['extraData']['scheme_id'];
               // dd($scheme_id);
                if (!$token || !$mobile_no) {
                   return response()->json([
                      "is_success" => false,'error' => __('messages.invalidToken')], 400);
                }
                
                $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
                //dd( $claims);
                
                $decrpt_mobile_header=EncryptDecrypt::decrypt(base64_decode($claims['mobile_no']));
                $decrpt_scheme_id_header=EncryptDecrypt::decrypt(base64_decode($claims['scheme_id']));
               // dd( $decrpt_scheme_id_header);
                if($decrpt_mobile_header!=$mobile_no){
                      return false;
                }
                if($decrpt_scheme_id_header!=$scheme_id){
                       return false;
                }
                
                
                 return true;
              
                //dd( $encrpt_mobile);
        } catch (\Throwable $e) {
            //dd($e);
             return false;
        }

       
       
    
    }
     public static function checkOtp(array $signature,Request $request)
    {
        
        try {
                $otp=$signature['formData']['otp'];
                
                $token= request()->bearerToken();
                $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
                $payload_otp=EncryptDecrypt::decrypt(base64_decode($claims['otp']));
                //dump(Carbon::now()->format('Y-m-d H:i:s'));
                 if($payload_otp!=$otp){
                      return false;
                }
                // dd('ok');
                if (Carbon::parse($claims['otpexpire'])->isPast()) {
    return false;
}
                
                 return true;
              
                //dd( $encrpt_mobile);
        } catch (\Throwable $e) {
            //dd($e);
             return false;
        }

       
       
    
    }
   public static function notEmptyCheck(Request $request)
    {

          
            if(is_null($request->data) || trim($request->data)==''){
              //dd('ok');
               return false;
              }
               return true;
    }
    public static function schemeValidation($scheme_id)
    {
        $scheme_id = (int) $scheme_id;
        if ($scheme_id != 20) {
            return false;
        }
        return true;
    }
    public static function mobileNoValidation($mobile_no)
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
    public static function OtpValidation($otp)
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
         public static function checTokenExpireTime(array $signature,Request $request)
    {
        
        try {
                
                $token= request()->bearerToken();
                $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
                $otpValidate=EncryptDecrypt::decrypt(base64_decode($claims['otpValidate']));
                //dump(Carbon::now()->format('Y-m-d H:i:s'));
                
                 if($otpValidate!='annapurna-2026'){
                      return false;
                }
                //dd($claims['otpValidatetimeexp']);
                // dd('ok');
                if (Carbon::parse($claims['otpValidatetimeexp'])->isPast()) {
    return true;
}
                
                 return true;
              
                //dd( $encrpt_mobile);
        } catch (\Throwable $e) {
            //dd($e);
             return false;
        }

       
       
    
    }

  
}
