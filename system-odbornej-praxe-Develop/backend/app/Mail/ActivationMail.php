<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $activationToken;

    public function __construct($user, $activationToken)
    {
        $this->user = $user;
        $this->activationToken = $activationToken;
    }

    public function build()
    {
        // FRONTEND activation URL (Vue → Laravel → redirect to set-password)
        $activationUrl = url('/api/activate-account?token=' . $this->activationToken);


        return $this->subject('Activate Your Account')
                    ->markdown('emails.activation')
                    ->with([
                        'name' => $this->user->first_name,
                        'activationUrl' => $activationUrl,
                    ]);
    }
}
