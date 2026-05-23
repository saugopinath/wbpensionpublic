<?php

// namespace App\Jobs;

// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Bus\Queueable;
// use Illuminate\Queue\InteractsWithQueue;
// use App\Models\BeneficiaryPersonal;

// class PersonaEntryJob implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable;

//     public $pension_details;

//     /**
//      * Create a new job instance.
//      */
//     public function __construct($pension_details)
//     {
//         $this->pension_details = $pension_details;
//     }

/**
 * Execute the job.
 */
// public function handle(): void
// {
//     if ($this->pension_details) {
//         if (isset($this->pension_details->add_edit_status) && $this->pension_details->add_edit_status == 1) {
//             $beneficiary_personal=BeneficiaryPersonal::where('scheme_id', $this->pension_details->scheme_id)
//             ->where('application_id', $this->pension_details->application_id)
//             ->first();
//             unset($this->pension_details->add_edit_status);
//             unset($this->pension_details->application_id);
//             $beneficiary_personal->update($this->pension_details);
//         }
//         else{
//             unset($this->pension_details->add_edit_status);

//         }
//         if (isset($this->pension_details->add_edit_sttus)) {
//             unset($this->pension_details->add_edit_sttus);
//         }
//         $exists = BeneficiaryPersonal::where('scheme_id', $this->pension_details->scheme_id)
//             ->where('application_id', $this->pension_details->application_id)
//             ->exists();
//         $this->pension_details->exists = $exists;
//         $this->pension_details->save();
//     }
// }

// } 

namespace App\Jobs;

use App\Models\BeneficiaryPersonal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class PersonaEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (isset($this->payload['add_edit_sttus'])) {
            unset($this->payload['add_edit_sttus']);
        }
        if (isset($this->payload['add_edit_status'])) {
            unset($this->payload['add_edit_status']);
        }
        
        // Ensure other_details is stored as json if it's an array
        if (isset($this->payload['other_details']) && is_array($this->payload['other_details'])) {
            $this->payload['other_details'] = json_encode($this->payload['other_details']);
        }

        return BeneficiaryPersonal::updateOrCreate(
            [
                'scheme_id' => $this->payload['scheme_id'],
                'application_id' => $this->payload['application_id'],
            ],
            $this->payload
        );
    }
}
