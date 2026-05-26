<?php

namespace App\Jobs;

use App\Mail\BookingCancelledMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookingCancelled implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $email;
    protected string $name;
    protected array $data;

    /**
     * Create a new job instance.
     *
     * @param string $email Guest email address
     * @param string $name  Guest display name
     * @param array  $data  Cancellation data (booking_code, reason, dates, etc.)
     */
    public function __construct(string $email, string $name, array $data)
    {
        $this->email = $email;
        $this->name  = $name;
        $this->data  = $data;
    }

    /**
     * Execute the job – send the cancellation email.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new BookingCancelledMail($this->email, $this->name, $this->data));
    }
}
