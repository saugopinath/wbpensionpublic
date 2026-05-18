<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Helpers\EncryptDecrypt;
use Carbon\Carbon;  
class GuestDashboardCheck
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
               //dd($token);
                if (!$token || !$mobile_no) {
                   return response()->json([
                      "is_success" => false,'error' => __('messages.invalidToken')], 400);
                }
                $claims = JWTAuth::getJWTProvider()->decode(base64_decode($token));
                $decrpt_mobile_header=EncryptDecrypt::decrypt(base64_decode($claims['mobile_no']));
                $decrpt_mobile_body=EncryptDecrypt::decrypt(base64_decode($mobile_no));
                $otpValidate=$claims['otpValidate'];
               // dd($otpValidate);
                if($decrpt_mobile_header!=$decrpt_mobile_body){
                      return response()->json([
                      "is_success" => false,'error' => __('messages.invalidToken')
                      ], 400);
                }
                if($otpValidate!=1){
                      return response()->json([
                      "is_success" => false,'error' => __('messages.invalidToken')
                      ], 400);
                }
                
               /*if(Carbon::now()->format('Y-m-d H:i:s')>Carbon::parse($claims['otpValidatetimeexp'])){
                     return response()->json([
                      "is_success" => false,'error' => __('messages.tokenexpired')
                      ], 400);
                }*/
               
                //dd( $encrpt_mobile);
        } catch (\Throwable $e) {
            return response()->json([
            "is_success" => false,'error' => __('messages.unexpectederror') ,
            'message' => $e->getMessage()
        ], 500);
        }
        //dd('ok');
        return $next($request);
       
       
    }
}
