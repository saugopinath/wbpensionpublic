<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BeneficiarySelfDeclaration extends Model 
{
    protected $guarded = [];
    protected $table = 'pension.beneficiary_self_declarations';
    protected $primaryKey = 'application_id';
    public $incrementing = false;
    protected $casts = [
        'other_details' => 'array',
    ];
    
}
