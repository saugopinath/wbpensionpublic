<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;

class BeneficiaryEnclosure extends Model implements Auditable
{
     use \OwenIt\Auditing\Auditable;

    protected $table = 'pension.beneficiary_documents';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_clean' => 'boolean',
    ];

    /**
     * Relation: Personal Details
     */
    public function personal()
    {
        return $this->belongsTo(BeneficiaryPersonal::class, 'application_id', 'application_id');
    }

    /**
     * Relation: Codemaster (Document Type)
     */
    public function documentType()
    {
        return $this->belongsTo(Codemaster::class, 'document_type', 'id');
    }

    
}