<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailSender extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $token;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function build()
    {
        $url = config('app.frontend_url') . '/set-password?token=' . $this->token . '&email=' . $this->user->email;

        return $this->subject('Set your password')
            ->view('emails.set_password')
            ->with([
                'name' => $this->user->first_name,
                'url' => $url,
            ]);
    }
}
