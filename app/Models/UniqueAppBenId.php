<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniqueAppBenId extends Model
{
    protected $table = 'pension.unique_app_ben_ids';
    protected $primaryKey = 'application_id';
    protected $fillable = ['scheme_id', 'application_id', 'beneficiary_id'];
}
