<?php

namespace App\Jobs;

use App\Mail\BookingMail;
use App\Mail\VerifyMail as MailVerifyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBooking implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $email;
    protected $name;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param string $name
     * @param array $data
     * @return void
     */
    public function __construct($email, $name, array $data)
    {
        $this->data = $data;
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
        return Mail::to($this->email)->send(new BookingMail($this->email, $this->name, $this->data));
    }
}
