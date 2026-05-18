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
                $token= request()->bearerToken();
                $mobile_no = $signature['mobile_no'];
                $scheme_id = $signature['scheme_id'];
                if (!$token || !$mobile_no) {
                   return response()->json([
                      "is_success" => false,'error' => __('messages.invalidToken')], 400);
                }
                //dd($scheme_id);
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
             return false;
        }

       
       
    
    }
     public static function checkOtp(array $signature,Request $request)
    {
        
        try {
                $otp=$signature['otp'];
                
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

  
}
