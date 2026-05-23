<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiaryFamilyDetail extends Model
{
    protected $guarded = [];

    protected $table = 'pension.beneficiary_family_details';

    protected $primaryKey = 'application_id';

    public $incrementing = false;

    protected $casts = [
        'family_members' => 'array',
    ];
}
