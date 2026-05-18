<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BeneficiaryAadhaar extends Model 
{
   // use \OwenIt\Auditing\Auditable;
    protected $guarded = [];
    protected $primaryKey = 'application_id';
    protected $table = 'pension.beneficiary_aadhars';
    public $incrementing = false;
    public function personal()
    {
        return $this->belongsTo(BeneficiaryPersonal::class, 'application_id', 'application_id');
    }
    
}
