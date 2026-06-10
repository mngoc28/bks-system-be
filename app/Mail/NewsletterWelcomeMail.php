<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcomeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $code;
    protected $value;
    protected $type;

    /**
     * Create a new message instance.
     *
     * @param string $code
     * @param float|string $value
     * @param string $type
     *
     * @return void
     */
    public function __construct($code, $value, $type)
    {
        $this->code = $code;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(
            env('MAIL_FROM_ADDRESS', 'noreply@bksstay.com'),
            env('MAIL_FROM_NAME', 'BKS Stay')
        )
            ->subject('Chào mừng bạn đến với BKS Stay - Nhận mã giảm giá 10%!')
            ->view('emails.newsletter-welcome')
            ->with([
                'code' => $this->code,
                'value' => $this->value,
                'type' => $this->type,
            ]);
    }
}
