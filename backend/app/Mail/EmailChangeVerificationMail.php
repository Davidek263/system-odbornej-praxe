<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailChangeVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $newEmail;
    public $token;
    public $emailType;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $newEmail, $token, $emailType)
    {
        $this->user = $user;
        $this->newEmail = $newEmail;
        $this->token = $token;
        $this->emailType = $emailType;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $verificationUrl = url('/api/verify-email-change?token=' . $this->token);

        return $this->subject('Potvrdenie zmeny emailu')
                    ->view('emails.email_change_verification')
                    ->with([
                        'name' => $this->user->first_name,
                        'oldEmail' => $this->user->email,
                        'newEmail' => $this->newEmail,
                        'verificationUrl' => $verificationUrl,
                        'emailType' => $this->emailType,
                    ]);
    }
}
