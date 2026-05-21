<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class AcceptrejectInfoEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $accept_reject_info;

    /**
     * Create a new job instance.
     */
    public function __construct($accept_reject_info)
    {
        $this->accept_reject_info = $accept_reject_info;
    }

    /**
     * Execute the job.
     */
        public function handle(): void
    {
        if ($this->accept_reject_info) {
            if (isset($this->accept_reject_info->add_edit_status)) {
                unset($this->accept_reject_info->add_edit_status);
            }
            if (isset($this->accept_reject_info->add_edit_sttus)) {
                unset($this->accept_reject_info->add_edit_sttus);
            }
            $this->accept_reject_info->save();
        }
    }
}
