<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\BeneficiaryBank;

class BankEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $pension_details_bank;

    /**
     * Create a new job instance.
     */
    public function __construct($pension_details_bank)
    {
        $this->pension_details_bank = $pension_details_bank;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->pension_details_bank) {
            if (isset($this->pension_details_bank->add_edit_status)) {
                unset($this->pension_details_bank->add_edit_status);
            }
            if (isset($this->pension_details_bank->add_edit_sttus)) {
                unset($this->pension_details_bank->add_edit_sttus);
            }
            $exists = BeneficiaryBank::where('scheme_id', $this->pension_details_bank->scheme_id)
                ->where('application_id', $this->pension_details_bank->application_id)
                ->exists();
            $this->pension_details_bank->exists = $exists;
            $this->pension_details_bank->save();
        }
    }
}
