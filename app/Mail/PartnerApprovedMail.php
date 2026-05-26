<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PartnerApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $name;
    public $email;

    /**
     * Create a new message instance.
     *
     * @param string $name
     * @param string $email
     * @return void
     */
    public function __construct($name, $email)
    {
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
        return $this->from(
            env('MAIL_FROM_ADDRESS'),
            env('MAIL_FROM_NAME')
        )
            ->subject(__('auth.subject_mail_partner_approved'))
            ->view('emails.partner-approved')
            ->with([
                'name' => $this->name,
                'email' => $this->email
            ]);
    }
}
