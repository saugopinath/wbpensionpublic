<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Helpers\EncryptDecrypt;
use Carbon\Carbon;  
class MobileAuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         try {
                $token= request()->bearerToken();
                $request_data = EncryptDecrypt::decrypt($request->data);
                $mobile_no=$request_data['mobile_no'];
                $otp=$request_data['otp'];
               

                if (!$token || !$mobile_no || !$otp) {
                   return response()->json([
                      "is_success" => false,'error' => __('messages.invalidToken')], 400);
                }
                //dd(base64_decode($token));
                $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
                //dd( $claims);
                
                $decrpt_mobile_header=EncryptDecrypt::decrypt(base64_decode($claims['mobile_no']));
                $decrpt_mobile_body=EncryptDecrypt::decrypt(base64_decode($mobile_no));
                $decrpt_otp_header=EncryptDecrypt::decrypt(base64_decode($claims['otp']));
                $decrpt_otp_body=EncryptDecrypt::decrypt(base64_decode($otp));
                  //dd( $decrpt_otp_body);
                if($decrpt_mobile_header!=$decrpt_mobile_body){
                      return response()->json([
                      "is_success" => false,'error' => __('messages.invalidToken')
                      ], 400);
                }
                
                if(Carbon::now()>Carbon::parse($claims['otpexpire'])){
                     return response()->json([
                      "is_success" => false,'error' => __('messages.otpexpired')
                      ], 400);
                }
                 if($decrpt_otp_header!=$decrpt_otp_body){
                      return response()->json([
                      "is_success" => false,'error' => __('messages.invalidOtp')
                      ], 400);
                }
                //dd( $encrpt_mobile);
        } catch (\Throwable $e) {
            return response()->json([
            "is_success" => false,'error' => __('messages.unexpectederror') ,
            'message' => $e->getMessage()
        ], 500);
        }

        return $next($request);
       
       
    }
}
