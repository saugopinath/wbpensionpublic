<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\BeneficiarySelfDeclaration;

class DeclarationEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $pension_details_declaration;

    /**
     * Create a new job instance.
     */
    public function __construct($pension_details_declaration)
    {
        $this->pension_details_declaration = $pension_details_declaration;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->pension_details_declaration) {
            if (isset($this->pension_details_declaration->add_edit_status)) {
                unset($this->pension_details_declaration->add_edit_status);
            }
            if (isset($this->pension_details_declaration->add_edit_sttus)) {
                unset($this->pension_details_declaration->add_edit_sttus);
            }
            $exists = BeneficiarySelfDeclaration::where('scheme_id', $this->pension_details_declaration->scheme_id)
                ->where('application_id', $this->pension_details_declaration->application_id)
                ->exists();
            $this->pension_details_declaration->exists = $exists;
            $this->pension_details_declaration->save();
        }
    }
}
