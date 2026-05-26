<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PartnerRejectedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $name;
    public $email;
    public $rejection_reason;

    /**
     * Create a new message instance.
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
            ->subject(__('auth.subject_mail_partner_rejected'))
            ->view('emails.partner-rejected')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'rejection_reason' => $this->rejection_reason
            ]);
    }
}
