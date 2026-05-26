<?php

namespace App\Jobs;

use App\Mail\PartnerRejectedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPartnerRejectedMail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $name;
    protected $email;
    protected $rejection_reason;

    /**
     * Create a new job instance.
     *
     * @param string $name
     * @param string $email
     * @param string $rejection_reason
     * @return void
     */
    public function __construct($name, $email, $rejection_reason)
    {
        $this->name = $name;
        $this->email = $email;
        $this->rejection_reason = $rejection_reason;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->email)->send(new PartnerRejectedMail($this->name, $this->email, $this->rejection_reason));
    }
}
