<?php

namespace App\Jobs;

use App\Mail\VerifyMail as MailVerifyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class VerifyMail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $token;
    protected $name;
    protected $email;
    /**
     * Create a new job instance.
     *
     * @param string $token
     * @param string $name
     * @param string $email
     * @return void
     */
    public function __construct($token, $name, $email)
    {
        $this->token = $token;
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return Mail::to($this->email)->send(new MailVerifyMail($this->token, $this->name, $this->email));
    }
}
