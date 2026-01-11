<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailChangedConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $oldEmail;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $oldEmail)
    {
        $this->user = $user;
        $this->oldEmail = $oldEmail;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Email bol úspešne zmenený')
                    ->view('emails.email_changed_confirmation')
                    ->with([
                        'name' => $this->user->first_name,
                        'oldEmail' => $this->oldEmail,
                        'newEmail' => $this->user->email,
                    ]);
    }
}
