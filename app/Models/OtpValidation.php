<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpValidation extends Model
{

    protected $fillable = ['mobile_no','ip_address','user_agent'];
    protected $primaryKey = 'id';
}