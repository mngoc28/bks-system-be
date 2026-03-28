<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $token;
    protected $name;
    protected $email;
    /**
     * Create a new message instance.
     *
     * @param string $token
     * @param string $name
     * @param string $email
     *
     * @return void
     */
    public function __construct($token, $name, $email)
    {
        $this->token = $token;
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
            ->view('emails.verify-mail')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'token' => $this->token
            ]);
    }
}
