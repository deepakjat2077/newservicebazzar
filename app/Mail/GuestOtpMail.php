<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class GuestOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public Carbon $expiresAt;
    public string $guestName;

    public function __construct(string $code, Carbon $expiresAt, string $guestName)
    {
        $this->code = $code;
        $this->expiresAt = $expiresAt;
        $this->guestName = $guestName;
    }

    public function build()
    {
        return $this->subject('Your verification code')
            ->view('emails.guest_otp');
    }
}
