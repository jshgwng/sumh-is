<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $verificationCode;
    protected $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verificationCode, $name)
    {
        $this->verificationCode = $verificationCode;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.verification-code')
            ->subject('Verification Code')
            ->with([
                'verificationCode' => $this->verificationCode,
                'name' => $this->name
            ]);
    }
}
