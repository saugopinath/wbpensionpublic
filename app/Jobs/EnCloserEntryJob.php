<?php

namespace App\Jobs;

use App\Models\BeneficiaryEnclosure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class EnCloserEntryJob implements ShouldQueue
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
        return BeneficiaryEnclosure::updateOrCreate(
            [
                'scheme_id' => $this->payload['scheme_id'],
                'application_id' => $this->payload['application_id'],
                'document_type' => $this->payload['document_type'],
            ],
            $this->payload
        );
    }
}
