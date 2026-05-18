<?php
namespace App\Services;
use Illuminate\Support\Facades\Http;
use App\Models\VerificationCode;
use App\Models\OtpValidation;
use App\Interfaces\SendSmsInterface;
use Illuminate\Http\Request;
class SendSmsService implements SendSmsInterface
{


    public function sendsms(string $mobile_no, string $msg): bool
    {
        //dd(env('APP_ENV'));
        if (config('app.env') == 'production') {
            try {
                Http::withUrlParameters([
                    'endpoint' => 'https://bulkpush.mytoday.com/BulkSms/SingleMsgApi',
                    'feedid' => 379523,
                    'username' => '8017072222',
                    'password' => 'newAuth\$gL22m',
                    'senderid' => 'WB_JAIBANGLAOTP',
                    'To' => $mobile_no,
                    'Text' => urlencode($msg),
                ])->get('{+endpoint}/{feedid}/{username}/{password}/{senderid}/{To}/{Text}');
                return true;
            } catch (\Exception $e) {
                return false;

            }
        } else {

            return true;
        }
    }
    public function SmstrackInsert(string $mobile_no, string $otp,Request $request): int
    {
        //dd($otp);
        $verification_coe_obj=new VerificationCode();
        $verification_coe_obj->encrypt_otp=$otp;
        $verification_coe_obj->mobile_no=$mobile_no;
        $verification_coe_obj->ip_address=$request->ip();
        $verification_coe_obj->user_agent=$request->userAgent();
        $is_inserted = $verification_coe_obj->save();
        if ($is_inserted) {
            return $verification_coe_obj->id;
        } else {
            return 0;
        }
    }
     public function OtpValidationLogInsert(string $mobile_no,int $verification_id,Request $request): int
    {
        //dd($otp);
        $verification_coe_obj=new OtpValidation();
        $verification_coe_obj->mobile_no=$mobile_no;
        $verification_coe_obj->verification_id=$verification_id;
        $verification_coe_obj->ip_address=$request->ip();
        $verification_coe_obj->user_agent=$request->userAgent();
        $is_inserted = $verification_coe_obj->save();
        if ($is_inserted) {
            return $verification_coe_obj->id;
        } else {
            return 0;
        }
    }
}