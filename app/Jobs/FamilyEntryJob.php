<?php

namespace App\Jobs;

use App\Models\BeneficiaryFamilyDetail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class FamilyEntryJob implements ShouldQueue
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
        if (isset($this->payload['family_members']) && is_array($this->payload['family_members'])) {
            $this->payload['family_members'] = json_encode($this->payload['family_members']);
        }

        return BeneficiaryFamilyDetail::updateOrCreate(
            [
                'scheme_id' => $this->payload['scheme_id'],
                'application_id' => $this->payload['application_id'],
            ],
            $this->payload
        );
    }
}
