<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $email;
    protected $name;
    protected $data;
    /**
     * Create a new message instance.
     *
     * @param string $email
     * @param string $name
     * @param array $data
     *
     * @return void
     */
    public function __construct($email, $name, array $data)
    {
        $this->data = $data;
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = !empty($this->data['is_paid'])
            ? 'Xác nhận thanh toán thành công và hoàn tất đặt phòng - BKS Stay'
            : 'Welcome to BKS - Xác nhận đặt phòng STAY';

        return $this->from(
            env('MAIL_FROM_ADDRESS'),
            env('MAIL_FROM_NAME')
        )
            ->subject($subject)
            ->view('room-bookings.room-booking')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'data' => $this->data
            ]);
    }
}
