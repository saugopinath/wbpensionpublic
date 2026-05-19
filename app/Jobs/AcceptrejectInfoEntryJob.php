<?php

namespace App\Jobs;


use App\Models\AcceptRejectInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AcceptrejectInfoEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $userData;

    // Pass data via the constructor
    public function __construct(array $userData)
    {
        $this->userData = $userData;
    }

    // Logic executed by the queue worker
    public function handle()
    {
        User::create($this->userData);
    }
}
