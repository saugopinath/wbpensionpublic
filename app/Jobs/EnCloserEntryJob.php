<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\BeneficiaryEnclosure;

class EnCloserEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $pension_details_enc;

    /**
     * Create a new job instance.
     */
    public function __construct($pension_details_enc)
    {
        $this->pension_details_enc = $pension_details_enc;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->pension_details_enc) {
            if (isset($this->pension_details_enc->add_edit_status)) {
                unset($this->pension_details_enc->add_edit_status);
            }
            if (isset($this->pension_details_enc->add_edit_sttus)) {
                unset($this->pension_details_enc->add_edit_sttus);
            }
            $exists = BeneficiaryEnclosure::where('scheme_id', $this->pension_details_enc->scheme_id)
                ->where('application_id', $this->pension_details_enc->application_id)
                ->where('document_type', $this->pension_details_enc->document_type)
                ->exists();
            $this->pension_details_enc->exists = $exists;
            $this->pension_details_enc->save();
        }
    }
}
