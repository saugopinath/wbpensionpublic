<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\BeneficiaryAadhaar;

class AadhaarEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $pension_details_aadhar;

    /**
     * Create a new job instance.
     */
    public function __construct($pension_details_aadhar)
    {
        $this->pension_details_aadhar = $pension_details_aadhar;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->pension_details_aadhar) {
            if (isset($this->pension_details_aadhar->add_edit_status)) {
                unset($this->pension_details_aadhar->add_edit_status);
            }
            if (isset($this->pension_details_aadhar->add_edit_sttus)) {
                unset($this->pension_details_aadhar->add_edit_sttus);
            }
            $exists = BeneficiaryAadhaar::where('scheme_id', $this->pension_details_aadhar->scheme_id)
                ->where('application_id', $this->pension_details_aadhar->application_id)
                ->exists();
            $this->pension_details_aadhar->exists = $exists;
            $this->pension_details_aadhar->save();
        }
    }
}
