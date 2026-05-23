<?php

// namespace App\Jobs;

// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Bus\Queueable;
// use Illuminate\Queue\InteractsWithQueue;

// class AcceptrejectInfoEntryJob implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable;

//     public $accept_reject_info;

/**
 * Create a new job instance.
 */
    // public function __construct($accept_reject_info)
    // {
    //     $this->accept_reject_info = $accept_reject_info;
    // }

/**
 * Execute the job.
 */
//     public function handle(): void
// {
//     if ($this->accept_reject_info) {
//         if (isset($this->accept_reject_info->add_edit_status)) {
//             unset($this->accept_reject_info->add_edit_status);
//         }
//         if (isset($this->accept_reject_info->add_edit_sttus)) {
//             unset($this->accept_reject_info->add_edit_sttus);
//         }
//         $this->accept_reject_info->save();
//     }
// }
// public function handle() { return AcceptRejectInfo::create( $this->payload ); }
// }

namespace App\Jobs;

use App\Models\AcceptRejectInfo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;

class AcceptrejectInfoEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function handle()
    {
        if (isset($this->payload['add_edit_sttus'])) {
            unset($this->payload['add_edit_sttus']);
        }
        if (isset($this->payload['add_edit_status'])) {
            unset($this->payload['add_edit_status']);
        }
        return AcceptRejectInfo::create($this->payload);
    }
}
