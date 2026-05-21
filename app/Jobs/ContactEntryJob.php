<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\BeneficiaryContact;

class ContactEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $pension_details_contact;

    /**
     * Create a new job instance.
     */
    public function __construct($pension_details_contact)
    {
        $this->pension_details_contact = $pension_details_contact;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->pension_details_contact) {
            if (isset($this->pension_details_contact->add_edit_status)) {
                unset($this->pension_details_contact->add_edit_status);
            }
            if (isset($this->pension_details_contact->add_edit_sttus)) {
                unset($this->pension_details_contact->add_edit_sttus);
            }
            $exists = BeneficiaryContact::where('scheme_id', $this->pension_details_contact->scheme_id)
                ->where('application_id', $this->pension_details_contact->application_id)
                ->exists();
            $this->pension_details_contact->exists = $exists;
            $this->pension_details_contact->save();
        }
    }
}
