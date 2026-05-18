<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
class BeneficiaryPersonal extends Model 
{
    //use \OwenIt\Auditing\Auditable;
    protected $guarded = [];

    protected $table = 'pension.beneficiary_personals';

    protected $primaryKey = 'application_id';

    public $incrementing = false;

    protected $casts = [
        'other_details' => 'array',
    ];

    public function contact()
    {
        return $this->hasOne(BeneficiaryContact::class, 'application_id', 'application_id');
    }
    public function documents()
    {
        return $this->hasMany(BeneficiaryEnclosure::class, 'application_id');
    }
    public function bank()
    {
        return $this->hasOne(BeneficiaryBank::class, 'application_id', 'application_id');
    }
    public function aadhar()
    {
        return $this->hasOne(BeneficiaryAadhaar::class, 'application_id', 'application_id');
    }
    public function aadhaar()
    {
        return $this->hasOne(BeneficiaryAadhaar::class, 'application_id');
    }

    public function banks()
    {
        return $this->hasOne(BeneficiaryBank::class, 'application_id', 'application_id');
    }
    public function enclosers()
    {
        return $this->hasMany(BeneficiaryEnclosure::class, 'application_id', 'application_id');
    }
    public function scheme()
    {
        return $this->hasOne(Scheme::class, 'id', 'scheme_id');
    }
   

   
}
