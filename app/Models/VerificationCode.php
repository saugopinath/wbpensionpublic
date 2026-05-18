<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{

    protected $fillable = ['encrypt_otp', 'expire_at','mobile_no','ip_address','user_agent'];
    protected $primaryKey = 'id';
}