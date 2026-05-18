<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;

interface SendSmsInterface
{

    public function sendSms(string $mobile_no,string $msg): bool;
    public function SmstrackInsert(string $mobile_no,string $otp,Request $request): int;
    public function OtpValidationLogInsert(string $mobile_no,int $verification_id,Request $request): int;

}