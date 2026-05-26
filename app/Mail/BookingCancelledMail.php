<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected string $guestEmail;
    protected string $guestName;
    protected array $data;

    /**
     * Create a new message instance.
     *
     * @param string $guestEmail
     * @param string $guestName
     * @param array  $data
     */
    public function __construct(string $guestEmail, string $guestName, array $data)
    {
        $this->guestEmail = $guestEmail;
        $this->guestName  = $guestName;
        $this->data       = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this
            ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
            ->subject('[BKS System] Thông báo hủy đặt phòng #' . ($this->data['booking_code'] ?? ''))
            ->view('room-bookings.booking-cancelled')
            ->with([
                'name'  => $this->guestName,
                'email' => $this->guestEmail,
                'data'  => $this->data,
            ]);
    }
}
