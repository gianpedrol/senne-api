<?php

namespace App\Jobs;

use App\Mail\emailPasswordReset;
use App\Mail\emailVerification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class sendEmailPasswordReset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $url;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user,$url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::queue(new emailPasswordReset($this->user,$this->url));
    }
}
